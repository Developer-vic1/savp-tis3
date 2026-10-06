<?php

namespace App\Models\Oficial\AulaVirtual;

use App\Support\Modelos\CodigoInstitucional;
use App\Models\Oficial\Sistema\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialClase extends Model
{
    use CodigoInstitucional;

    protected $table = 'material_clase';

    protected $primaryKey = 'cod_mat';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cod_mat',          // Codigo Material
        'cod_cla',          // Codigo Clase
        'cod_pub',          // Codigo Publicacion
        'cod_usu',          // Codigo Usuario
        'nom_mat',          // Nombre Material
        'tip_mat',          // Tipo Material
        'rut_mat',          // Ruta Material
        'url_mat',          // URL Material
        'mime_mat',         // MIME Material (Identifica técnicamente el tipo de archivo para validación, vista previa y descarga segura)
        'tam_mat',          // Tamaño Material
        'est_mat',          // Estado Material
        'cod_sec',
        'has_mat',
    ];

    protected $casts = [
        'tam_mat' => 'integer',
    ];

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

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function registroActividadClaseRegistros(): HasMany
    {
        return $this->hasMany(RegistroActividadClase::class, 'mat_rac', 'cod_mat');
    }

    /** La FK compuesta en PostgreSQL garantiza también el contexto de clase. */
    public function seccionClase(): BelongsTo
    {
        return $this->belongsTo(SeccionClase::class, 'cod_sec', 'cod_sec');
    }
}
