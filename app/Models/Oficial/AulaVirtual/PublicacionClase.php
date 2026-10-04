<?php

namespace App\Models\Oficial\AulaVirtual;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicacionClase extends Model
{
    protected $table = 'publicacion_clase';

    protected $primaryKey = 'cod_pub';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_pub', // Código único de la publicación
        'cod_cla', // Clase virtual a la que pertenece
        'cod_usu', // Usuario que creó la publicación

        'tip_pub', // Tipo de publicación: ANUNCIO, AVISO, MATERIAL, RECORDATORIO o GENERAL
        'tit_pub', // Título visible de la publicación
        'con_pub', // Contenido o descripción de la publicación
        'fec_pub', // Fecha y hora de publicación

        'est_pub', // Estado: BORRADOR, PUBLICADO, OCULTO o ANULADO
        'cod_sec',
    ];

    protected $casts = [
        'fec_pub' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PublicacionClase $publicacionClase) {
            if (! $publicacionClase->cod_pub) {
                $publicacionClase->cod_pub = 'PUB_'.strtoupper(bin2hex(random_bytes(8)));
            }

            if (! $publicacionClase->fec_pub && $publicacionClase->est_pub === 'PUBLICADO') {
                $publicacionClase->fec_pub = now();
            }
        });
    }

    public function claseVirtual(): BelongsTo
    {
        return $this->belongsTo(ClaseVirtual::class, 'cod_cla', 'cod_cla');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(MaterialClase::class, 'cod_pub', 'cod_pub');
    }

    public function scopePublicadas($query)
    {
        return $query->where('est_pub', 'PUBLICADO');
    }

    public function scopeBorradores($query)
    {
        return $query->where('est_pub', 'BORRADOR');
    }

    public function scopeOcultas($query)
    {
        return $query->where('est_pub', 'OCULTO');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('est_pub', 'ANULADO');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tip_pub', $tipo);
    }

    public function estaPublicada(): bool
    {
        return $this->est_pub === 'PUBLICADO';
    }

    public function esBorrador(): bool
    {
        return $this->est_pub === 'BORRADOR';
    }

    public function estaOculta(): bool
    {
        return $this->est_pub === 'OCULTO';
    }

    public function estaAnulada(): bool
    {
        return $this->est_pub === 'ANULADO';
    }

    public function publicar(): void
    {
        $this->forceFill([
            'est_pub' => 'PUBLICADO',
            'fec_pub' => $this->fec_pub ?? now(),
        ])->save();
    }

    public function ocultar(): void
    {
        $this->forceFill([
            'est_pub' => 'OCULTO',
        ])->save();
    }

    public function anular(): void
    {
        $this->forceFill([
            'est_pub' => 'ANULADO',
        ])->save();
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function registroActividadClaseRegistros(): HasMany
    {
        return $this->hasMany(RegistroActividadClase::class, 'pub_rac', 'cod_pub');
    }

    /** La FK compuesta en PostgreSQL garantiza también el contexto de clase. */
    public function seccionClase(): BelongsTo
    {
        return $this->belongsTo(SeccionClase::class, 'cod_sec', 'cod_sec');
    }
}
