<?php
/** QA de consultas y bloqueos. La transacción impide cualquier escritura institucional. */
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Support\Academico\ConsultaAsignaturasInstitucionales;
use App\Support\Academico\ConsultaCursosInstitucionales;
use App\Livewire\Admin\GestionAsignatura;
use App\Models\Oficial\Sistema\User;
DB::beginTransaction();
DB::statement('SET TRANSACTION READ ONLY');
try {
    $verificar=function(bool $condicion,string $nombre){if(!$condicion)throw new RuntimeException($nombre);};
    $gestion=DB::table('gestion_academica')->where('est_gea','ACTIVO')->value('cod_gea');
    DB::enableQueryLog(); $inicio=microtime(true);
    $consulta=app(ConsultaAsignaturasInstitucionales::class);
    $materias=$consulta->catalogo($gestion);
    $consultas=count(DB::getQueryLog());
    $ms=round((microtime(true)-$inicio)*1000);
    $verificar($consultas===4,'El catálogo debe usar cuatro consultas, independientemente de su cantidad.');
    $verificar($materias->count()===DB::table('asignatura')->count(),'Catálogo completo.');
    foreach($materias as $m){
        $notas=DB::table('calificacion as n')->join('plan_asignatura as p','p.cod_pas','=','n.cod_pas')->where('p.cod_asi',$m->cod_asi)->where('n.est_cal','<>','ANULADA')->count();
        $verificar($m->uso_academico['calificaciones']===$notas,'Conteo de notas por plan, no por una columna inexistente.');
        $verificar(count($consulta->docentes($m->cod_asi,$gestion))===$m->docentes_actuales,'Docentes de la gestión seleccionada.');
    }
    $cursos=app(ConsultaCursosInstitucionales::class);
    $curso=$cursos->resumen($gestion)->first(); $paralelo=$curso['paralelos'][0]['valor'];
    $periodos=$cursos->periodosHorario($curso['cod_cur'],$gestion,$paralelo);
    $elegido=collect($periodos)->firstWhere('aplicado',true)??$periodos[0];
    $todos=$cursos->horario($curso['cod_cur'],$gestion,$paralelo);
    $seleccionados=$cursos->horario($curso['cod_cur'],$gestion,$paralelo,$elegido['valor']);
    $verificar(collect($seleccionados)->pluck('plantilla_id')->unique()->all()===[$elegido['valor']],'Una sola cabecera en el horario seleccionado.');
    $verificar(count($seleccionados)===collect($todos)->where('plantilla_id',$elegido['valor'])->count(),'No se pierde ningún bloque del horario seleccionado.');
    $planificador=app(\App\Support\Academico\PlanificacionClaseInteligente::class);
    $bloque=DB::table('horario_bloque as b')->join('horario as h','h.cod_pho','=','b.cod_pho')->where('h.cod_hor',$elegido['valor'])->where('b.tip_hbl','CLASE')->where('b.est_hbl','ACTIVO')->whereNotExists(fn($q)=>$q->selectRaw('1')->from('horario_detalle as d')->whereColumn('d.cod_hbl','b.cod_hbl')->where('d.cod_hor',$elegido['valor'])->where('d.dia_hde','VIERNES'))->value('b.cod_hbl');
    $verificar($bloque!==null,'Debe existir un bloque libre para comprobar su contexto.');
    $verificar($planificador->contexto($elegido['valor'],$bloque,'VIERNES')['valido'],'La plantilla habilitada utiliza estado booleano del contrato oficial.');
    $verificar(!$planificador->contexto($elegido['valor'],$bloque,'DOMINGO')['valido'],'El día de clase se valida en el servidor.');
    $verificar(count($planificador->opciones($elegido['valor'])['materias'])>0,'Materias, planes y docentes consultables desde el grupo canónico.');
    $componente=new GestionAsignatura;
    try{$componente->abrirModalCrear();throw new RuntimeException('Falta autorización.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$verificar($e->getStatusCode()===403,'Sin actor no se permite registrar.');}
    auth()->setUser(User::role('Administrador')->firstOrFail());
    $componente->mount();
    $componente->abrirModalEditar($materias->first()->cod_asi);
    try{$componente->guardarEdicionAsignatura();throw new RuntimeException('Edición sin motivo permitida.');}catch(ValidationException $e){$verificar(isset($e->errors()['motivoEdicion']),'Edición bloqueada sin motivo.');}
    $componente->solicitarDesactivar($materias->first()->cod_asi);
    try{$componente->desactivarAsignatura($materias->first()->cod_asi);throw new RuntimeException('Retiro sin motivo permitido.');}catch(ValidationException $e){$verificar(isset($e->errors()['motivoCambio'])&&isset($e->errors()['confirmarCambio']),'Retiro requiere motivo y confirmación.');}
    $siglas=$materias->pluck('sig_asi')->map(fn($s)=>mb_strtoupper($s));
    $verificar(!collect($componente->catalogoPendiente)->contains(fn($s)=>$siglas->contains(mb_strtoupper($s['sigla']))),'El catálogo pendiente excluye materias registradas.');
    echo json_encode(['resultado'=>'CORRECTO','asignaturas'=>$materias->count(),'notas_historicas'=>$materias->sum(fn($m)=>$m->uso_academico['calificaciones']),'consultas_catalogo'=>$consultas,'ms_catalogo'=>$ms,'periodos_disponibles'=>count($periodos),'bloques_antes'=>count($todos),'bloques_seleccionados'=>count($seleccionados),'bloqueos'=>'actor, motivo y confirmación comprobados','escrituras'=>0],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
} finally { DB::rollBack(); }
