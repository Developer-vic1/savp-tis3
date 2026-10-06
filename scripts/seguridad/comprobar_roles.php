<?php
// Diagnóstico de solo lectura; no modifica asignaciones ni registros institucionales.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
DB::beginTransaction();
try {
    if (DB::getDriverName()==='pgsql') DB::statement('SET TRANSACTION READ ONLY');
    $morph=(new App\Models\Oficial\Sistema\User)->getMorphClass();
    $roles=App\Models\Oficial\Sistema\Role::withCount('permissions')->get()->map(fn($r)=>[
        'rol'=>$r->name,'permisos'=>$r->permissions_count,
        'usuarios'=>DB::table('model_has_roles')->where('model_type',$morph)->where('role_id',$r->id)->distinct()->count('cod_usu'),
    ]);
    echo json_encode(['conexion'=>DB::getDriverName(),'base'=>DB::connection()->getDatabaseName(),'morph'=>$morph,'roles'=>$roles,
        'gestiones'=>DB::table('gestion_academica')->get(['ani_gea','est_gea','fii_gea','ffi_gea']),
        'permisos_catalogo'=>DB::table('permissions')->count(),
        'tablas'=>collect(['solicitud_rol','concesion_acceso','concesion_acceso_usuario','concesion_acceso_permiso','notificacion','notificacion_usuario'])->mapWithKeys(fn($t)=>[$t=>Schema::hasTable($t)]),
        'notificaciones_habilitadas'=>config('features.notifications')],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
} finally { DB::rollBack(); }
