<?php

namespace App\Services\AulaVirtual;

use App\Models\AulaVirtual\OrientacionActividad;
use App\Models\AulaVirtual\OrientacionPregunta;
use App\Models\AulaVirtual\OrientacionRespuesta;
use App\Models\AulaVirtual\OrientacionResultado;
use App\Models\Estudiante;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrientacionService
{
    public const MENSAJE_ORIENTATIVO = 'Este resultado es orientativo y debe complementarse con tu rendimiento académico, tu formación técnica y el acompañamiento docente.';

    public function __construct(private readonly CursoVirtualService $cursos) {}

    public function resumen(User $user): array
    {
        $estudiante = $this->cursos->estudianteDeUsuario($user);
        $actividad = $estudiante ? OrientacionActividad::with('resultado.carreras')->where('cod_est', $estudiante->cod_est)->latest()->first() : null;
        $resultado = $actividad?->resultado?->load('carreras');

        return [
            'actividad' => $actividad,
            'resultado' => $resultado,
            'estado' => $this->estadoVisible($actividad?->estado ?? 'pendiente'),
            'avance' => $actividad?->avance ?? 0,
            'perfil' => $resultado ? $this->dimensiones()[$resultado->perfil_predominante] ?? 'En proceso' : 'En proceso',
            'compatibilidad_principal' => $resultado?->compatibilidad_principal,
            'carreras' => $resultado?->carreras ?? collect(),
            'mensaje' => self::MENSAJE_ORIENTATIVO,
        ];
    }

    public function actividadActual(Estudiante $estudiante): OrientacionActividad
    {
        $user = auth()->user();
        abort_unless($user && $this->cursos->estudianteDeUsuario($user)?->cod_est === $estudiante->cod_est, 403);
        $actividad = OrientacionActividad::query()
            ->with('resultado.carreras', 'respuestas.pregunta')
            ->where('cod_est', $estudiante->cod_est)
            ->whereIn('estado', ['pendiente', 'en_proceso', 'finalizado', 'revisado', 'requiere_seguimiento'])
            ->latest()
            ->first();

        if ($actividad) {
            return $actividad;
        }

        return OrientacionActividad::create([
            'cod_est' => $estudiante->cod_est,
            'cod_gea' => $estudiante->inscripciones()->latest('fei_ins')->value('cod_gea'),
            'estado' => 'pendiente',
            'avance' => 0,
        ]);
    }

    public function actividadExistente(Estudiante $student): ?OrientacionActividad
    {
        $user = auth()->user();
        abort_unless($user && $this->cursos->estudianteDeUsuario($user)?->cod_est === $student->cod_est, 403);

        return OrientacionActividad::with('resultado.carreras', 'respuestas.pregunta')->where('cod_est', $student->cod_est)->latest()->first();
    }

    public function preguntas(): Collection
    {
        return OrientacionPregunta::query()
            ->where('visible', true)
            ->orderBy('orden')
            ->get();
    }

    public function respuestasGuardadas(OrientacionActividad $actividad): array
    {
        $this->authorizeActivity($actividad);

        return $actividad->respuestas()
            ->pluck('valor_likert', 'orientacion_pregunta_id')
            ->map(fn ($valor) => (int) $valor)
            ->all();
    }

    public function guardarAvance(OrientacionActividad $actividad, array $respuestas): OrientacionActividad
    {
        $this->authorizeActivity($actividad);
        abort_unless(in_array($actividad->estado, ['pendiente', 'en_proceso'], true), 422, 'El resultado finalizado se conserva en el historial.');
        $preguntas = $this->preguntas();

        DB::transaction(function () use ($actividad, $respuestas, $preguntas) {
            $actividad = OrientacionActividad::lockForUpdate()->findOrFail($actividad->id);
            $this->authorizeActivity($actividad);
            abort_unless(in_array($actividad->estado, ['pendiente', 'en_proceso'], true), 422);
            foreach ($preguntas as $pregunta) {
                $valor = $respuestas[$pregunta->id] ?? null;

                if (! in_array((int) $valor, [1, 2, 3, 4, 5], true)) {
                    continue;
                }

                OrientacionRespuesta::updateOrCreate(
                    [
                        'orientacion_actividad_id' => $actividad->id,
                        'orientacion_pregunta_id' => $pregunta->id,
                    ],
                    [
                        'cod_est' => $actividad->cod_est,
                        'valor_likert' => (int) $valor,
                    ]
                );
            }

            $contestadas = $actividad->respuestas()->count();
            $total = max(1, $preguntas->count());

            $actividad->forceFill([
                'estado' => $contestadas >= $total ? $actividad->estado : 'en_proceso',
                'avance' => (int) round(($contestadas / $total) * 100),
                'iniciado_at' => $actividad->iniciado_at ?? now(),
            ])->save();
        });

        return $actividad->refresh()->load('respuestas.pregunta');
    }

    public function finalizar(OrientacionActividad $actividad, array $respuestas): OrientacionResultado
    {
        $actividad = $this->guardarAvance($actividad, $respuestas);
        $preguntas = $this->preguntas();

        abort_if($preguntas->isEmpty(), 409, 'No hay preguntas aprobadas para esta actividad.');
        abort_if($actividad->respuestas()->count() < $preguntas->count(), 422, 'Responde todas las preguntas visibles antes de finalizar.');

        return DB::transaction(function () use ($actividad) {
            $actividad = OrientacionActividad::lockForUpdate()->findOrFail($actividad->id);
            $this->authorizeActivity($actividad);
            abort_unless(in_array($actividad->estado, ['pendiente', 'en_proceso'], true), 422, 'La evaluación finalizada se conserva en el historial.');
            $respuestas = $actividad->respuestas()->with('pregunta')->get();
            $porcentajes = [];

            foreach ($this->dimensiones() as $codigo => $nombre) {
                $valores = $respuestas
                    ->filter(fn (OrientacionRespuesta $respuesta) => $respuesta->pregunta?->dimension === $codigo)
                    ->pluck('valor_likert');

                $promedio = $valores->isNotEmpty() ? $valores->avg() : 0;
                $porcentajes[$codigo] = round(($promedio / 5) * 100, 2);
            }

            arsort($porcentajes);
            $perfilPredominante = array_key_first($porcentajes);
            $compatibilidadPrincipal = $porcentajes[$perfilPredominante] ?? 0;

            $resultado = OrientacionResultado::updateOrCreate(
                ['orientacion_actividad_id' => $actividad->id],
                [
                    'cod_est' => $actividad->cod_est,
                    ...$porcentajes,
                    'perfil_predominante' => $perfilPredominante,
                    'interpretacion' => self::MENSAJE_ORIENTATIVO,
                    'compatibilidad_principal' => $compatibilidadPrincipal,
                    'estado' => 'generado',
                ]
            );

            // La afinidad con carreras requiere un contrato especializado aprobado; no se fabrica un porcentaje.

            $actividad->forceFill([
                'estado' => 'finalizado',
                'avance' => 100,
                'finalizado_at' => now(),
            ])->save();

            BitacoraService::registrar(accion: 'FINALIZAR_EXPLORADOR', tabla: 'orientacion_actividades',
                registro: (string) $actividad->id, modulo: 'Orientación', valoresNuevos: ['estado' => 'finalizado']);

            return $resultado->refresh()->load('carreras');
        });
    }

    public function dimensiones(): array
    {
        return [
            'tecnico_practico' => 'Técnico-práctico',
            'analitico_cientifico' => 'Analítico-científico',
            'creativo_expresivo' => 'Creativo-expresivo',
            'social_comunitario' => 'Social-comunitario',
            'liderazgo_emprendimiento' => 'Liderazgo-emprendimiento',
            'organizativo_administrativo' => 'Organizativo-administrativo',
        ];
    }

    private function authorizeActivity(OrientacionActividad $activity): void
    {
        $user = auth()->user();
        abort_unless($user && $this->cursos->estudianteDeUsuario($user)?->cod_est === $activity->cod_est
            && $user->can('Orientacion_Academica_Profesional'), 403);
    }

    public function carrerasBase(): array
    {
        return [
            'tecnico_practico' => ['Ingeniería Electrónica', 'Ingeniería Mecatrónica', 'Sistemas Informáticos', 'Electricidad Industrial', 'Telecomunicaciones'],
            'analitico_cientifico' => ['Ingeniería de Sistemas', 'Ingeniería Industrial', 'Estadística', 'Matemática', 'Ciencia de Datos'],
            'creativo_expresivo' => ['Diseño Gráfico', 'Comunicación Social', 'Producción Audiovisual', 'Arquitectura', 'Marketing Digital'],
            'social_comunitario' => ['Educación', 'Psicología', 'Trabajo Social', 'Enfermería', 'Comunicación para el Desarrollo'],
            'liderazgo_emprendimiento' => ['Administración de Empresas', 'Ingeniería Comercial', 'Marketing', 'Comercio Internacional', 'Gestión de Proyectos'],
            'organizativo_administrativo' => ['Contaduría Pública', 'Administración', 'Secretariado Ejecutivo', 'Finanzas', 'Gestión Pública'],
        ];
    }

    public function estadoVisible(string $estado): string
    {
        return match ($estado) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'finalizado' => 'Revisado',
            'revisado' => 'Revisado',
            'requiere_seguimiento' => 'Requiere seguimiento',
            default => 'En proceso',
        };
    }
}
