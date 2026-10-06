<?php

namespace Tests\Feature;

use App\Livewire\Admin\GestionParalelo;
use App\Models\Oficial\Sistema\User;
use App\Models\Oficial\Academico\Bitacora;
use App\Support\Academico\RespaldoCursoInstitucional;
use App\Support\Academico\ConsultaParalelosInstitucionales;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ProteccionExpedienteParaleloTest extends TestCase
{
    public function test_autollenado_solo_sobrescribe_un_pdf_coherente_y_rechazo_preserva_valores(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('bitacora', function ($t) { foreach ((new Bitacora)->getFillable() as $columna) $t->text($columna)->nullable(); });
        Storage::fake('local');
        $usuario = new User(['cod_usu'=>'prueba-aislada']);
        $usuario->setRelation('roles', collect());
        auth()->setUser($usuario);
        Gate::shouldReceive('authorize')->with('Paralelos')->andReturn(null);
        $texto = 'Dirección Departamental de Educación de La Paz. Resolución Administrativa 123/'.now()->year.'. Fecha: '.now('America/La_Paz')->toDateString().'. RESUELVE autorizar el paralelo E de la Unidad Educativa Franz Tamayo N°3 para la gestión '.now()->year.'. La decisión corresponde al expediente técnico institucional y a la disponibilidad de infraestructura.';
        $lector = Mockery::mock(RespaldoCursoInstitucional::class);
        $lector->shouldReceive('leer')->once()->andReturn(['texto'=>$texto, 'sha256'=>'aceptado']);
        $lector->shouldReceive('leer')->twice()->andReturn(['texto'=>str_replace('RESUELVE autorizar', 'RESUELVE no se autoriza', $texto), 'sha256'=>'rechazado']);
        $this->app->instance(RespaldoCursoInstitucional::class, $lector);
        $componente = new GestionParalelo;
        $componente->modalCrear = true;
        $componente->faseCrear = 2;
        $componente->form['nom_par'] = 'E';
        $componente->numeroDocumento = 'valor previo';
        $componente->documentoCambio = UploadedFile::fake()->create('respaldo.pdf', 1, 'application/pdf');
        $componente->completarDesdePdf();
        $this->assertTrue($componente->revisionDocumento['coherente']);
        $this->assertSame('123/'.now()->year, $componente->numeroDocumento);
        $this->assertSame(now('America/La_Paz')->toDateString(), $componente->fechaDocumento);
        $this->assertSame((string)now()->year, $componente->gestionSolicitud);
        $this->assertSame('Dirección Departamental de Educación de La Paz', $componente->autoridadDocumento);
        $this->assertSame('', $componente->motivoCambio);
        $this->assertFalse($componente->confirmarImpacto);
        $this->assertSame(0, DB::table('bitacora')->count());
        $componente->completarDesdePdf();
        $componente->completarDesdePdf();
        $this->assertFalse($componente->revisionDocumento['coherente']);
        $this->assertSame('123/'.now()->year, $componente->numeroDocumento);
        $this->assertSame(1, DB::table('bitacora')->count());
        $this->assertSame('INTENTO_CREAR_PARALELO_PDF_RECHAZADO', Bitacora::first()->acc_bit);
        $this->assertFalse(Schema::hasTable('paralelo'));
    }

    public function test_extraccion_ambigua_o_fecha_imposible_no_inventa_datos(): void
    {
        $detectar = \App\Support\Academico\ExpedienteParaleloInstitucional::detectar(...);
        $datos = $detectar('Resolución Administrativa 123/2026 y Resolución Administrativa 456/2026. Gestión 2026. Fecha: 31/02/2026.');
        $this->assertSame('', $datos['numero']);
        $this->assertSame('', $datos['fecha']);
        $this->assertSame('', $datos['autoridad']);
        $this->assertSame('2026-01-15', $detectar('Fecha: 15 de enero de 2026')['fecha']);
        $this->assertSame('', $detectar('Fecha: 15/01/2026. Fecha: 16/01/2026')['fecha']);
    }

    public function test_solo_el_proceso_completo_persiste_catalogo_y_expediente(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('bitacora', function ($t) { foreach ((new Bitacora)->getFillable() as $columna) $t->text($columna)->nullable(); });
        Schema::create('gestion_academica', fn ($t) => $t->string('cod_gea'));
        Schema::create('paralelo', function ($t) { foreach (['cod_par','nom_par','est_par'] as $c) $t->string($c); $t->timestamps(); });
        DB::table('gestion_academica')->insert(['cod_gea'=>'actual']);
        foreach (['A','B','C','D'] as $n=>$nombre) DB::table('paralelo')->insert(['cod_par'=>'PAR_'.str_pad($n+1,6,'0',STR_PAD_LEFT), 'nom_par'=>$nombre, 'est_par'=>'ACTIVO']);
        Storage::fake('local');
        $usuario = new User(['cod_usu'=>'prueba-aislada']);
        $usuario->setRelation('roles', collect());
        auth()->setUser($usuario);
        Gate::shouldReceive('authorize')->with('Paralelos')->andReturn(null);
        $consulta = Mockery::mock(ConsultaParalelosInstitucionales::class);
        $consulta->shouldReceive('consultar')->andReturn(['gestion'=>(object)['cod_gea'=>'actual','ani_gea'=>now()->year], 'usos'=>[], 'mapa'=>[]]);
        $this->app->instance(ConsultaParalelosInstitucionales::class, $consulta);
        $texto = 'Dirección Departamental de Educación. Resolución Administrativa 123/'.now()->year.'. '.now('America/La_Paz')->format('Y-m-d').'. RESUELVE autorizar el paralelo E de la Unidad Educativa Franz Tamayo N°3 para la gestión '.now()->year.'. La decisión corresponde al expediente técnico institucional y a la disponibilidad de infraestructura.';
        $lector = Mockery::mock(RespaldoCursoInstitucional::class);
        $lector->shouldReceive('leer')->andReturn(['texto'=>$texto, 'sha256'=>'prueba', 'paginas'=>1]);
        $this->app->instance(RespaldoCursoInstitucional::class, $lector);
        $componente = new GestionParalelo;
        $componente->modalCrear = true;
        $componente->form['nom_par'] = 'E';
        $componente->continuarCreacion();
        $this->assertSame(2, $componente->faseCrear);
        $this->assertSame(4, DB::table('paralelo')->count());
        $componente->causaCambio = 'infraestructura';
        $componente->motivoCambio = 'Preparación documentada de la organización con nuevos ambientes disponibles.';
        $componente->autoridadDocumento = 'Dirección Departamental de Educación';
        $componente->numeroDocumento = '123/'.now()->year;
        $componente->fechaDocumento = now('America/La_Paz')->toDateString();
        $componente->gestionSolicitud = (string)now()->year;
        $componente->documentoCambio = UploadedFile::fake()->create('respaldo.pdf', 1, 'application/pdf');
        $componente->continuarCreacion();
        $this->assertSame(3, $componente->faseCrear);
        $this->assertSame(0, DB::table('bitacora')->count());
        $this->assertSame(4, DB::table('paralelo')->count());
        $componente->referenciaVerificacion = 'Comprobación en el canal oficial con referencia de expediente 123.';
        $componente->confirmarImpacto = true;
        $componente->guardarParalelo();
        $this->assertSame(5, DB::table('paralelo')->count());
        $this->assertSame(1, DB::table('bitacora')->count());
        $registro = Bitacora::firstOrFail();
        $this->assertSame('CREAR_PARALELO', $registro->acc_bit);
        Storage::disk('local')->assertExists($registro->val_nue_bit['expediente']['archivo']);
        $this->assertSame($componente->motivoCambio, $registro->val_nue_bit['expediente']['motivo']);
        $this->assertFalse(Schema::hasTable('grupo_academico'));
    }

    public function test_rechazar_un_pdf_leido_registra_un_intento_y_no_crea_el_paralelo(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('bitacora', function ($t) { foreach ((new Bitacora)->getFillable() as $columna) $t->text($columna)->nullable(); });
        Storage::fake('local');
        $usuario = new User(['cod_usu'=>'prueba-aislada']);
        $usuario->setRelation('roles', collect());
        auth()->setUser($usuario);
        Gate::shouldReceive('authorize')->with('Paralelos')->andReturn(null);
        $lector = Mockery::mock(RespaldoCursoInstitucional::class);
        $lector->shouldReceive('leer')->andReturn(['texto'=>'Solicitud de incorporar otro paralelo, sin resolución administrativa ni aprobación. Documento incompleto que no identifica autoridad ni unidad educativa correctamente. Se envía para revisión.', 'sha256'=>'huella-de-prueba', 'paginas'=>1]);
        $this->app->instance(RespaldoCursoInstitucional::class, $lector);
        $componente = new GestionParalelo;
        $componente->modalCrear = true;
        $componente->form['nom_par'] = 'E';
        $componente->numeroDocumento = '123/2026';
        $componente->fechaDocumento = '2026-01-01';
        $componente->gestionSolicitud = '2026';
        $componente->documentoCambio = UploadedFile::fake()->create('respaldo.pdf', 1, 'application/pdf');
        $componente->revisarDocumento();
        $componente->revisarDocumento();
        $this->assertFalse($componente->revisionDocumento['coherente']);
        $this->assertSame(1, DB::table('bitacora')->count());
        $registro = Bitacora::firstOrFail();
        $this->assertSame('INTENTO_CREAR_PARALELO_PDF_RECHAZADO', $registro->acc_bit);
        $this->assertSame('BLOQUEADO', $registro->res_bit);
        Storage::disk('local')->assertExists($registro->val_nue_bit['documento']['archivo']);
        $this->assertFalse(Schema::hasTable('paralelo'));
    }

    public function test_invocar_guardar_directamente_exige_expediente(): void
    {
        auth()->setUser(new User(['cod_usu'=>'prueba-aislada']));
        Gate::shouldReceive('authorize')->with('Paralelos')->andReturn(null);
        $componente = new GestionParalelo;
        $componente->form['nom_par'] = 'E';
        try {
            $componente->guardarParalelo();
            $this->fail('No debe guardar sin expediente.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('motivoCambio', $e->errors());
            $this->assertArrayHasKey('documentoCambio', $e->errors());
            $this->assertArrayHasKey('referenciaVerificacion', $e->errors());
            $this->assertArrayHasKey('confirmarImpacto', $e->errors());
        }
    }

    public function test_un_invitado_no_puede_invocar_la_mutacion(): void
    {
        try {
            (new GestionParalelo)->guardarParalelo();
            $this->fail('Debe exigir autenticación.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }
}
