<?php
namespace App\Models\Oficial\Sistema;

use App\Support\Modelos\CodigoInstitucional;
use Illuminate\Database\Eloquent\Model;

class SolicitudRol extends Model
{
    use CodigoInstitucional;
    protected $table='solicitud_rol'; protected $primaryKey='cod_sor';
    public $incrementing=false; protected $keyType='string';
    protected $fillable=['cod_gea','cod_usu_solicitante','cod_usu_director','cod_usu_revisor','role_id','nombre','justificacion','motivo','funciones','alcance','observaciones','documento_ruta','documento_sha256','analisis','estado','nota_revision','revisada_en','creada_en'];
    protected $casts=['analisis'=>'array','revisada_en'=>'immutable_datetime','creada_en'=>'immutable_datetime'];
    public function permisos(){return $this->belongsToMany(Permission::class,'solicitud_rol_permiso','cod_sor','permission_id');}
    public function solicitante(){return $this->belongsTo(User::class,'cod_usu_solicitante','cod_usu');}
    public function revisor(){return $this->belongsTo(User::class,'cod_usu_revisor','cod_usu');}
    public function director(){return $this->belongsTo(User::class,'cod_usu_director','cod_usu');}
    public function gestion(){return $this->belongsTo(\App\Models\Oficial\Academico\GestionAcademica::class,'cod_gea','cod_gea');}
    public function rol(){return $this->belongsTo(Role::class,'role_id');}
}
