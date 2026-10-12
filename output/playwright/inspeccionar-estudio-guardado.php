<?php
require dirname(__DIR__,2).'/vendor/autoload.php';
$app=require dirname(__DIR__,2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$estudio=App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad::where('analysis_snapshot->trace_id','42c1c470-1e6e-40c6-a401-a708b5896af3')->first();
if (!$estudio) throw new RuntimeException('No se encontró el estudio solicitado.');
$contexto=app(App\Services\AporteIngenieril\StudentOrientationService::class);
echo json_encode(['id'=>$estudio->id,'estado'=>$estudio->estado,'fecha_estudio'=>$estudio->analysis_completed_at,'valido'=>App\Services\AporteIngenieril\DTO\ContratoAporteIngenierilV2::respuestaValida('analysis_v2',$estudio->analysis_snapshot),'perfil_valido'=>App\Support\Orientacion\ExperienciaIntereses::perfilVigente($estudio->riasec_public,$estudio->riasec_score),'status'=>$estudio->analysis_snapshot['analysis_status'],'claves'=>array_keys($estudio->analysis_snapshot)]).PHP_EOL;
$ultimo=App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad::where('cod_est',$estudio->cod_est)->whereNotNull('riasec_public')->latest('id')->first();
echo json_encode(['id_ultimo'=>$ultimo->id,'estudio_ultimo'=>$ultimo->analysis_completed_at,'snapshot_igual'=>$ultimo->analysis_snapshot==$estudio->analysis_snapshot]).PHP_EOL;
$actor=App\Models\Oficial\Sistema\User::where('cod_per',$estudio->estudiante->cod_per)->firstOrFail();
$lectura=$contexto->context($actor);
echo json_encode(['id_contexto'=>$lectura['activity']?->id,'analysis_contexto'=>$lectura['activity']?->analysis_completed_at,'perfil_vista'=>App\Support\Orientacion\ExperienciaIntereses::presentar($lectura)['vigente']]).PHP_EOL;
echo json_encode(['fecha_ultimo'=>$ultimo->analysis_completed_at,'referencias'=>array_map(fn($c)=>count(data_get($c,'vocational_interest_relation.occupational_references',[])),$ultimo->analysis_snapshot['career_evidence_profiles']),'estudios_estudiante'=>App\Models\Oficial\AporteAcademicoVocacional\OrientacionActividad::where('cod_est',$estudio->cod_est)->count()]).PHP_EOL;
