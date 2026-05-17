<?php

namespace App\Models\AulaVirtual;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialClase extends Model
{
    protected $table = 'material_clase';
    protected $primaryKey = 'cod_mat';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_mat',          //Codigo Material
        'cod_cla',          //Codigo Clase
        'cod_pub',          //Codigo Publicacion
        'cod_usu',          //Codigo Usuario
        'nom_mat',          //Nombre Material
        'tip_mat',          //Tipo Material
        'rut_mat',          //Ruta Material
        'url_mat',          //URL Material
        'mime_mat',         //MIME Material (Identifica técnicamente el tipo de archivo para validación, vista previa y descarga segura)
        'tam_mat',          //Tamaño Material
        'est_mat',          //Estado Material
    ];

    protected $casts = [
        'tam_mat' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (MaterialClase $materialClase) {
            if (! $materialClase->cod_mat) {
                $ultimoCodigo = self::where('cod_mat', 'like', 'MATC_%')
                    ->orderByDesc('cod_mat')
                    ->value('cod_mat');

                $numero = $ultimoCodigo
                    ? ((int) str_replace('MATC_', '', $ultimoCodigo)) + 1
                    : 1;

                $materialClase->cod_mat = 'MATC_' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function claseVirtual(): BelongsTo
    {
        return $this->belongsTo(ClaseVirtual::class, 'cod_cla', 'cod_cla');
    }

    public function publicacion(): BelongsTo
    {
        return $this->belongsTo(PublicacionClase::class, 'cod_pub', 'cod_pub');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function scopeActivos($query)
    {
        return $query->where('est_mat', 'ACTIVO');
    }

    public function scopeOcultos($query)
    {
        return $query->where('est_mat', 'OCULTO');
    }

    public function scopeAnulados($query)
    {
        return $query->where('est_mat', 'ANULADO');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tip_mat', $tipo);
    }

    public function estaActivo(): bool
    {
        return $this->est_mat === 'ACTIVO';
    }

    public function esEnlace(): bool
    {
        return $this->tip_mat === 'ENLACE';
    }

    public function esArchivo(): bool
    {
        return ! empty($this->rut_mat);
    }

    public function estaOculto(): bool
    {
        return $this->est_mat === 'OCULTO';
    }

    public function estaAnulado(): bool
    {
        return $this->est_mat === 'ANULADO';
    }
}
