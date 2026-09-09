<?php

namespace App\Services;

use App\Models\CalendarioEvento;
use App\Support\Academico\CalendarioAcademicoInteligente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CalendarioAcademicoService
{
    public function registrarEvento(array $datos): CalendarioEvento
    {
        return $this->guardar($datos);
    }

    public function modificarEvento(string $codigo, array $datos, ?string $version = null): CalendarioEvento
    {
        return $this->guardar($datos, $codigo, $version);
    }

    public function confirmarEvento(string $codigo, ?string $version = null): CalendarioEvento
    {
        return $this->guardar(['est_cae' => 'CONFIRMADO'], $codigo, $version);
    }

    public function cancelarEvento(string $codigo, string $motivo, ?string $version = null): CalendarioEvento
    {
        return $this->guardar(['est_cae' => 'CANCELADO', 'mot_cae' => $motivo], $codigo, $version);
    }

    public function registrarRecuperacion(array $datos): CalendarioEvento
    {
        return $this->guardar(array_replace($datos, ['tip_cae' => 'RECUPERACION', 'efe_cae' => 'RECUPERACION']));
    }

    private function guardar(array $datos, ?string $codigo = null, ?string $version = null): CalendarioEvento
    {
        return DB::transaction(function () use ($datos, $codigo, $version) {
            $evento = $codigo ? CalendarioEvento::whereKey($codigo)->lockForUpdate()->firstOrFail() : new CalendarioEvento;
            Gate::authorize($codigo ? 'update' : 'create', $codigo ? $evento : CalendarioEvento::class);
            if ($version !== null && $evento->getRawOriginal('updated_at') !== $version) {
                throw ValidationException::withMessages(['evento' => 'El registro fue modificado por otro usuario. Actualice la información antes de continuar.']);
            }
            $antes = $evento->toArray();
            $datos = array_replace($evento->getAttributes(), $datos);
            foreach (['cod_tur', 'cod_cur', 'cod_par', 'cod_hde', 'cod_cae_ori', 'cod_cae_ant', 'hoi_cae', 'hof_cae', 'fec_emi_cae', 'fec_pub_cae'] as $campo) {
                $datos[$campo] = ($datos[$campo] ?? null) ?: null;
            }
            $datos['mot_cae'] = trim($datos['mot_cae'] ?? '');
            $validos = Validator::make($datos, [
                'cod_gea' => 'required|exists:gestion_academica,cod_gea', 'nom_cae' => 'required|string|max:180',
                'tip_cae' => ['required', Rule::in(CalendarioAcademicoInteligente::TIPOS)],
                'efe_cae' => ['required', Rule::in(CalendarioAcademicoInteligente::EFECTOS)],
                'est_cae' => ['required', Rule::in(CalendarioAcademicoInteligente::ESTADOS)],
                'fii_cae' => 'required|date_format:Y-m-d', 'ffi_cae' => 'required|date_format:Y-m-d|after_or_equal:fii_cae',
                'hoi_cae' => 'nullable|date_format:H:i,H:i:s|required_with:hof_cae', 'hof_cae' => 'nullable|date_format:H:i,H:i:s|required_with:hoi_cae|after:hoi_cae',
                'cod_tur' => 'nullable|exists:turno,cod_tur', 'cod_cur' => 'nullable|required_with:cod_par|exists:curso,cod_cur',
                'cod_par' => 'nullable|exists:paralelo,cod_par', 'cod_hde' => 'nullable|exists:horario_detalle,cod_hde',
                'cod_cae_ori' => 'nullable|required_if:tip_cae,RECUPERACION|exists:calendario_evento,cod_cae',
                'mot_cae' => 'required|string|max:4000', 'fue_cae' => 'nullable|string|max:2000', 'com_cae' => 'nullable|boolean',
                'ent_cae' => 'nullable|string|max:180', 'tip_doc_cae' => 'nullable|string|max:80',
                'num_doc_cae' => 'nullable|string|max:100', 'fec_emi_cae' => 'nullable|date_format:Y-m-d',
                'fec_pub_cae' => 'nullable|date_format:Y-m-d|after_or_equal:fec_emi_cae',
                'url_cae' => 'nullable|url:http,https|max:2000',
                'niv_cae' => ['sometimes', Rule::in(['NACIONAL', 'DEPARTAMENTAL', 'DISTRITAL', 'INSTITUCIONAL'])],
                'cer_cae' => ['sometimes', Rule::in(['INFORMATIVO', 'PROBABLE', 'ALTA_PROBABILIDAD', 'CONFIRMADO'])],
                'cod_cae_ant' => 'nullable|exists:calendario_evento,cod_cae',
            ])->validate();
            $gestion = DB::table('gestion_academica')->where('cod_gea', $validos['cod_gea'])->lockForUpdate()->first();
            if ($codigo && $evento->cod_gea !== $validos['cod_gea']) {
                throw ValidationException::withMessages(['cod_gea' => 'Un evento existente no puede trasladarse a otra gestión.']);
            }
            if ($validos['cod_hde']) {
                $horario = DB::table('horario_detalle as d')->join('horario as h', 'h.cod_hor', '=', 'd.cod_hor')->join('plantilla_horaria as p', 'p.cod_pho', '=', 'h.cod_pho')
                    ->where('d.cod_hde', $validos['cod_hde'])->select('h.cod_gea', 'h.cod_cur', 'h.cod_par', 'p.cod_tur')->first();
                foreach (['cod_gea', 'cod_cur', 'cod_par', 'cod_tur'] as $campo) {
                    if (! empty($validos[$campo]) && $validos[$campo] !== $horario->{$campo}) {
                        throw ValidationException::withMessages(['cod_hde' => 'El horario no corresponde al ámbito seleccionado.']);
                    }
                }
            }
            if (! in_array($gestion->est_gea, ['ACTIVA', 'ACTIVO', 'PLANIFICADA', 'PLANIFICADO'], true)) {
                throw ValidationException::withMessages(['evento' => 'La gestión no permite modificar el calendario.']);
            }
            if (($gestion->fii_gea && $validos['fii_cae'] < $gestion->fii_gea) || ($gestion->ffi_gea && $validos['ffi_cae'] > $gestion->ffi_gea)) {
                throw ValidationException::withMessages(['evento' => 'Las fechas deben pertenecer a la gestión.']);
            }
            if (! in_array($validos['est_cae'], ['BORRADOR', 'PREALERTA', 'PENDIENTE_APROBACION'], true) || ($evento->exists && ! in_array($evento->est_cae, ['BORRADOR', 'PREALERTA', 'PENDIENTE_APROBACION'], true)) || $validos['fii_cae'] < today()->toDateString()) {
                Gate::authorize(match (true) {
                    $validos['est_cae'] === 'CANCELADO' => 'cancel',
                    $validos['fii_cae'] < today()->toDateString() => 'correct',
                    default => 'confirm',
                }, $evento);
            }
            if ($validos['est_cae'] === 'SUPERADO' && (! $evento->exists || $evento->est_cae !== 'SUPERADO')) {
                throw ValidationException::withMessages(['evento' => 'Registre una disposición posterior confirmada para superar el evento original.']);
            }
            if ($evento->exists && in_array($evento->est_cae, ['CANCELADO', 'SUPERADO'], true)) {
                throw ValidationException::withMessages(['evento' => 'El evento cerrado conserva su historia. Registre una nueva disposición.']);
            }
            if ($evento->exists && in_array($evento->est_cae, ['CONFIRMADO', 'FINALIZADO'], true)) {
                foreach (['fii_cae', 'ffi_cae', 'hoi_cae', 'hof_cae', 'cod_tur', 'cod_cur', 'cod_par', 'cod_hde', 'efe_cae', 'com_cae', 'niv_cae'] as $campo) {
                    if (array_key_exists($campo, $validos) && (string) $validos[$campo] !== (string) $evento->getRawOriginal($campo)) {
                        throw ValidationException::withMessages(['evento' => 'Para modificar los efectos de una disposición oficial registre un nuevo evento que la sustituya.']);
                    }
                }
            }
            if ($validos['cod_cae_ant']) {
                if ($codigo && $evento->cod_cae_ant !== $validos['cod_cae_ant']) {
                    throw ValidationException::withMessages(['evento' => 'La disposición anterior no puede cambiarse después del registro.']);
                }
                $anterior = CalendarioEvento::whereKey($validos['cod_cae_ant'])->lockForUpdate()->firstOrFail();
                $niveles = ['INSTITUCIONAL' => 1, 'DISTRITAL' => 2, 'DEPARTAMENTAL' => 3, 'NACIONAL' => 4];
                if ($anterior->cod_gea !== $validos['cod_gea'] || $anterior->cod_cae === $codigo || $niveles[$validos['niv_cae'] ?? 'INSTITUCIONAL'] < $niveles[$anterior->niv_cae]) {
                    throw ValidationException::withMessages(['cod_cae_ant' => 'La nueva disposición debe pertenecer a la misma gestión y tener autoridad igual o superior.']);
                }
                if ($validos['est_cae'] === 'CONFIRMADO' && $evento->est_cae !== 'CONFIRMADO') {
                    Gate::authorize('correct', $anterior);
                    if (! in_array($anterior->est_cae, ['CONFIRMADO', 'FINALIZADO'], true)) {
                        throw ValidationException::withMessages(['cod_cae_ant' => 'La disposición anterior ya no está vigente. Actualice el análisis.']);
                    }
                    $historia = $anterior->toArray();
                    $anterior->update(['est_cae' => 'SUPERADO']);
                    BitacoraService::registrar(accion: 'CALENDARIO_EVENTO_SUPERADO', tabla: 'calendario_evento', registro: $anterior->cod_cae, modulo: 'Calendario Académico', valoresAnteriores: $historia, valoresNuevos: $anterior->toArray());
                }
            }
            if ($validos['est_cae'] === 'CONFIRMADO') {
                $validos['cer_cae'] = 'CONFIRMADO';
            }
            if ($validos['cod_cae_ori']) {
                $origen = CalendarioEvento::findOrFail($validos['cod_cae_ori']);
                if ($origen->cod_gea !== $validos['cod_gea'] || $origen->cod_cae === $codigo || $origen->tip_cae === 'RECUPERACION') {
                    throw ValidationException::withMessages(['cod_cae_ori' => 'La recuperación debe referir una contingencia original de la misma gestión.']);
                }
                if (! in_array($origen->est_cae, ['CONFIRMADO', 'FINALIZADO'], true) || ! in_array($origen->efe_cae, ['SIN_CLASES', 'SUSPENSION_PARCIAL', 'INGRESO_DIFERIDO', 'SALIDA_ANTICIPADA'], true)) {
                    throw ValidationException::withMessages(['cod_cae_ori' => 'El origen debe ser una contingencia confirmada con pérdida de clases.']);
                }
            }
            $evento->fill($validos);
            if (! $evento->exists) {
                $evento->cod_cae = 'CAE_'.bin2hex(random_bytes(8));
            }
            $evento->save();
            BitacoraService::registrar(accion: 'CALENDARIO_EVENTO_'.match ($evento->est_cae) {
                'CONFIRMADO' => 'CONFIRMADO', 'CANCELADO' => 'CANCELADO', default => 'REGISTRADO'
            }, tabla: 'calendario_evento', registro: $evento->cod_cae, modulo: 'Calendario Académico', valoresAnteriores: $antes, valoresNuevos: $evento->toArray());

            return $evento;
        }, 3);
    }
}
