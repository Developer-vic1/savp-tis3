<?php
namespace Tests\Feature;

use App\Models\Oficial\Academico\Bitacora;
use App\Models\Oficial\Sistema\{Role,Permission,User,ConcesionAcceso,SolicitudRol};
use App\Services\{AccesoProgramadoService,RoleRequestService,InstitutionalAuthorityService,InstitutionalDocumentAnalyzer,NotificationService};
use App\Support\ConsultaRolesInstitucionales;
use Illuminate\Support\Facades\{DB,Schema,Storage};
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Mockery;
use Tests\TestCase;

class GobernanzaAccesosProgramadosTest extends TestCase
{
    private User $admin,$docente,$alumno;
    protected function setUp():void
    {
        parent::setUp();$this->assertSame('sqlite',DB::getDriverName());$this->assertSame(':memory:',DB::connection()->getDatabaseName());
        $this->travelTo(\Carbon\Carbon::parse('2026-10-04 16:00:00','UTC'));
        Schema::create('persona',function($t){$t->string('cod_per')->primary();$t->string('nom_per');$t->string('ape_pat_per')->nullable();$t->string('ape_mat_per')->nullable();});
        Schema::create('users',function($t){$t->string('cod_usu')->primary();$t->string('cod_per')->nullable();$t->string('email')->nullable();$t->string('est_usu');$t->timestamps();});
        Schema::create('roles',function($t){$t->id();$t->string('name');$t->string('guard_name');$t->timestamps();$t->unique(['name','guard_name']);});
        Schema::create('permissions',function($t){$t->id();$t->string('name');$t->string('guard_name');$t->timestamps();$t->unique(['name','guard_name']);});
        Schema::create('model_has_roles',function($t){$t->unsignedBigInteger('role_id');$t->string('model_type');$t->string('cod_usu');$t->primary(['role_id','model_type','cod_usu']);});
        Schema::create('model_has_permissions',function($t){$t->unsignedBigInteger('permission_id');$t->string('model_type');$t->string('cod_usu');$t->primary(['permission_id','model_type','cod_usu']);});
        Schema::create('role_has_permissions',function($t){$t->unsignedBigInteger('role_id');$t->unsignedBigInteger('permission_id');$t->primary(['role_id','permission_id']);});
        Schema::create('gestion_academica',function($t){$t->string('cod_gea')->primary();$t->integer('ani_gea');$t->date('fii_gea');$t->date('ffi_gea');$t->string('est_gea');$t->timestamps();});
        Schema::create('bitacora',function($t){foreach((new Bitacora)->getFillable() as $c)$t->text($c)->nullable();});
        DB::table('gestion_academica')->insert(['cod_gea'=>'G','ani_gea'=>2026,'fii_gea'=>'2026-02-02','ffi_gea'=>'2026-12-02','est_gea'=>'ACTIVO']);
        (require database_path('migrations/Sistema/2026_10_04_230000_crear_notificaciones_institucionales.php'))->up();
        (require database_path('migrations/Sistema/2026_10_05_000000_crear_concesiones_temporales.php'))->up();
        (require database_path('migrations/Sistema/2026_10_05_000100_crear_gobernanza_y_accesos_programados.php'))->up();
        config(['features.notifications'=>true]);Storage::fake('local');app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permisos=['roles-permisos.gestionar','roles.permisos.asignar','roles.crear','roles.solicitudes.crear','roles.solicitudes.analizar','roles.solicitudes.ver','roles.solicitudes.cancelar','reportes.exportar.institucional','reportes.ver.institucional','usuarios.ver.global'];
        foreach($permisos as $p)Permission::create(['name'=>$p,'guard_name'=>'web']);
        foreach(['Administrador','Director','Docente','Estudiante'] as $r)Role::create(['name'=>$r,'guard_name'=>'web']);
        Role::findByName('Administrador')->givePermissionTo($permisos);
        $this->admin=$this->usuario('A','Administrador');$this->docente=$this->usuario('D','Docente');$this->alumno=$this->usuario('E','Estudiante');auth()->setUser($this->admin);
    }
    private function usuario(string $id,string $rol):User{$u=User::create(['cod_usu'=>$id,'est_usu'=>'ACTIVO']);$u->assignRole($rol);return $u;}
    public function test_servicio_rechaza_fecha_inexistente_zona_alterada_y_un_dia_incompleto():void
    {
        $svc=app(AccesoProgramadoService::class);
        foreach(['2026-02-30T13:00','2026-10-04T13:00+02:00'] as $inicio){
            try{$svc->analizar($this->admin,$this->datos(['inicio'=>$inicio]));$this->fail('Solo admite fechas reales y hora local institucional.');}
            catch(ValidationException $e){$this->assertArrayHasKey('inicio',$e->errors());}
        }
        $a=$svc->analizar($this->admin,$this->datos(['vigencia'=>'DIA']));
        $this->assertFalse($a['valido']);$this->assertStringContainsString('exactamente 24 horas',implode(' ',$a['errores']));
        $this->assertSame(0,ConcesionAcceso::count());
    }
    public function test_revision_fallida_no_conserva_autorizacion_anterior():void
    {
        $componente=new \App\Livewire\Admin\RolesPermisos;
        $componente->usuariosAcceso=['D'];$componente->permisosAcceso=['reportes.exportar.institucional'];
        $componente->fechaInicioAcceso=$componente->fechaFinAcceso='2026-10-04';
        $componente->horaInicioAcceso='13';$componente->horaFinAcceso='15';
        $componente->tipoMotivoAcceso='OTRO';$componente->motivoAcceso='Apoyo autorizado para preparar informes de la gestión.';
        $componente->analizarAcceso();$this->assertTrue($componente->analisisAcceso['valido']);
        $componente->fechaFinAcceso='2026-02-30';
        try{$componente->analizarAcceso();$this->fail('La fecha no existe.');}
        catch(ValidationException $e){$this->assertArrayHasKey('fechaFinAcceso',$e->errors());}
        $this->assertNull($componente->analisisAcceso);$this->assertSame('',$componente->revisionAcceso);
    }
    public function test_rol_complementario_modificado_despues_de_revisar_no_concede_tareas_nuevas():void
    {
        $rol=Role::create(['name'=>'Apoyo institucional','guard_name'=>'web']);$rol->givePermissionTo('reportes.exportar.institucional');
        $componente=new \App\Livewire\Admin\RolesPermisos;$componente->modalAcceso=true;
        $componente->tipoAcceso='ROL';$componente->rolAcceso=$rol->id;$componente->usuariosAcceso=['D'];
        $componente->fechaInicioAcceso=$componente->fechaFinAcceso='2026-10-04';
        $componente->horaInicioAcceso='13';$componente->horaFinAcceso='15';
        $componente->tipoMotivoAcceso='OTRO';$componente->motivoAcceso='Apoyo autorizado para preparar informes de la gestión.';
        $componente->analizarAcceso();$this->assertTrue($componente->analisisAcceso['valido']);
        $rol->givePermissionTo('reportes.ver.institucional');
        try{$componente->guardarAcceso();$this->fail('No puede autorizar tareas que no se revisaron.');}
        catch(ValidationException $e){$this->assertStringContainsString('cambiaron después de la revisión',implode(' ',$e->errors()['acceso']));}
        $this->assertSame(0,ConcesionAcceso::count());$this->assertSame(0,DB::table('notificacion')->count());
    }
    public function test_destinatarios_malformados_se_rechazan_sin_error_de_servidor():void
    {
        try{app(AccesoProgramadoService::class)->crear($this->admin,$this->datos(['usuarios'=>'D']));$this->fail('Los destinatarios deben ser una lista.');}
        catch(ValidationException $e){$this->assertArrayHasKey('usuarios',$e->errors());}
        $this->assertSame(0,ConcesionAcceso::count());
    }
    public function test_aviso_de_exito_aparece_solo_si_permiso_y_notificaciones_se_guardaron():void
    {
        $preparar=function(string $permiso){
            $c=new \App\Livewire\Admin\RolesPermisos;$c->modalAcceso=true;$c->usuariosAcceso=['D'];$c->permisosAcceso=[$permiso];
            $c->fechaInicioAcceso=$c->fechaFinAcceso='2026-10-04';$c->horaInicioAcceso='13';$c->horaFinAcceso='15';
            $c->tipoMotivoAcceso='OTRO';$c->motivoAcceso='Apoyo autorizado para preparar informes de la gestión.';$c->analizarAcceso();return $c;
        };
        $c=$preparar('reportes.exportar.institucional');$c->guardarAcceso();
        $eventos=collect(\Livewire\store($c)->get('dispatched',[]))->map(fn($e)=>$e->serialize());
        $this->assertContains('roles-acceso-guardado',$eventos->pluck('name')->all());
        $this->assertContains('notificaciones-actualizadas',$eventos->pluck('name')->all());
        $this->assertFalse($c->modalAcceso);$this->assertSame(1,ConcesionAcceso::count());
        $this->assertSame(2,DB::table('notificacion_usuario')->count());
        $concesion=ConcesionAcceso::firstOrFail();
        $destinatario=$concesion->destinatarios()->firstOrFail();
        $tarea=$concesion->tareasAutorizadas()->firstOrFail();
        $this->assertTrue($destinatario->usuario->is($this->docente));
        $this->assertTrue($destinatario->concesion->is($concesion));
        $this->assertSame('reportes.exportar.institucional',$tarea->permiso->name);
        $this->assertTrue($tarea->concesion->is($concesion));
        $this->assertTrue(\App\Models\Oficial\Sistema\ConcesionAccesoUsuario::porClave($destinatario->getKey())->exists());
        $this->assertTrue(\App\Models\Oficial\Sistema\ConcesionAccesoPermiso::porClave($tarea->getKey())->exists());
        $fallo=$preparar('reportes.ver.institucional');
        $notificaciones=Mockery::mock(NotificationService::class);$notificaciones->shouldReceive('available')->andReturn(true);
        $notificaciones->shouldReceive('publicar')->once()->andReturn(null);$this->app->instance(NotificationService::class,$notificaciones);
        try{$fallo->guardarAcceso();$this->fail('El fallo de aviso debe revertir el permiso.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(409,$e->getStatusCode());}
        $this->assertSame([],\Livewire\store($fallo)->get('dispatched',[]));$this->assertTrue($fallo->modalAcceso);
        $this->assertSame(1,ConcesionAcceso::count());
    }
    public function test_un_dia_exige_24_horas_y_personalizado_permite_ajustar_el_plazo():void
    {
        $componente=new \App\Livewire\Admin\RolesPermisos;
        $componente->usuariosAcceso=['D'];$componente->permisosAcceso=['reportes.exportar.institucional'];
        $componente->fechaInicioAcceso='2026-10-04';$componente->fechaFinAcceso='2026-10-05';
        $componente->horaInicioAcceso=$componente->horaFinAcceso='13';
        $componente->tipoMotivoAcceso='OTRO';$componente->motivoAcceso='Apoyo autorizado para preparar informes de la gestión.';
        $componente->modoVigenciaAcceso='DIA';$componente->analizarAcceso();
        $this->assertTrue($componente->analisisAcceso['valido']);
        $this->assertStringContainsString('domingo 4 de octubre a las 13:00',$componente->analisisAcceso['inicio']);
        $componente->minutoFinAcceso='01';
        try{$componente->analizarAcceso();$this->fail('Un día no acepta cambios de duración.');}
        catch(ValidationException $e){$this->assertArrayHasKey('modoVigenciaAcceso',$e->errors());}
        $componente->modoVigenciaAcceso='PERSONALIZADO';$componente->analizarAcceso();
        $this->assertTrue($componente->analisisAcceso['valido']);
        $componente->modoVigenciaAcceso='INDEFINIDO';
        try{$componente->analizarAcceso();$this->fail('Una concesión temporal no se convierte en acceso permanente.');}
        catch(ValidationException $e){$this->assertArrayHasKey('modoVigenciaAcceso',$e->errors());}
        $this->assertSame(0,ConcesionAcceso::count());
    }
    public function test_confirmar_acceso_requiere_revision_y_bloquea_datos_modificados():void
    {
        $componente=new \App\Livewire\Admin\RolesPermisos;$componente->modalAcceso=true;
        try{$componente->guardarAcceso();$this->fail('No autoriza sin una revisión previa.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(422,$e->getStatusCode());}
        $componente->usuariosAcceso=['D'];$componente->permisosAcceso=['reportes.exportar.institucional'];
        $componente->fechaInicioAcceso=$componente->fechaFinAcceso='2026-10-04';
        $componente->horaInicioAcceso='13';$componente->horaFinAcceso='15';
        $componente->tipoMotivoAcceso='OTRO';$componente->motivoAcceso='Apoyo autorizado para preparar informes de la gestión.';
        $componente->analizarAcceso();$this->assertTrue($componente->analisisAcceso['valido']);
        $componente->permisosAcceso=['reportes.ver.institucional'];
        try{$componente->guardarAcceso();$this->fail('Cambiar tareas obliga a revisar de nuevo.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(422,$e->getStatusCode());}
        $this->assertSame(0,ConcesionAcceso::count());$this->assertSame(0,DB::table('notificacion')->count());
    }
    public function test_cambio_de_rol_avisa_a_cuentas_del_rol_y_operador_sin_avisar_a_otros():void
    {
        $rol=Role::findByName('Docente');
        app(\App\Services\RolePermissionService::class)->sync($rol,['reportes.exportar.institucional'],$this->admin,'','ADECUACION');
        $this->assertEqualsCanonicalizing(['A','D'],DB::table('notificacion_usuario')->pluck('cod_usu')->all());
        $this->assertTrue($this->docente->fresh()->can('reportes.exportar.institucional'));
        $this->assertFalse($this->alumno->fresh()->can('reportes.exportar.institucional'));
        $this->assertStringContainsString('1 permisos agregados',DB::table('notificacion')->value('mensaje'));
    }
    public function test_fallo_de_notificacion_revierte_permisos_del_rol_y_bitacora():void
    {
        $notificaciones=Mockery::mock(NotificationService::class);
        $notificaciones->shouldReceive('available')->andReturn(true);
        $notificaciones->shouldReceive('publicar')->once()->andReturn(null);
        $this->app->instance(NotificationService::class,$notificaciones);
        try{app(\App\Services\RolePermissionService::class)->sync(Role::findByName('Docente'),['reportes.exportar.institucional'],$this->admin,'','ADECUACION');$this->fail('El aviso forma parte de la transacción.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(409,$e->getStatusCode());}
        $this->assertFalse($this->docente->fresh()->can('reportes.exportar.institucional'));
        $this->assertSame(0,Bitacora::where('acc_bit','ACTUALIZAR_PERMISOS_ROL')->count());
    }
    public function test_edicion_de_rol_no_sobrescribe_una_asignacion_concurrente():void
    {
        $svc=app(\App\Services\RolePermissionService::class);$rol=Role::findByName('Docente');$huella=$svc->huella($rol);
        $rol->givePermissionTo('reportes.ver.institucional');
        try{$svc->sync($rol,['reportes.exportar.institucional'],$this->admin,'','ADECUACION',$huella);$this->fail('El borrador desactualizado debe rechazarse.');}
        catch(ValidationException $e){$this->assertArrayHasKey('permissions',$e->errors());}
        $this->assertSame(['reportes.ver.institucional'],$rol->fresh()->permissions->pluck('name')->all());
        $this->assertSame(0,DB::table('notificacion')->count());
    }
    public function test_delegacion_de_cursos_caduca_en_24_horas_sin_cambiar_roles_ni_pivotes():void
    {
        Permission::create(['name'=>'cursos.gestionar.global','guard_name'=>'web']);
        Role::findByName('Administrador')->givePermissionTo('cursos.gestionar.global');$this->admin=$this->admin->fresh();
        $svc=app(AccesoProgramadoService::class);
        $this->assertFalse(\App\Support\AccesoGestionCursos::permite($this->docente));
        $svc->crear($this->admin,$this->datos(['usuarios'=>['D'],'permisos'=>['cursos.gestionar.global'],'inicio'=>'2026-10-04T12:00','fin'=>'2026-10-05T12:00']));
        $this->docente=$this->docente->fresh();
        $this->assertTrue(\App\Support\AccesoGestionCursos::permite($this->docente));
        $this->assertFalse(\App\Support\AccesoGestionCursos::permite($this->alumno));
        $this->assertSame('Docente',app(\App\Services\RoleDashboardResolver::class)->roleFor($this->docente));
        $this->assertSame(0,DB::table('model_has_permissions')->count());
        $hook=new \App\Livewire\InstitutionalAuthorization;$hook->setComponent(new \App\Livewire\Admin\GestionCurso);
        auth()->setUser($this->docente);$hook->call('abrirCambioCurso',[],fn()=>null);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-05 16:00:00','UTC'));
        $this->assertFalse(\App\Support\AccesoGestionCursos::permite($this->docente));
        try{$hook->call('guardarCambioCurso',[],fn()=>null);$this->fail('Caducar bloquea la siguiente petición Livewire.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        $this->assertSame(1,Bitacora::where('acc_bit','PROGRAMAR_ACCESO')->count());
    }
    public function test_delegacion_de_cursos_rechaza_mas_de_un_dia_estudiantes_y_asignacion_permanente():void
    {
        Permission::create(['name'=>'cursos.gestionar.global','guard_name'=>'web']);
        Role::findByName('Administrador')->givePermissionTo('cursos.gestionar.global');$this->admin=$this->admin->fresh();
        $datos=$this->datos(['usuarios'=>['D'],'permisos'=>['cursos.gestionar.global'],'inicio'=>'2026-10-04T12:00','fin'=>'2026-10-05T12:01']);
        $this->assertFalse(app(AccesoProgramadoService::class)->analizar($this->admin,$datos)['valido']);
        $datos['fin']='2026-10-05T12:00';$datos['usuarios']=['E'];
        $this->assertFalse(app(AccesoProgramadoService::class)->analizar($this->admin,$datos)['valido']);
        $this->assertFalse(app(\App\Services\PermisosPersonalesService::class)->delegable('cursos.gestionar.global',$this->admin));
        $this->assertSame(0,DB::table('model_has_permissions')->count());
    }
    public function test_catalogo_conserva_actor_y_requisitos_del_destino_del_estudiante():void
    {
        $ventanas=app(\App\Support\CatalogoVentanasPermisos::class)->ventanas($this->admin);
        $materias=collect($ventanas)->first(fn($v)=>$v['actor']==='Estudiante'&&$v['label']==='Mis materias');
        $this->assertContains('Acceso_Aula_Virtual',$materias['requisitos']);
        $this->assertContains('Aula_Virtual_Estudiante',$materias['requisitos']);
        $this->assertFalse($materias['puede_ir']);
        $this->assertFalse(\App\Support\CatalogoVentanasPermisos::cumple($materias,'Estudiante',['Aula_Virtual_Estudiante']));
        $this->assertTrue(\App\Support\CatalogoVentanasPermisos::cumple($materias,'Estudiante',['Aula_Virtual_Estudiante','Acceso_Aula_Virtual']));
    }
    public function test_catalogo_reconoce_lectura_compatible_y_gate_de_gobernanza():void
    {
        $ventanas=app(\App\Support\CatalogoVentanasPermisos::class)->ventanas($this->admin);
        $roles=collect($ventanas)->first(fn($v)=>$v['route']==='admin.roles-permisos');
        $this->assertTrue(\App\Support\CatalogoVentanasPermisos::cumple($roles,'Administrador',['roles-permisos.gestionar']));
        $curso=collect($ventanas)->first(fn($v)=>$v['actor']==='Director'&&$v['label']==='Cursos');
        $this->assertTrue(\App\Support\CatalogoVentanasPermisos::cumple($curso,'Director',['Cursos']));
        $this->assertFalse(\App\Support\CatalogoVentanasPermisos::cumple($curso,'Docente',['Cursos','cursos.ver.institucional']));
    }
    public function test_permiso_administrativo_no_convierte_al_docente_en_administrador():void
    {
        $this->docente->givePermissionTo('usuarios.ver.global');
        $request=\Illuminate\Http\Request::create('/admin/gestion-usuarios');$request->setUserResolver(fn()=>$this->docente);
        try{(new \App\Http\Middleware\EnsureActorRole)->handle($request,fn()=>response('No debe entrar'),'Administrador');$this->fail('La ventana mantiene su actor.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        $this->assertSame('Docente',app(\App\Services\RoleDashboardResolver::class)->roleFor($this->docente));
    }
    public function test_detalle_distingue_editar_usuarios_de_entrar_al_modulo():void
    {
        $catalogo=app(\App\Support\CatalogoVentanasPermisos::class);
        $descripcion=$catalogo->describir(['name'=>'usuarios.crear'],$catalogo->ventanas($this->admin));
        $this->assertSame([],$descripcion['ventanas']);
        $this->assertSame('admin.gestion-usuarios',$descripcion['operaciones'][0]['route']);
        $this->assertContains('Gestion_Usuarios',$descripcion['operaciones'][0]['requisitos']);
        $this->assertStringContainsString('no concede',$descripcion['detalle']);
    }
    public function test_consulta_no_guarda_y_salir_descarta_el_borrador():void
    {
        $this->admin->givePermissionTo(Permission::create(['name'=>'roles-permisos.ver','guard_name'=>'web']));
        $componente=new \App\Livewire\Admin\RolesPermisos;
        $componente->selectedRoleId=Role::findByName('Docente')->id;
        try{$componente->save(app(\App\Services\RolePermissionService::class));$this->fail('Guardar exige Edición.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(422,$e->getStatusCode());}
        $this->assertSame(0,Role::findByName('Docente')->permissions()->count());
        $componente->habilitarEdicion();$this->assertTrue($componente->edicionPermisos);
        $componente->selectedPermissions=['reportes.exportar.institucional'];
        $componente->salirEdicion();$this->assertFalse($componente->edicionPermisos);$this->assertSame([],$componente->selectedPermissions);
        $this->assertSame(0,Role::findByName('Docente')->permissions()->count());
    }
    public function test_asignacion_personal_se_registra_y_avisa_privadamente_al_destinatario_y_autorizador():void
    {
        $svc=app(\App\Services\PermisosPersonalesService::class);
        $svc->guardar($this->admin,'D',['reportes.exportar.institucional'],$svc->huella($this->docente),'ADECUACION','');
        $this->assertTrue($this->docente->fresh()->can('reportes.exportar.institucional'));
        $this->assertFalse($this->alumno->fresh()->can('reportes.exportar.institucional'));
        $this->assertSame(1,DB::table('model_has_permissions')->count());
        $this->assertEqualsCanonicalizing(['D','A'],DB::table('notificacion_usuario')->pluck('cod_usu')->all());
        $this->assertSame(2,DB::table('notificacion')->count());
        $this->assertSame(1,DB::table('notificacion_usuario')->where('cod_usu','D')->count());
        $this->assertSame(1,DB::table('notificacion_usuario')->where('cod_usu','A')->count());
        $this->assertStringContainsString('Sin fecha de fin',DB::table('notificacion')->value('mensaje'));
        auth()->setUser($this->docente);
        $this->assertSame([],app(NotificationService::class)->snapshot($this->docente)['vigencias']);
        auth()->setUser($this->admin);
        $this->assertSame(1,Bitacora::where('acc_bit','ACTUALIZAR_PERMISOS_PERSONALES')->count());
        $this->assertSame(['Docente'],$this->docente->fresh()->getRoleNames()->all());
    }
    public function test_avisos_temporales_tienen_vigencia_privada_y_texto_breve():void
    {
        $c=app(AccesoProgramadoService::class)->crear($this->admin,$this->datos(['usuarios'=>['D']]));
        $destinatario=DB::table('notificacion_usuario')->where('cod_usu','D')->value('cod_nus');
        auth()->setUser($this->docente);
        $vista=app(NotificationService::class)->snapshot($this->docente);
        $this->assertArrayHasKey($destinatario,$vista['vigencias']);
        $this->assertSame('AUTORIZADA',$vista['vigencias'][$destinatario]['estado']);
        $this->assertSame($c->fin->toIso8601String(),$vista['vigencias'][$destinatario]['fin']);
        $this->assertLessThan(400,mb_strlen($vista['rows']->first()->notificacion->mensaje));
        auth()->setUser($this->admin);
        $operador=app(NotificationService::class)->snapshot($this->admin);
        $this->assertCount(1,$operador['vigencias']);
        $this->assertStringContainsString('Autorizaste',$operador['rows']->first()->notificacion->mensaje);
        app(AccesoProgramadoService::class)->revocar($this->admin,$c->getKey(),'Terminó la actividad institucional autorizada.');
        auth()->setUser($this->docente);
        $this->assertSame('REVOCADA',app(NotificationService::class)->snapshot($this->docente)['vigencias'][$destinatario]['estado']);
        auth()->setUser($this->alumno);
        $this->assertSame([],app(NotificationService::class)->snapshot($this->alumno)['vigencias']);
    }
    public function test_retirar_permiso_personal_conserva_el_heredado_y_no_se_autoconcede():void
    {
        Role::findByName('Docente')->givePermissionTo('reportes.ver.institucional');
        $this->docente->givePermissionTo('reportes.ver.institucional');$this->docente=$this->docente->fresh();
        $svc=app(\App\Services\PermisosPersonalesService::class);
        $svc->guardar($this->admin,'D',[],$svc->huella($this->docente),'MINIMO_ACCESO','');
        $this->assertTrue($this->docente->fresh()->can('reportes.ver.institucional'));
        $this->assertSame(0,$this->docente->fresh()->permissions()->count());
        try{$svc->guardar($this->admin,'A',[],$svc->huella($this->admin),'ADECUACION','');$this->fail('No se permite cambiar el acceso propio.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
    }
    public function test_personal_bloquea_catalogo_inventado_permiso_critico_y_borrador_desactualizado():void
    {
        $svc=app(\App\Services\PermisosPersonalesService::class);$huella=$svc->huella($this->docente);
        foreach([['inventado.ver.institucional'],['roles.crear'],['usuarios.ver.global']] as $nombres){
            try{$svc->guardar($this->admin,'D',$nombres,$huella,'ADECUACION','');$this->fail('No puede delegar esta tarea.');}
            catch(ValidationException $e){$this->assertArrayHasKey('permisosUsuario',$e->errors());}
        }
        $this->docente->givePermissionTo('reportes.ver.institucional');
        try{$svc->guardar($this->admin,'D',['reportes.exportar.institucional'],$huella,'ADECUACION','');$this->fail('Debe revisar el cambio concurrente.');}
        catch(ValidationException $e){$this->assertArrayHasKey('permisosUsuario',$e->errors());}
        $this->assertSame(['reportes.ver.institucional'],$this->docente->fresh()->permissions->pluck('name')->all());
        $this->assertSame(0,DB::table('notificacion_usuario')->count());
    }
    public function test_fallo_del_aviso_revierte_la_asignacion_personal_y_su_bitacora():void
    {
        $notificaciones=Mockery::mock(NotificationService::class);
        $notificaciones->shouldReceive('available')->andReturn(true);
        $notificaciones->shouldReceive('publicar')->once()->andThrow(new \RuntimeException('Fallo de prueba del aviso.'));
        $this->app->instance(NotificationService::class,$notificaciones);
        $svc=app(\App\Services\PermisosPersonalesService::class);
        try{$svc->guardar($this->admin,'D',['reportes.exportar.institucional'],$svc->huella($this->docente),'ADECUACION','');$this->fail('Debe revertir toda la operación.');}
        catch(\RuntimeException $e){$this->assertSame('Fallo de prueba del aviso.',$e->getMessage());}
        $this->assertSame(0,DB::table('model_has_permissions')->count());
        $this->assertSame(0,Bitacora::where('acc_bit','ACTUALIZAR_PERMISOS_PERSONALES')->count());
    }
    public function test_asignacion_personal_no_duplica_heredados_y_guardar_exige_edicion():void
    {
        Role::findByName('Docente')->givePermissionTo('reportes.ver.institucional');$this->docente=$this->docente->fresh();
        $svc=app(\App\Services\PermisosPersonalesService::class);
        try{$svc->guardar($this->admin,'D',['reportes.ver.institucional'],$svc->huella($this->docente),'ADECUACION','');$this->fail('No se duplica el permiso heredado.');}
        catch(ValidationException $e){$this->assertArrayHasKey('permisosUsuario',$e->errors());}
        $componente=new \App\Livewire\Admin\RolesPermisos;$componente->cuentaSeleccionada='D';$componente->permisosUsuario=['reportes.exportar.institucional'];
        try{$componente->guardarCuenta();$this->fail('Guardar exige edición habilitada.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(422,$e->getStatusCode());}
        $this->assertSame(0,DB::table('model_has_permissions')->count());
    }
    private function datos(array $cambios=[]):array{return array_replace(['tipo'=>'PERMISOS','role_id'=>null,'usuarios'=>['D','E'],'permisos'=>['reportes.exportar.institucional'],'inicio'=>'2026-10-04T13:00','fin'=>'2026-10-04T15:00','motivo_tipo'=>'OTRO','motivo'=>'Apoyo autorizado para preparar los informes de esta gestión.'],$cambios);}
    public function test_conteos_respetan_el_morph_historico_y_no_confunden_permisos_con_cuentas():void
    {
        $r=app(ConsultaRolesInstitucionales::class)->resumen();$d=$r['roles']->firstWhere('name','Docente');$this->assertSame(1,$d->users_count);$this->assertSame(0,$d->permissions_count);$this->assertSame(3,$r['cuentas']);
        $this->assertSame('App\\Models\\User',$this->docente->getMorphClass());
    }
    public function test_vigencia_exacta_hora_boliviana_y_avisos_idempotentes_sin_cron_para_autorizacion():void
    {
        $svc=app(AccesoProgramadoService::class);$c=$svc->crear($this->admin,$this->datos());$this->assertSame('17:00',$c->inicio->format('H:i'));
        $this->assertFalse($this->docente->can('reportes.exportar.institucional'));$this->assertSame(3,DB::table('notificacion_usuario')->count());
        $this->assertSame([],$svc->permisosVigentes($this->docente));
        $this->travelTo(\Carbon\Carbon::parse('2026-10-04 17:00:00','UTC'));$this->assertTrue($this->docente->can('reportes.exportar.institucional'));
        $this->assertSame(['reportes.exportar.institucional'],$svc->permisosVigentes($this->docente));
        $this->assertFalse($this->usuario('F','Docente')->can('reportes.exportar.institucional'));
        $this->assertSame(1,$svc->procesarAvisos());$this->assertSame(0,$svc->procesarAvisos());$this->assertSame(6,DB::table('notificacion_usuario')->count());
        $this->travelTo(\Carbon\Carbon::parse('2026-10-04 19:00:00','UTC'));$this->assertFalse($this->docente->can('reportes.exportar.institucional'));$this->assertSame(1,$svc->procesarAvisos());$this->assertSame(0,$svc->procesarAvisos());
        $this->assertSame(9,DB::table('notificacion_usuario')->count());$this->assertSame(['Docente'],$this->docente->getRoleNames()->all());
        $this->assertSame(0,DB::table('model_has_permissions')->count());
        $this->assertSame([],$svc->permisosVigentes($this->docente));
    }
    public function test_destinatarios_inactivos_no_bloquean_avisos_del_autorizador():void
    {
        $svc=app(AccesoProgramadoService::class);$c=$svc->crear($this->admin,$this->datos());
        User::whereIn('cod_usu',['D','E'])->update(['est_usu'=>'INACTIVO']);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-04 17:00:00','UTC'));
        $this->assertSame(1,$svc->procesarAvisos());$this->assertSame(0,$svc->procesarAvisos());
        $this->assertNotNull($c->fresh()->activada_en);
        $this->assertSame(4,DB::table('notificacion_usuario')->count());
        $this->assertFalse($this->docente->fresh()->can('reportes.exportar.institucional'));
    }
    public function test_revocacion_no_retira_el_permiso_permanente_y_no_modifica_otra_cuenta():void
    {
        $this->docente->givePermissionTo('reportes.exportar.institucional');$svc=app(AccesoProgramadoService::class);$c=$svc->crear($this->admin,$this->datos());
        $svc->revocar($this->admin,$c->getKey(),'La actividad concluyó y ya no requiere estos accesos.');
        $this->assertTrue($this->docente->can('reportes.exportar.institucional'));$this->assertFalse($this->alumno->can('reportes.exportar.institucional'));
        $this->assertSame('REVOCADA',$c->fresh()->estado);$this->assertSame(1,ConcesionAcceso::count());$this->assertSame(1,DB::table('model_has_permissions')->count());
    }
    public function test_pasado_fuera_de_gestion_permiso_critico_rol_principal_y_autoconcesion_se_bloquean():void
    {
        $svc=app(AccesoProgramadoService::class);
        foreach([
            ['inicio'=>'2026-10-03T13:00'],['fin'=>'2026-12-03T13:00'],['permisos'=>['usuarios.ver.global']],
            ['tipo'=>'ROL','role_id'=>Role::findByName('Director')->id],['usuarios'=>['A']],['usuarios'=>['no-existe']],
        ] as $datos){$a=$svc->analizar($this->admin,$this->datos($datos));$this->assertFalse($a['valido']);$this->assertNotEmpty($a['errores']);}
        $this->assertSame(0,ConcesionAcceso::count());
    }
    public function test_el_servidor_bloquea_motivo_alterado_y_genera_el_motivo_seleccionado():void
    {
        $svc=app(AccesoProgramadoService::class);
        foreach([['motivo_tipo'=>'VALOR_INVENTADO'],['motivo'=>'SJKANMKJSANDJKNAKJN SJKANMKJSANDJKNAKJN']] as $d){
            try{$svc->crear($this->admin,$this->datos($d));$this->fail('No debe aceptar el motivo alterado.');}catch(ValidationException $e){$this->assertArrayHasKey('motivo',$e->errors());}
        }
        $this->assertSame(0,ConcesionAcceso::count());
        $c=$svc->crear($this->admin,$this->datos(['motivo_tipo'=>'SUPLENCIA','motivo'=>'']));
        $this->assertSame(\App\Support\SupportRolesInstitucionales::MOTIVOS['acceso']['SUPLENCIA'],$c->motivo);
    }
    public function test_no_repite_concesiones_coincidentes_y_notificaciones_son_personales():void
    {
        $svc=app(AccesoProgramadoService::class);$svc->crear($this->admin,$this->datos());$this->assertFalse($svc->analizar($this->admin,$this->datos())['valido']);
        auth()->setUser($this->docente);$aviso=DB::table('notificacion_usuario')->where('cod_usu','E')->value('cod_nus');
        try{app(NotificationService::class)->mark($this->docente,$aviso,true);$this->fail('No debe leer un aviso ajeno.');}catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){$this->addToAssertionCount(1);}
        $this->assertNull(DB::table('notificacion_usuario')->where('cod_nus',$aviso)->value('leida_en'));
    }
    public function test_rol_complementario_temporal_congela_los_permisos_aprobados():void
    {
        $r=Role::create(['name'=>'Coordinación de informes','guard_name'=>'web']);$r->givePermissionTo('reportes.exportar.institucional');
        $c=app(AccesoProgramadoService::class)->crear($this->admin,$this->datos(['tipo'=>'ROL','role_id'=>$r->id,'permisos'=>[]]));$this->assertSame([$r->id],[$c->role_id]);
        $r->givePermissionTo('reportes.ver.institucional');$this->travelTo(\Carbon\Carbon::parse('2026-10-04 17:00:00','UTC'));
        $this->assertTrue($this->alumno->can('reportes.exportar.institucional'));$this->assertFalse($this->alumno->can('reportes.ver.institucional'));
    }
    private function carta(bool $coherente=true):array
    {
        $director=$this->usuario('DIR','Director');$autoridad=Mockery::mock(InstitutionalAuthorityService::class);$autoridad->shouldReceive('current')->andReturn(['status'=>'ACTIVO','name'=>'Director de prueba','director'=>(object)['persona'=>(object)['usuario'=>$director]]]);$this->app->instance(InstitutionalAuthorityService::class,$autoridad);
        $lector=Mockery::mock(InstitutionalDocumentAnalyzer::class);$lector->shouldReceive('leerAutorizacion')->andReturnUsing(fn($ruta)=>['coherente'=>$coherente,'errores'=>$coherente?[]:['La gestión de la carta no coincide.'],'sha256'=>hash_file('sha256',$ruta),'paginas'=>1]);$this->app->instance(InstitutionalDocumentAnalyzer::class,$lector);
        $archivo=UploadedFile::fake()->create('carta.pdf',2,'application/pdf');
        $datos=['motivo_tipo'=>'OTRO','responsabilidades'=>['REPORTES'],'requested_name'=>'Coordinación de informes','justification'=>'Preparar reportes e informes con criterios institucionales comunes.','institutional_reason'=>'Necesitamos apoyo para organizar los reportes de esta gestión.','functions'=>'Elaborar informes y exportar reportes autorizados de la institución.','scope'=>'Reportes institucionales','requested_permissions'=>['reportes.exportar.institucional']];
        return [$archivo,$datos];
    }
    public function test_carta_rechazada_conserva_evidencia_y_bitacora_sin_crear_rol():void
    {
        [$file,$datos]=$this->carta(false);$s=app(RoleRequestService::class)->submit($this->admin,$datos,$file);
        $this->assertSame('RECHAZADA',$s->estado);Storage::disk('local')->assertExists($s->documento_ruta);$this->assertSame(4,Role::count());
        $this->assertSame(1,DB::table('bitacora')->where('acc_bit','SOLICITUD_ROL_PDF_RECHAZADO')->count());
    }
    public function test_otro_administrador_revisa_la_firma_y_creacion_revalida_evidencia():void
    {
        [$file,$datos]=$this->carta();$svc=app(RoleRequestService::class);$s=$svc->submit($this->admin,$datos,$file);
        try{$svc->review($this->admin,$s->getKey(),true,'Verifiqué la carta con el canal institucional.',true,true,true,true);$this->fail('No se aprueba a sí mismo.');}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        $otro=$this->usuario('B','Administrador');auth()->setUser($otro);
        try{$svc->review($otro,$s->getKey(),true,'Verifiqué la carta con el canal institucional.',true,true,false,true);$this->fail('Falta firma.');}catch(ValidationException $e){$this->assertArrayHasKey('role_request',$e->errors());}
        $svc->review($otro,$s->getKey(),true,'Firma verificada con la Dirección de la institución.',true,true,true,true);
        $rol=$svc->createRole($otro,$s->getKey());$this->assertSame('CREADA',$s->fresh()->estado);$this->assertSame($rol->id,$s->fresh()->role_id);$this->assertSame(['reportes.exportar.institucional'],$rol->permissions->pluck('name')->all());
    }
}
