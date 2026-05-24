<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InscripcionEstudiante extends Model
{
    protected $table = 'inscripcion_estudiante';
    protected $primaryKey = 'cod_ins';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_ins',
        'cod_est',
        'cod_gea',
        'cod_cur',
        'cod_par',
        'cod_tur',
        'fei_ins',
        'tip_ins',
        'con_ins',
        'est_ins',
        'pro_ins',
        'obs_ins',
        'mot_obs_ins',
        'doc_com_ins',
        'sob_aut_ins',
        'sie_ins',
        'fec_sie_ins',
        'cod_esp_tec',
        'est_esp_tec_ins',
        'obs_esp_tec_ins',
        'fec_con_ins',
        'fec_anu_ins',
        'mot_anu_ins',
        'fec_ret_ins',
        'mot_ret_ins',
    ];

    protected $casts = [
        'fei_ins' => 'date',
        'doc_com_ins' => 'boolean',
        'sob_aut_ins' => 'boolean',
        'sie_ins' => 'boolean',
        'fec_sie_ins' => 'datetime',
        'fec_con_ins' => 'datetime',
        'fec_anu_ins' => 'datetime',
        'fec_ret_ins' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (InscripcionEstudiante $inscripcion) {
            if (! empty($inscripcion->cod_ins)) {
                return;
            }

            $ultimo = self::query()
                ->where('cod_ins', 'like', 'INS_%')
                ->orderByDesc('cod_ins')
                ->value('cod_ins');

            $numero = $ultimo
                ? ((int) str_replace('INS_', '', $ultimo)) + 1
                : 1;

            $inscripcion->cod_ins = 'INS_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
        });
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'cod_est', 'cod_est');
    }

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademica::class, 'cod_gea', 'cod_gea');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'cod_cur', 'cod_cur');
    }

    public function paralelo(): BelongsTo
    {
        return $this->belongsTo(Paralelo::class, 'cod_par', 'cod_par');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'cod_tur', 'cod_tur');
    }

    public function especialidadTecnica(): BelongsTo
    {
        return $this->belongsTo(EspecialidadTecnica::class, 'cod_esp_tec', 'cod_esp');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoInscripcionEstudiante::class, 'cod_ins', 'cod_ins');
    }

    public function documentosPendientes(): HasMany
    {
        return $this->hasMany(DocumentoInscripcionEstudiante::class, 'cod_ins', 'cod_ins')
            ->where('est_die', 'PENDIENTE');
    }

    public function documentosObservados(): HasMany
    {
        return $this->hasMany(DocumentoInscripcionEstudiante::class, 'cod_ins', 'cod_ins')
            ->where('est_die', 'OBSERVADO');
    }

    public function getNombreVisibleAttribute(): string
    {
        $estudiante = $this->estudiante?->persona
            ? trim(
                ($this->estudiante->persona->nom_per ?? '') . ' ' .
                ($this->estudiante->persona->ape_pat_per ?? '') . ' ' .
                ($this->estudiante->persona->ape_mat_per ?? '')
            )
            : 'Estudiante sin datos';

        $gestion = $this->gestionAcademica?->ani_gea ?? 'Sin gestión';
        $curso = $this->curso?->nom_cur ?? 'Sin curso';
        $paralelo = $this->paralelo?->nom_par ?? 'Sin paralelo';

        return "{$estudiante} - {$curso} {$paralelo} - Gestión {$gestion}";
    }

    public function getEstadoDocumentalAttribute(): string
    {
        if ($this->doc_com_ins) {
            return 'COMPLETO';
        }

        if ($this->documentosObservados()->exists()) {
            return 'OBSERVADO';
        }

        if ($this->documentosPendientes()->exists()) {
            return 'PENDIENTE';
        }

        return 'SIN_DOCUMENTOS';
    }

    public function getRequiereRegularizacionAttribute(): bool
    {
        return in_array($this->con_ins, ['OBSERVADA', 'CONDICIONAL', 'PROVISIONAL'], true)
            || $this->estado_documental !== 'COMPLETO';
    }

    public function scopeDeGestion($query, string $codGestion)
    {
        return $query->where('cod_gea', $codGestion);
    }

    public function scopeDeEstudiante($query, string $codEstudiante)
    {
        return $query->where('cod_est', $codEstudiante);
    }

    public function scopeDeCurso($query, string $codCurso)
    {
        return $query->where('cod_cur', $codCurso);
    }

    public function scopeDeParalelo($query, string $codParalelo)
    {
        return $query->where('cod_par', $codParalelo);
    }

    public function scopeDeTurno($query, string $codTurno)
    {
        return $query->where('cod_tur', $codTurno);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tip_ins', strtoupper($tipo));
    }

    public function scopePorCondicion($query, string $condicion)
    {
        return $query->where('con_ins', strtoupper($condicion));
    }

    public function scopePorEstado($query, string $estado)
    {
        return $query->where('est_ins', strtoupper($estado));
    }

    public function scopePendientes($query)
    {
        return $query->where('est_ins', 'PENDIENTE');
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('est_ins', 'CONFIRMADA');
    }

    public function scopeActivas($query)
    {
        return $query->where('est_ins', 'ACTIVA');
    }

    public function scopeObservadas($query)
    {
        return $query->where('est_ins', 'OBSERVADA');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('est_ins', 'ANULADA');
    }

    public function scopeRetiradas($query)
    {
        return $query->where('est_ins', 'RETIRADA');
    }

    public function scopeReportadasSie($query)
    {
        return $query->where('sie_ins', true);
    }

    public function scopeNoReportadasSie($query)
    {
        return $query->where('sie_ins', false);
    }

    public function scopeConDocumentosCompletos($query)
    {
        return $query->where('doc_com_ins', true);
    }

    public function scopeConDocumentosPendientes($query)
    {
        return $query->where('doc_com_ins', false);
    }

    public function estaPendiente(): bool
    {
        return $this->est_ins === 'PENDIENTE';
    }

    public function estaConfirmada(): bool
    {
        return $this->est_ins === 'CONFIRMADA';
    }

    public function estaActiva(): bool
    {
        return $this->est_ins === 'ACTIVA';
    }

    public function estaObservada(): bool
    {
        return $this->est_ins === 'OBSERVADA';
    }

    public function estaAnulada(): bool
    {
        return $this->est_ins === 'ANULADA';
    }

    public function estaRetirada(): bool
    {
        return $this->est_ins === 'RETIRADA';
    }

    public function estaArchivada(): bool
    {
        return $this->est_ins === 'ARCHIVADA';
    }

    public function esNuevo(): bool
    {
        return $this->tip_ins === 'NUEVO';
    }

    public function esRegular(): bool
    {
        return $this->tip_ins === 'REGULAR';
    }

    public function esTraslado(): bool
    {
        return $this->tip_ins === 'TRASLADO';
    }

    public function esExterior(): bool
    {
        return $this->tip_ins === 'EXTERIOR';
    }

    public function esVulnerable(): bool
    {
        return $this->tip_ins === 'VULNERABLE';
    }

    public function esExcepcional(): bool
    {
        return $this->tip_ins === 'EXCEPCIONAL';
    }

    public function esPreinscripcion(): bool
    {
        return $this->tip_ins === 'PREINSCRIPCION';
    }

    public function esProvisional(): bool
    {
        return $this->con_ins === 'PROVISIONAL';
    }

    public function tieneSobrecupo(): bool
    {
        return (bool) $this->sob_aut_ins || $this->con_ins === 'SOBRECUPO';
    }

    public function estaReportadaEnSie(): bool
    {
        return (bool) $this->sie_ins;
    }

    public function documentosCompletos(): bool
    {
        return (bool) $this->doc_com_ins;
    }
}