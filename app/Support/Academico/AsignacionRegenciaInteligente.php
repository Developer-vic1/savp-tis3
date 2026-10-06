<?php

namespace App\Support\Academico;

use App\Models\Oficial\Academico\Curso;
use App\Models\Oficial\Academico\GestionAcademica;
use App\Models\Oficial\Academico\RegenteAsignacion;
use App\Models\Oficial\Academico\VinculoPersonal;

class AsignacionRegenciaInteligente
{
    public static function intervaloDentro(string $inicio, ?string $fin, string $minimo, ?string $maximo): bool
    {
        return $inicio >= $minimo && (!$fin || $fin >= $inicio)
            && (!$maximo || ($fin && $fin <= $maximo && $inicio <= $maximo));
    }

    public function revisar(array $datos): array
    {
        $errores = validator($datos, [
            'cod_vpe'=>['required','string'], 'cod_gea'=>['required','string'], 'cod_cur'=>['required','string'],
            'fii_ras'=>['required','date_format:Y-m-d'], 'ffi_ras'=>['required','date_format:Y-m-d','after_or_equal:fii_ras'],
            'obs_ras'=>['required','string','min:20','max:1000'],
        ], [
            'required'=>'Completa :attribute.',
            'date_format'=>':attribute debe tener una fecha válida.',
            'after_or_equal'=>'El fin no puede ser anterior al inicio.',
            'min'=>'Escribe al menos :min caracteres en :attribute.',
            'max'=>':attribute no puede superar :max caracteres.',
        ], ['cod_vpe'=>'el regente','cod_gea'=>'la gestión','cod_cur'=>'el grado','fii_ras'=>'el inicio','ffi_ras'=>'el fin','obs_ras'=>'el motivo'])->errors()->messages();
        if ($errores) return ['puede_guardar'=>false,'errores'=>$errores,'advertencias'=>[]];
        $vinculo = VinculoPersonal::with('cargoInstitucional','personalInstitucional.persona.usuario.roles')->find($datos['cod_vpe']);
        $gestion = GestionAcademica::find($datos['cod_gea']);
        if (!$vinculo || $vinculo->est_vpe !== 'ACTIVO' || $vinculo->cargoInstitucional?->cla_cai !== 'REGENTE'
            || $vinculo->personalInstitucional?->est_pin !== 'ACTIVO'
            || $vinculo->personalInstitucional?->persona?->usuario?->est_usu !== 'ACTIVO'
            || !$vinculo->personalInstitucional?->persona?->usuario?->hasRole('Regente')) {
            $errores['cod_vpe'][] = 'Necesitas cargo, vínculo institucional y cuenta Regente activos. No basta seleccionar un nombre.';
        } elseif (!self::intervaloDentro($datos['fii_ras'],$datos['ffi_ras'],$vinculo->fii_vpe->toDateString(),$vinculo->ffi_vpe?->toDateString())) {
            $errores['fii_ras'][] = 'El intervalo debe quedar dentro de la designación institucional del regente.';
        }
        if (!$gestion || $gestion->est_gea !== 'ACTIVO') $errores['cod_gea'][] = 'Solo puedes registrar asignaciones en una gestión activa; las anteriores se consultan como historia.';
        elseif (!self::intervaloDentro($datos['fii_ras'],$datos['ffi_ras'],$gestion->fii_gea->toDateString(),$gestion->ffi_gea->toDateString())) $errores['fii_ras'][] = 'Inicio y fin deben estar dentro de las fechas de la gestión seleccionada.';
        if (!Curso::whereKey($datos['cod_cur'])->where('est_cur','ACTIVO')->exists()) $errores['cod_cur'][] = 'Selecciona un grado activo del catálogo institucional.';
        if (!ExpedienteParaleloInstitucional::justificacionComprensible($datos['obs_ras'])) $errores['obs_ras'][] = 'Describe el motivo real y su referencia de designación; no se acepta texto aparentemente incoherente.';
        $advertencias = [];
        $solapadas = RegenteAsignacion::where('cod_gea',$datos['cod_gea'])->where('est_ras','ACTIVO')->where('fii_ras','<=',$datos['ffi_ras'])
            ->where(fn($q)=>$q->whereNull('ffi_ras')->orWhere('ffi_ras','>=',$datos['fii_ras']))->get();
        $propias = $solapadas->where('cod_vpe',$datos['cod_vpe']);
        if ($propias->contains('cod_cur',$datos['cod_cur'])) $errores['cod_cur'][] = 'Ya existe una asignación del mismo grado que coincide con estas fechas.';
        foreach ($propias as $a) foreach ($propias as $b) {
            if ($a->cod_ras === $b->cod_ras) continue;
            if (max($datos['fii_ras'],$a->fii_ras->toDateString(),$b->fii_ras->toDateString()) <= min($datos['ffi_ras'],$a->ffi_ras?->toDateString()??'9999-12-31',$b->ffi_ras?->toDateString()??'9999-12-31')) $errores['cod_cur'] = ['El regente alcanzaría tres grados simultáneos. El máximo es dos.'];
        }
        if ($solapadas->where('cod_cur',$datos['cod_cur'])->where('cod_vpe','!=',$datos['cod_vpe'])->isNotEmpty()) $advertencias[] = 'Otro regente tiene este grado en parte del intervalo. Comprueba si la responsabilidad compartida está autorizada.';
        return ['puede_guardar'=>$errores===[],'errores'=>$errores,'advertencias'=>$advertencias];
    }
}
