<?php

namespace App\Livewire\Admin;

use App\Support\Academico\PanelGestionAcademica;
use App\Support\Academico\CalendarioAcademicoInteligente;
use App\Support\Academico\GestionAcademicaInteligente;
use App\Support\Academico\CasosCalendarioInstitucional;
use App\Services\RoleDashboardResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use App\Support\Academico\UniversidadesCalendario;
use App\Support\Academico\RecuperacionCalendario;
use Illuminate\Validation\ValidationException;
use App\Services\AporteIngenieril\AporteIngenierilClient;
use App\Rules\UrlFuenteUniversitaria;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class CalendarioInstitucional extends Component
{
    public ?string $selectedGestionId = null;
    public string $filtroCalendario = '';
    public string $mesCalendario = '';
    public string $seccion = 'agenda';
    public array $casoImpacto = [];
    #[Locked]
    public array $resultadoImpacto = [];
    #[Locked]
    public array $avisosSesion = [];
    #[Locked]
    public array $revisionUniversidad = [];

    public function mount(): void
    {
        $this->autorizar();
        $this->casoImpacto = CasosCalendarioInstitucional::vacio();
        $gestion = request()->query('gestion');
        $this->selectedGestionId = is_string($gestion) && strlen($gestion) <= 50 && DB::table('gestion_academica')->where('cod_gea', $gestion)->exists()
            ? $gestion : DB::table('gestion_academica')->whereIn('est_gea', ['ACTIVA', 'ACTIVO'])->value('cod_gea');
    }

    private function autorizar(): void
    {
        Gate::authorize('Gestion_Academica');
        abort_unless(app(RoleDashboardResolver::class)->roleFor(auth()->user()) === 'Administrador', 403);
    }

    public function getGestionSeleccionadaProperty(): ?array
    {
        $g = DB::table('gestion_academica')->where('cod_gea', $this->selectedGestionId)->first();
        return $g ? ['id' => $g->cod_gea, 'anio' => (int) $g->ani_gea, 'estado' => GestionAcademicaInteligente::normalizarEstado($g->est_gea), 'fecha_inicio' => $g->fii_gea, 'fecha_fin' => $g->ffi_gea] : null;
    }

    public function render()
    {
        $this->autorizar();
        $gestion = $this->gestionSeleccionada;
        $panel = app(PanelGestionAcademica::class);
        return view('livewire.admin.calendario-institucional', [
            'gestionSeleccionada' => $gestion,
            'panelAcademico' => $panel->consultar($gestion),
            'opcionesGestion' => DB::table('gestion_academica')->orderByDesc('ani_gea')->get()->map(fn ($g) => ['valor' => $g->cod_gea, 'etiqueta' => 'Gestión '.$g->ani_gea])->all(),
            'periodosCalendario' => $gestion ? $panel->periodos($gestion['id']) : [],
            'inicioCurricular' => $gestion ? app(GestionAcademicaInteligente::class)->analizarInicioCurricular($gestion['anio'], collect($panel->periodos($gestion['id']))->firstWhere('orden', 1)['fecha_inicio'] ?? null) : [],
            'tiposCaso' => CasosCalendarioInstitucional::tipos(),
            'efectosCaso' => CasosCalendarioInstitucional::efectos(),
            'gruposCaso' => $this->gruposDisponibles(),
            'turnosCaso' => $this->turnosDisponibles(),
            'docentesCaso' => $this->docentesDisponibles(),
            'claveBorrador' => 'savp-calendario-'.hash('sha256', (string) auth()->id()).'-'.$this->selectedGestionId,
            'clavePlantillas' => 'savp-feriados-'.hash('sha256', (string) auth()->id()),
            'puedeAnalizar' => $this->esGestionActiva($gestion),
            'limitesRecuperacion' => $gestion ? app(RecuperacionCalendario::class)->limites($this->casoImpacto, $gestion) : [],
            'universidadesCaso' => Gate::allows('conocimiento.ver') ? app(UniversidadesCalendario::class)->consultar() : [],
        ]);
    }

    public function updatedSelectedGestionId(): void
    {
        $this->autorizar();
        $this->resultadoImpacto = [];
        $this->casoImpacto = CasosCalendarioInstitucional::vacio();
        $this->revisionUniversidad = [];
        $this->resetValidation();
    }

    public function updatedCasoImpacto($valor, ?string $campo = null): void
    {
        $this->casoImpacto = array_replace(CasosCalendarioInstitucional::vacio(), $this->casoImpacto);
        if ($campo === 'tipo' && isset(CasosCalendarioInstitucional::tipos()[$valor])) {
            if ($valor !== 'FERIADO') $this->casoImpacto['anual'] = 'NO';
            $this->casoImpacto['efecto'] = CasosCalendarioInstitucional::tipos()[$valor]['efecto'];
            $this->casoImpacto['jornada'] = in_array($valor, ['ENTREGA_NOTAS', 'VISITA_UNIVERSIDAD'], true) ? 'PARCIAL' : 'COMPLETA';
            $this->casoImpacto['periodo'] = in_array($valor, ['ANIVERSARIO','EMERGENCIA_SALUD','AMPLIACION_DESCANSO','VIAJE'], true) ? 'RANGO' : 'DIA';
            if ($this->casoImpacto['periodo'] === 'DIA') $this->casoImpacto['fin'] = $this->casoImpacto['inicio'];
            $this->revisionUniversidad = [];
            if (in_array($valor, ['VISITA_UNIVERSIDAD', 'VIAJE'], true)) $this->casoImpacto['alcance'] = 'GRUPO';
        }
        if ($campo === 'efecto' && $valor === 'PARCIAL') $this->casoImpacto['jornada'] = 'PARCIAL';
        if ($campo === 'alcance') {
            $this->casoImpacto['grupo'] = $this->casoImpacto['turno'] = '';
            $this->casoImpacto['grupos'] = $this->casoImpacto['turnos'] = [];
        }
        if (($campo === 'periodo' && $valor === 'DIA') || ($campo === 'inicio' && $this->casoImpacto['periodo'] === 'DIA')) $this->casoImpacto['fin'] = $this->casoImpacto['inicio'];
        if ($campo === 'universidad_url' || $campo === 'universidad') $this->revisionUniversidad = [];
        if ($campo === 'revision' && $valor === 'NO') $this->casoImpacto['recuperacion'] = 'PENDIENTE';
        if (in_array($this->casoImpacto['recuperacion'], ['PENDIENTE','NO'], true)) $this->casoImpacto['modalidad_recuperacion'] = 'POR_DEFINIR';
        $this->resultadoImpacto = [];
        $this->resetValidation();
    }

    public function analizarImpacto(): void
    {
        $this->autorizar();
        $this->resultadoImpacto = [];
        $this->avisosSesion = [];
        $gestion = $this->gestionSeleccionada;
        if (! $this->esGestionActiva($gestion)) {
            $this->alerta('warning', 'Esta gestión es de consulta', 'Selecciona la gestión activa para revisar un cambio en sus clases.');
            return;
        }
        if (! $gestion) {
            $this->alerta('warning', 'Elige una gestión', 'Selecciona el expediente antes de analizar el caso.');
            return;
        }
        if ($this->casoImpacto['revision'] !== 'SI') return;
        $datos = $this->validarCaso($gestion);
        try {
            $this->resultadoImpacto = app(CalendarioAcademicoInteligente::class)->analizarImpactoEvento(array_merge($datos, ['cod_gea' => $gestion['id']]));
        } catch (\Throwable $e) {
            report($e);
            $this->alerta('error', 'No pudimos calcular el impacto', 'Tus campos se conservan. Revisa el calendario y vuelve a intentarlo; si continúa, contacta con soporte.');
        }
    }

    private function validarCaso(array $gestion, bool $comprobarDuracion = false): array
    {
        $this->casoImpacto = array_replace(CasosCalendarioInstitucional::vacio(), $this->casoImpacto);
        abort_unless($this->esGestionActiva($gestion), 422, 'El análisis y los borradores corresponden únicamente a la gestión activa.');
        if ($this->casoImpacto['periodo'] === 'DIA') $this->casoImpacto['fin'] = $this->casoImpacto['inicio'];
        $datos = $this->validate([
            'casoImpacto.periodo' => ['required', Rule::in(['DIA','RANGO'])],
            'casoImpacto.revision' => ['required', Rule::in(['SI','NO'])],
            'casoImpacto.universidad' => ['nullable', Rule::in(array_merge(['OTRA'], array_column(app(UniversidadesCalendario::class)->consultar(), 'valor')))],
            'casoImpacto.universidad_nombre' => ['nullable', 'string', 'max:240'],
            'casoImpacto.universidad_url' => ['nullable', 'url:https', 'max:2000', new UrlFuenteUniversitaria],
            'casoImpacto.estudiar_universidad' => ['required', Rule::in(['SI','NO'])],
            'casoImpacto.recuperacion' => ['required', Rule::in(['PENDIENTE', 'NO', 'POR_CONFIRMAR', 'PROPUESTA'])],
            'casoImpacto.modalidad_recuperacion' => ['required', Rule::in(['POR_DEFINIR','PRESENCIAL','VIRTUAL'])],
            'casoImpacto.medio_recuperacion' => ['required', Rule::in(['AULA_INSTITUCIONAL','VIDEOLLAMADA','OTRO'])],
            'casoImpacto.plan_recuperacion' => ['exclude_unless:casoImpacto.modalidad_recuperacion,VIRTUAL','required','string','min:10','max:1000'],
            'casoImpacto.enlace_recuperacion' => ['nullable','url:https','max:2000'],
            'casoImpacto.observacion_recuperacion' => ['required_if:casoImpacto.recuperacion,POR_CONFIRMAR', 'nullable', 'string', 'min:10', 'max:1000'],
            'casoImpacto.dia_recuperacion' => ['required', Rule::in(['POR_DEFINIR','SABADO','ENTRE_SEMANA'])],
            'casoImpacto.fecha_recuperacion' => ['exclude_unless:casoImpacto.recuperacion,PROPUESTA', 'required', 'date_format:Y-m-d', 'after_or_equal:casoImpacto.fin', 'before_or_equal:'.substr($gestion['fecha_fin'], 0, 10)],
            'casoImpacto.hora_recuperacion_inicio' => ['exclude_unless:casoImpacto.recuperacion,PROPUESTA', 'required', 'date_format:H:i'],
            'casoImpacto.hora_recuperacion_fin' => ['exclude_unless:casoImpacto.recuperacion,PROPUESTA', 'required', 'date_format:H:i', 'after:casoImpacto.hora_recuperacion_inicio'],
            'casoImpacto.anual' => ['required', Rule::in(['NO', 'SI'])],
            'casoImpacto.tipo' => ['required', Rule::in(array_keys(CasosCalendarioInstitucional::tipos()))],
            'casoImpacto.efecto' => ['required', Rule::in(array_keys(CasosCalendarioInstitucional::efectos()))],
            'casoImpacto.alcance' => ['required', Rule::in(['INSTITUCIONAL', 'TURNO', 'GRUPO'])],
            'casoImpacto.grupos' => ['exclude_unless:casoImpacto.alcance,GRUPO', 'required', 'array', 'min:1', 'max:100'],
            'casoImpacto.grupos.*' => ['string', 'distinct', Rule::in(array_column($this->gruposDisponibles(), 'valor'))],
            'casoImpacto.turnos' => ['exclude_unless:casoImpacto.alcance,TURNO', 'required', 'array', 'min:1', 'max:10'],
            'casoImpacto.turnos.*' => ['string', 'distinct', Rule::in(array_column($this->turnosDisponibles(), 'valor'))],
            'casoImpacto.jornada' => ['required', Rule::in(['COMPLETA', 'PARCIAL'])],
            'casoImpacto.hora_inicio' => ['exclude_unless:casoImpacto.jornada,PARCIAL', 'required', 'date_format:H:i'],
            'casoImpacto.hora_fin' => ['exclude_unless:casoImpacto.jornada,PARCIAL', 'required', 'date_format:H:i', 'after:casoImpacto.hora_inicio'],
            'casoImpacto.inicio' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.substr($gestion['fecha_inicio'], 0, 10), 'before_or_equal:'.substr($gestion['fecha_fin'], 0, 10)],
            'casoImpacto.fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:casoImpacto.inicio', 'before_or_equal:'.substr($gestion['fecha_fin'], 0, 10)],
            'casoImpacto.motivo' => ['required', 'string', 'min:10', 'max:1000'],
            'casoImpacto.documento' => ['nullable', 'string', 'max:250'],
            'casoImpacto.url_documento' => ['nullable', 'url:http,https', 'max:2000'],
            'casoImpacto.zona' => ['nullable', 'string', 'max:150'],
            'casoImpacto.responsable' => ['nullable', Rule::in(array_column($this->docentesDisponibles(), 'valor'))],
            'casoImpacto.fecha_original' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.$gestion['anio'].'-01-01', 'before_or_equal:'.$gestion['anio'].'-12-31'],
        ], [
            'casoImpacto.inicio.required' => 'Elige cuándo empieza el caso.',
            'casoImpacto.fin.required' => 'Elige cuándo termina el caso.',
            'casoImpacto.inicio.after_or_equal' => 'La fecha debe pertenecer a la gestión.',
            'casoImpacto.inicio.before_or_equal' => 'La fecha debe pertenecer a la gestión.',
            'casoImpacto.fin.after_or_equal' => 'El cierre debe ser igual o posterior al inicio.',
            'casoImpacto.fin.before_or_equal' => 'El cierre debe pertenecer a la gestión.',
            'casoImpacto.motivo.required' => 'Describe el caso para orientar la revisión.',
            'casoImpacto.motivo.min' => 'Explica el caso con al menos 10 caracteres.',
            'casoImpacto.grupos.required' => 'Elige el grupo que participará; el resto del colegio conserva sus clases.',
            'casoImpacto.grupos.*.in' => 'Selecciona un grupo de esta gestión.',
            'casoImpacto.turnos.required' => 'Selecciona el turno afectado.',
            'casoImpacto.hora_inicio.required' => 'Indica a qué hora empieza el cambio.',
            'casoImpacto.hora_fin.required' => 'Indica cuándo termina el cambio.',
            'casoImpacto.hora_fin.after' => 'La hora final debe ser posterior a la inicial.',
            'casoImpacto.url_documento.url' => 'Escribe un enlace válido que empiece por https:// o http://.',
            'casoImpacto.fecha_recuperacion.required' => 'Elige la fecha que propones para recuperar las clases.',
            'casoImpacto.fecha_recuperacion.date_format' => 'Elige una fecha válida para recuperar las clases.',
            'casoImpacto.fecha_recuperacion.after_or_equal' => 'La recuperación no puede ser anterior al caso.',
            'casoImpacto.fecha_recuperacion.before_or_equal' => 'La recuperación debe quedar dentro de la gestión activa.',
            'casoImpacto.hora_recuperacion_inicio.required' => 'Indica a qué hora empezaría la recuperación.',
            'casoImpacto.hora_recuperacion_inicio.date_format' => 'Elige una hora de inicio válida.',
            'casoImpacto.hora_recuperacion_fin.required' => 'Indica a qué hora terminaría la recuperación.',
            'casoImpacto.hora_recuperacion_fin.date_format' => 'Elige una hora de cierre válida.',
            'casoImpacto.hora_recuperacion_fin.after' => 'La hora final de recuperación debe ser posterior a la inicial.',
            'casoImpacto.observacion_recuperacion.required_if' => 'Cuéntanos qué falta coordinar para definir el día de recuperación.',
            'casoImpacto.observacion_recuperacion.min' => 'Describe la coordinación pendiente con al menos 10 caracteres.',
            'casoImpacto.observacion_recuperacion.max' => 'Resume la observación en un máximo de 1000 caracteres.',
            'casoImpacto.plan_recuperacion.required' => 'Explica cómo se recuperarán las clases virtuales y cómo participará el alumnado.',
            'casoImpacto.plan_recuperacion.min' => 'Describe el plan de recuperación con al menos 10 caracteres.',
            'casoImpacto.enlace_recuperacion.url' => 'Usa un enlace HTTPS válido para el aula o la reunión.',
        ])['casoImpacto'];
        $datos = array_replace(CasosCalendarioInstitucional::vacio(), $datos);
        if ($datos['tipo']==='VISITA_UNIVERSIDAD' && $datos['estudiar_universidad']==='SI' && $datos['universidad_url']) {
            $identidad = app(UniversidadesCalendario::class)->identificar($datos['universidad_url']);
            if ($identidad['bloqueada']) throw ValidationException::withMessages(['casoImpacto.universidad_url'=>$identidad['mensaje']]);
            if (($identidad['conocida'] ?? false) && ! in_array($datos['universidad'], ['', 'OTRA', $identidad['catalogo']['valor']], true)) throw ValidationException::withMessages(['casoImpacto.universidad_url'=>'La página corresponde a '.$identidad['titulo'].'. Revisa la universidad elegida para la visita.']);
            $datos['universidad_url'] = $identidad['url'];
        }
        if ($datos['recuperacion'] === 'PROPUESTA') {
            $revision = app(RecuperacionCalendario::class)->revisar($datos,$gestion);
            if ($revision['bloqueos']) throw ValidationException::withMessages(collect($revision['bloqueos'])->mapWithKeys(fn($m,$k)=>['casoImpacto.'.$k=>$m])->all());
        }
        if ($comprobarDuracion && $datos['recuperacion'] === 'PROPUESTA') {
            $impacto = app(CalendarioAcademicoInteligente::class)->analizarImpactoEvento($datos + ['cod_gea'=>$gestion['id']]);
            if (! $impacto['puede_continuar']) throw ValidationException::withMessages(['casoImpacto.hora_recuperacion_fin'=>$impacto['bloqueos']]);
        }
        return $datos;
    }

    private function esGestionActiva(?array $gestion): bool
    {
        return $gestion && $gestion['estado'] === 'ACTIVA' && DB::table('gestion_academica')->whereIn('est_gea', ['ACTIVA', 'ACTIVO'])->count() === 1;
    }

    private function gruposDisponibles(): array
    {
        return DB::table('grupo_academico as g')->join('curso as c', 'c.cod_cur', '=', 'g.cod_cur')
            ->join('paralelo as p', 'p.cod_par', '=', 'g.cod_par')->join('turno as t', 't.cod_tur', '=', 'g.cod_tur')
            ->where('g.cod_gea', $this->selectedGestionId)->orderBy('c.nom_cur')->orderBy('p.nom_par')
            ->get(['g.cod_gac', 'c.nom_cur', 'p.nom_par', 't.nom_tur'])
            ->map(fn ($g) => ['valor' => $g->cod_gac, 'etiqueta' => mb_strtoupper($g->nom_cur.' '.$g->nom_par.' · '.$g->nom_tur)])->all();
    }

    private function turnosDisponibles(): array
    {
        return DB::table('turno')->whereIn('cod_tur', DB::table('grupo_academico')->where('cod_gea', $this->selectedGestionId)->select('cod_tur'))
            ->orderBy('nom_tur')->get(['cod_tur', 'nom_tur'])->map(fn ($t) => ['valor' => $t->cod_tur, 'etiqueta' => mb_strtoupper($t->nom_tur)])->all();
    }

    private function docentesDisponibles(): array
    {
        return DB::table('docente as d')->join('personal_institucional as pin', 'pin.cod_pin', '=', 'd.cod_pin')
            ->join('persona as p', 'p.cod_per', '=', 'pin.cod_per')
            ->where(fn ($q) => $q->whereIn('d.cod_doc', DB::table('plan_asignatura')->whereIn('cod_gac', DB::table('grupo_academico')->where('cod_gea', $this->selectedGestionId)->select('cod_gac'))->select('cod_doc'))
                ->orWhereIn('d.cod_doc', DB::table('plan_especialidad')->whereIn('cod_gac', DB::table('grupo_academico')->where('cod_gea', $this->selectedGestionId)->select('cod_gac'))->select('cod_doc')))
            ->orderBy('p.ape_pat_per')->get(['d.cod_doc', 'p.nom_per', 'p.ape_pat_per', 'p.ape_mat_per'])
            ->map(fn ($d) => ['valor' => $d->cod_doc, 'etiqueta' => mb_strtoupper(trim($d->nom_per.' '.$d->ape_pat_per.' '.$d->ape_mat_per))])->all();
    }

    public function usarDespuesRecreo(): void
    {
        $this->autorizar();
        $grupo = $this->casoImpacto['alcance'] === 'GRUPO' ? $this->casoImpacto['grupos'] : [];
        $turno = $this->casoImpacto['alcance'] === 'TURNO' ? $this->casoImpacto['turnos'] : [];
        if (! $grupo && ! $turno) {
            $this->addError('casoImpacto.hora_inicio', 'Elige un grupo o turno para consultar su recreo.');
            return;
        }
        $q = DB::table('horario as h')->join('grupo_academico as g', 'g.cod_gac', '=', 'h.cod_gac')->join('horario_bloque as b', 'b.cod_pho', '=', 'h.cod_pho')
            ->where('g.cod_gea', $this->selectedGestionId)->where('h.est_hor', '<>', 'ANULADO')->where('b.est_hbl', 'ACTIVO')
            ->when($grupo, fn ($q) => $q->whereIn('g.cod_gac', $grupo))->when($turno, fn ($q) => $q->whereIn('g.cod_tur', $turno))
            ->when($this->casoImpacto['inicio'], fn ($q) => $q->where('h.fii_hor', '<=', $this->casoImpacto['inicio'])->where(fn ($q) => $q->whereNull('h.ffi_hor')->orWhere('h.ffi_hor', '>=', $this->casoImpacto['inicio'])));
        $recreos = (clone $q)->where('b.tip_hbl', 'RECREO')->distinct()->pluck('b.hor_fin_hbl');
        if ($recreos->count() !== 1) {
            $this->addError('casoImpacto.hora_inicio', 'No hay una única hora de recreo para esta selección. Indica el horario del caso manualmente.');
            return;
        }
        $this->casoImpacto['hora_inicio'] = substr($recreos->first(), 0, 5);
        $this->casoImpacto['hora_fin'] = substr((clone $q)->where('b.tip_hbl', 'CLASE')->max('b.hor_fin_hbl') ?? '', 0, 5);
        $this->casoImpacto['jornada'] = 'PARCIAL';
        $this->resultadoImpacto = [];
        $this->resetValidation();
    }

    public function guardarBorrador(): void
    {
        $this->autorizar();
        $gestion = $this->gestionSeleccionada;
        if (! $gestion) return;
        $datos = $this->validarCaso($gestion, true);
        $this->dispatch('calendario-borrador-validado', caso: $datos);
    }

    public function restaurarBorrador(array $caso): void
    {
        $this->autorizar();
        $vacio = CasosCalendarioInstitucional::vacio();
        foreach ($vacio as $campo => $valor) {
            if (is_array($valor) && isset($caso[$campo]) && is_array($caso[$campo])) $vacio[$campo] = array_slice(array_values(array_filter($caso[$campo], fn ($v) => is_string($v) && strlen($v) <= 50)), 0, 100);
            if (isset($caso[$campo]) && is_string($caso[$campo]) && mb_strlen($caso[$campo]) <= 2000) $vacio[$campo] = $caso[$campo];
        }
        if (! $vacio['grupos'] && $vacio['grupo']) $vacio['grupos'] = [$vacio['grupo']];
        if (! $vacio['turnos'] && $vacio['turno']) $vacio['turnos'] = [$vacio['turno']];
        if ($vacio['inicio'] !== $vacio['fin']) $vacio['periodo'] = 'RANGO';
        $this->casoImpacto = $vacio;
        $this->resultadoImpacto = [];
        $this->seccion = 'impacto';
        $this->resetValidation();
    }

    public function sugerirCelebracion(string $sentido): void
    {
        $this->autorizar();
        if (! in_array($sentido, ['previous', 'next'], true) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->casoImpacto['fecha_original'])) return;
        try {
            $fecha = \Carbon\CarbonImmutable::createFromFormat('!Y-m-d', $this->casoImpacto['fecha_original']);
            $sugerida = $sentido === 'previous' ? $fecha->previous(\Carbon\Carbon::FRIDAY) : $fecha->next(\Carbon\Carbon::MONDAY);
            $this->usarFechaImpacto($sugerida->toDateString());
        } catch (\Throwable) {
            $this->addError('casoImpacto.fecha_original', 'Revisa la fecha conmemorativa antes de elegir la celebración.');
        }
    }

    public function descargarCaso()
    {
        $this->autorizar();
        $gestion = $this->gestionSeleccionada;
        abort_unless($gestion, 422);
        $datos = $this->validarCaso($gestion, true);
        $json = json_encode(['version' => 1, 'estado' => 'BORRADOR', 'gestion' => $gestion['anio'], 'caso' => $datos,
            'observacion_gestion' => ['estado'=>$datos['recuperacion'], 'detalle'=>$datos['observacion_recuperacion'], 'modalidad'=>$datos['modalidad_recuperacion'], 'plan'=>$datos['plan_recuperacion'], 'medio'=>$datos['medio_recuperacion'], 'fecha'=>$datos['fecha_recuperacion'] ?? null, 'hora_inicio'=>$datos['hora_recuperacion_inicio'] ?? null, 'hora_fin'=>$datos['hora_recuperacion_fin'] ?? null],
            'nota' => 'Propuesta para revisión. No registra ni confirma un evento institucional.'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        return response()->streamDownload(fn () => print($json), 'caso-calendario-'.$gestion['anio'].'.json', ['Content-Type' => 'application/json']);
    }

    public function usarFechaImpacto(string $fecha): void
    {
        $this->autorizar();
        $gestion = $this->gestionSeleccionada;
        if (! $this->esGestionActiva($gestion) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || $fecha < substr($gestion['fecha_inicio'], 0, 10) || $fecha > substr($gestion['fecha_fin'], 0, 10)) {
            return;
        }
        $this->seccion = 'impacto';
        $this->casoImpacto['inicio'] = $this->casoImpacto['fin'] = $fecha;
        $this->resultadoImpacto = [];
        $this->resetValidation();
        $this->dispatch('academica-enfocar-impacto');
    }


    public function usarHoy(): void
    {
        $this->usarFechaImpacto(now('America/La_Paz')->toDateString());
        $this->casoImpacto['periodo'] = 'DIA';
    }

    public function sugerirSabadoRecuperacion(): void
    {
        $this->autorizar();
        $gestion = $this->gestionSeleccionada;
        if (! $this->esGestionActiva($gestion)) return;
        $limites = app(RecuperacionCalendario::class)->limites($this->casoImpacto,$gestion);
        if (! $limites['disponible']) {
            $this->addError('casoImpacto.fecha_recuperacion','Revisa las fechas y los grupos: no hay un rango válido en el mismo trimestre.');
            return;
        }
        $base = \Carbon\CarbonImmutable::parse($limites['minimo']);
        $fecha = $base->isSaturday() ? $base : $base->next(\Carbon\Carbon::SATURDAY);
        if ($fecha->toDateString() > $limites['maximo']) {
            $this->addError('casoImpacto.fecha_recuperacion','El siguiente sábado queda fuera del trimestre. Elige una fecha anterior a su cierre.');
            return;
        }
        $this->casoImpacto['fecha_recuperacion'] = $fecha->toDateString();
        $this->resultadoImpacto = [];
        $this->resetValidation();
    }

    public function comprobarUniversidad(): void
    {
        $this->autorizar();
        Gate::authorize('conocimiento.ver');
        Gate::authorize('conocimiento.proponer');
        abort_unless($this->casoImpacto['tipo'] === 'VISITA_UNIVERSIDAD' && $this->casoImpacto['estudiar_universidad'] === 'SI', 422);
        $this->revisionUniversidad = [];
        $datos = $this->validate(['casoImpacto.universidad_url' => ['required','url:https','max:2000',new UrlFuenteUniversitaria]], ['casoImpacto.universidad_url.required'=>'Escribe la página oficial que quieres revisar.']);
        $clave = 'calendario-universidad:'.auth()->id();
        if (RateLimiter::tooManyAttempts($clave, 6)) {
            $this->addError('casoImpacto.universidad_url', 'Ya revisamos varios enlaces. Espera un minuto antes de intentarlo otra vez.');
            return;
        }
        RateLimiter::hit($clave, 60);
        $original = $datos['casoImpacto']['universidad_url'];
        $identidad = app(UniversidadesCalendario::class)->identificar($original);
        if ($identidad['bloqueada']) {
            $this->revisionUniversidad = ['mensaje'=>$identidad['mensaje'],'verificada'=>false,'bloqueada'=>true,'avisos'=>[]];
            return;
        }
        if (($identidad['conocida'] ?? false) && ! in_array($this->casoImpacto['universidad'], ['', 'OTRA', $identidad['catalogo']['valor']], true)) {
            $this->addError('casoImpacto.universidad_url', 'La página corresponde a '.$identidad['titulo'].'. Revisa la universidad elegida para la visita.');
            return;
        }
        $this->casoImpacto['universidad_url'] = $identidad['url'];
        $respuesta = app(AporteIngenierilClient::class)->inspectUniversity(['url'=>$identidad['url']]);
        $this->revisionUniversidad = [
            'mensaje'=>$respuesta->available ? $respuesta->data['message'] : $identidad['mensaje'].' No pudimos leer la página en este momento; su contenido sigue pendiente de comprobación.',
            'titulo'=>$respuesta->available ? ($respuesta->data['title'] ?? $identidad['titulo'] ?? '') : ($identidad['titulo'] ?? ''),
            'verificada'=>$respuesta->available && ($respuesta->data['can_use'] ?? false) === true,
            'bloqueada'=>$respuesta->available && $respuesta->data['status']==='BLOQUEADA',
            'url'=>$identidad['url'], 'original'=>$original,
            'conocida'=>$identidad['conocida'] ?? false,
            'institucion'=>$identidad['titulo'] ?? ($respuesta->data['assessment']['recognized_institution'] ?? ''),
            'resumen'=>$respuesta->available ? ($respuesta->data['excerpt'] ?? '') : '',
            'carreras'=>$respuesta->available ? ($respuesta->data['career_links'] ?? []) : [],
            'catalogo'=>$identidad['catalogo'] ?? [],
            'avisos'=>$respuesta->available ? ($respuesta->data['warnings'] ?? []) : [],
        ];
    }

    private function alerta(string $nivel, string $titulo, string $mensaje): void
    {
        $this->avisosSesion = array_slice(array_merge($this->avisosSesion, [compact('nivel', 'titulo', 'mensaje')]), -8);
    }
}
