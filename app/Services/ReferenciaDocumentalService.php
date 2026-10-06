<?php

namespace App\Services;

use App\Models\Oficial\Academico\ReferenciaDocumental;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReferenciaDocumentalService
{
    public function autorizar(): array
    {
        $autoridad=app(InstitutionalAuthorityService::class)->current();
        $usuario=auth()->user();
        abort_unless($usuario && ($usuario->hasRole('Administrador') || ($usuario->hasRole('Director') && $autoridad['status']==='ACTIVO' && $autoridad['director']->persona->usuario?->getKey()===$usuario->getKey())),403);
        return $autoridad;
    }

    public function guardar(string $tipo,string $ruta): ReferenciaDocumental
    {
        $autoridad=$this->autorizar();
        validator(compact('tipo'),['tipo'=>'required|in:FIRMA,SELLO'])->validate();
        if(!Schema::hasTable('referencia_documental') || !Schema::hasTable('bitacora')) throw ValidationException::withMessages(['imagen'=>'Falta el registro documental o la bitácora.']);
        if($tipo==='FIRMA' && $autoridad['status']!=='ACTIVO') throw ValidationException::withMessages(['imagen'=>'Se necesita un Director vigente único.']);
        $info=getimagesize($ruta);
        if(!$info || !in_array($info[2],[IMAGETYPE_PNG,IMAGETYPE_JPEG],true) || filesize($ruta)>4*1024*1024 || $info[0]*$info[1]>16000000) throw ValidationException::withMessages(['imagen'=>'Usa una imagen PNG o JPG de hasta 4 MB y 16 megapíxeles.']);
        $id=(string)Str::uuid(); $destino='documentacion/referencias/'.$id.($info[2]===IMAGETYPE_PNG?'.png':'.jpg');
        $comprobacion = $this->procesar(['validar', $ruta]);
        if (!($comprobacion['valida'] ?? false)) throw ValidationException::withMessages(['imagen'=>$comprobacion['error'] ?? 'La imagen está vacía o no es legible.']);
        Storage::disk('local')->put($destino,file_get_contents($ruta));
        if(!Storage::disk('local')->exists($destino)) throw ValidationException::withMessages(['imagen'=>'No pudimos conservar la imagen privada.']);
        try {
            return DB::transaction(function()use($autoridad,$tipo,$ruta,$id,$destino){
                $pin=$tipo==='FIRMA'?$autoridad['director']->cod_pin:null;
                // Serializa actualizaciones de referencia dentro de la institución.
                if($autoridad['director']) DB::table('personal_institucional')->where('cod_pin',$autoridad['director']->cod_pin)->lockForUpdate()->first();
                ReferenciaDocumental::where('tipo',$tipo)->where('cod_pin',$pin)->where('vigente',true)->update(['vigente'=>false]);
                $r=ReferenciaDocumental::create(['id'=>$id,'tipo'=>$tipo,'cod_pin'=>$pin,'titular'=>$tipo==='FIRMA'?$autoridad['name']:'Unidad Educativa Franz Tamayo N° 3','ruta'=>$destino,'sha256'=>hash_file('sha256',$ruta),'vigente'=>true,'cod_usu'=>auth()->id()]);
                BitacoraService::registrar(accion:'REGISTRAR_REFERENCIA_DOCUMENTAL',tabla:'referencia_documental',registro:$id,modulo:'Documentación institucional',nombreRegistro:$r->titular,descripcion:'Referencia privada de '.$tipo.' registrada para comparación documental.',nivel:'SUCCESS',valoresNuevos:$r->toArray());
                return $r;
            });
        }catch(\Throwable $e){Storage::disk('local')->delete($destino);throw $e;}
    }

    public function actuales(): array
    {
        if(!Schema::hasTable('referencia_documental')) return [];
        $a=app(InstitutionalAuthorityService::class)->current();
        return ['firma'=>$a['status']==='ACTIVO'?ReferenciaDocumental::where('tipo','FIRMA')->where('cod_pin',$a['director']->cod_pin)->where('vigente',true)->latest()->first():null,
            'sello'=>ReferenciaDocumental::where('tipo','SELLO')->where('vigente',true)->latest()->first()];
    }

    public function comparar(string $pdf): array
    {
        $referencias = $this->actuales();
        $rutas = [];
        foreach (['firma', 'sello'] as $tipo) {
            $r = $referencias[$tipo] ?? null;
            if (!$r || !Storage::disk('local')->exists($r->ruta)) return ['coinciden'=>false, 'error'=>'Falta registrar la firma vigente o el sello en Documentación institucional.'];
            $ruta = Storage::disk('local')->path($r->ruta);
            if (!hash_equals($r->sha256, hash_file('sha256', $ruta))) return ['coinciden'=>false, 'error'=>'La referencia conservada no coincide con su huella. Revisa el registro documental.'];
            $rutas[] = $ruta;
        }
        return $this->procesar(['comparar', $pdf, ...$rutas]) + ['referencias'=>collect($referencias)->map(fn($r)=>['id'=>$r->id,'sha256'=>$r->sha256])->all()];
    }

    private function procesar(array $argumentos): array
    {
        $python = config('calendario-estudio.python', 'python');
        if ($python === 'python' && is_file(base_path('ai-service/.venv/Scripts/python.exe'))) $python = base_path('ai-service/.venv/Scripts/python.exe');
        $proceso = new \Symfony\Component\Process\Process([$python, base_path('scripts/academico/comparar_referencias_documentales.py'), ...$argumentos], base_path());
        $proceso->setTimeout(20);
        try {
            $proceso->run();
            $resultado = json_decode($proceso->getOutput(), true);
            if ($proceso->isSuccessful() && is_array($resultado)) return $resultado;
        } catch (\Throwable $e) { report($e); }
        return ['coinciden'=>false, 'valida'=>false, 'error'=>'No pudimos comprobar las imágenes. Conserva los datos y vuelve a intentar con un documento legible.'];
    }

    public function avisarFirmaPendiente(): int
    {
        $a = app(InstitutionalAuthorityService::class)->current();
        if ($a['status'] !== 'ACTIVO' || !Schema::hasTable('referencia_documental') || ($this->actuales()['firma'] ?? null)) return 0;
        $u = $a['director']->persona->usuario;
        $n = app(NotificationService::class);
        if (!$u || !$n->available()) return 0;
        $n->publicar(['clave_evento'=>'firma-director:'.$a['director']->cod_pin, 'origen'=>'ADMINISTRATIVO','tipo'=>'ACCION',
            'titulo'=>'Registra tu firma de Dirección', 'mensaje'=>'Como Director vigente, ingresa a Documentación institucional y registra tu firma para revisar los documentos de la unidad educativa.'], [$u]);
        return 1;
    }
}
