<?php
namespace App\Models\Oficial\Sistema;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};

class ConcesionAcceso extends Model
{
    use CodigoInstitucional, \App\Support\Modelos\FechasConZonaHoraria;
    protected $table='concesion_acceso'; protected $primaryKey='cod_cac';
    public $incrementing=false; protected $keyType='string';
    protected $fillable=['cod_gea','cod_usu_autorizador','role_id','tipo','motivo','inicio','fin','estado','activada_en','finalizada_en','cod_usu_revocador','motivo_revocacion','revocada_en'];
    protected $casts=['inicio'=>'immutable_datetime','fin'=>'immutable_datetime','activada_en'=>'immutable_datetime','finalizada_en'=>'immutable_datetime','revocada_en'=>'immutable_datetime'];
    public function usuarios(): BelongsToMany {return $this->belongsToMany(User::class,'concesion_acceso_usuario','cod_cac','cod_usu');}
    public function permisos(): BelongsToMany {return $this->belongsToMany(Permission::class,'concesion_acceso_permiso','cod_cac','permission_id');}
    public function rol(): BelongsTo {return $this->belongsTo(Role::class,'role_id');}
    public function destinatarios(): HasMany {return $this->hasMany(ConcesionAccesoUsuario::class,'cod_cac','cod_cac');}
    public function tareasAutorizadas(): HasMany {return $this->hasMany(ConcesionAccesoPermiso::class,'cod_cac','cod_cac');}
    public function estadoVisible(): string {return $this->estado==='REVOCADA'?'Revocada':($this->fin<=now()?'Finalizada':($this->inicio>now()?'Programada':'Vigente'));}
}
