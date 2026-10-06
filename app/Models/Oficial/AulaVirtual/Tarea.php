<?php

namespace App\Models\Oficial\AulaVirtual;

use App\Models\Oficial\Academico\Docente;
use App\Models\Oficial\Academico\PeriodoEvaluacion;
use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarea extends Model
{
    use CodigoInstitucional;

    protected $table = 'tarea';

    protected $primaryKey = 'cod_tar';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_tar', // Código único de la tarea
        'cod_cla', // Clase virtual a la que pertenece la tarea
        'cod_doc', // Docente responsable de la tarea

        'tit_tar', // Título visible de la tarea
        'des_tar', // Descripción, instrucciones o consigna de la tarea
        'tip_tar', // Tipo: TAREA, PRACTICA, PROYECTO, INVESTIGACION, LABORATORIO o EVALUACION

        'fec_pub_tar', // Fecha y hora de publicación
        'fec_lim_tar', // Fecha y hora límite de entrega

        'pun_max_tar', // Puntaje máximo de la tarea
        'perm_ent_tardia', // Permite entregas después de la fecha límite

        'est_tar', // Estado: BORRADOR, PUBLICADA, CERRADA o ANULADA
        'cod_sec',
        'cod_pev',
        'ape_tar',
        'cor_tar',
        'int_tar',
        'arc_tar',
        'tam_tar',
    ];

    protected $casts = [
        'fec_pub_tar' => 'datetime',
        'fec_lim_tar' => 'datetime',
        'pun_max_tar' => 'decimal:2',
        'perm_ent_tardia' => 'boolean',
        'ape_tar' => 'datetime',
        'cor_tar' => 'datetime',
        'int_tar' => 'integer',
        'arc_tar' => 'integer',
        'tam_tar' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tarea $tarea) {

            if (! $tarea->fec_pub_tar && $tarea->est_tar === 'PUBLICADA') {
                $tarea->fec_pub_tar = now();
            }
        });
    }

    public function claseVirtual(): BelongsTo
    {
        return $this->belongsTo(ClaseVirtual::class, 'cod_cla', 'cod_cla');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'cod_doc', 'cod_doc');
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(TareaMaterial::class, 'cod_tar', 'cod_tar');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(EntregaTarea::class, 'cod_tar', 'cod_tar');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(CalificacionTarea::class, 'cod_tar', 'cod_tar');
    }

    public function scopeBorradores($query)
    {
        return $query->where('est_tar', 'BORRADOR');
    }

    public function scopePublicadas($query)
    {
        return $query->where('est_tar', 'PUBLICADA');
    }

    public function scopeCerradas($query)
    {
        return $query->where('est_tar', 'CERRADA');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('est_tar', 'ANULADA');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tip_tar', $tipo);
    }

    public function scopeVigentes($query)
    {
        return $query->where('est_tar', 'PUBLICADA')
            ->where(function ($subQuery) {
                $subQuery
                    ->whereNull('fec_lim_tar')
                    ->orWhere('fec_lim_tar', '>=', now());
            });
    }

    public function esBorrador(): bool
    {
        return $this->est_tar === 'BORRADOR';
    }

    public function estaPublicada(): bool
    {
        return $this->est_tar === 'PUBLICADA';
    }

    public function estaCerrada(): bool
    {
        return $this->est_tar === 'CERRADA';
    }

    public function estaAnulada(): bool
    {
        return $this->est_tar === 'ANULADA';
    }

    public function permiteEntregaTardia(): bool
    {
        return (bool) $this->perm_ent_tardia;
    }

    public function vencida(): bool
    {
        return $this->fec_lim_tar !== null && now()->greaterThan($this->fec_lim_tar);
    }

    public function puedeRecibirEntregas(): bool
    {
        if (! $this->estaPublicada()) {
            return false;
        }

        if (($this->ape_tar && now()->lessThan($this->ape_tar))
            || ($this->cor_tar && now()->greaterThanOrEqualTo($this->cor_tar))) {
            return false;
        }

        if (! $this->vencida()) {
            return true;
        }

        return $this->permiteEntregaTardia();
    }

    public function publicar(): void
    {
        $this->forceFill([
            'est_tar' => 'PUBLICADA',
            'fec_pub_tar' => $this->fec_pub_tar ?? now(),
        ])->save();
    }

    public function cerrar(): void
    {
        $this->forceFill([
            'est_tar' => 'CERRADA',
        ])->save();
    }

    public function anular(): void
    {
        $this->forceFill([
            'est_tar' => 'ANULADA',
        ])->save();
    }

    public function periodoEvaluacion(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluacion::class, 'cod_pev', 'cod_pev');
    }

    public function tareaMaterialRegistros(): HasMany
    {
        return $this->hasMany(TareaMaterial::class, 'cod_tar', 'cod_tar');
    }

    public function entregaTareaRegistros(): HasMany
    {
        return $this->hasMany(EntregaTarea::class, 'cod_tar', 'cod_tar');
    }

    public function registroActividadClaseRegistros(): HasMany
    {
        return $this->hasMany(RegistroActividadClase::class, 'tar_rac', 'cod_tar');
    }

    /** La FK compuesta en PostgreSQL garantiza también el contexto de clase. */
    public function seccionClase(): BelongsTo
    {
        return $this->belongsTo(SeccionClase::class, 'cod_sec', 'cod_sec');
    }
}
