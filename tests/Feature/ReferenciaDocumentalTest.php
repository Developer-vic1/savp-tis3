<?php
namespace Tests\Feature;

use App\Models\Oficial\Academico\ReferenciaDocumental;
use App\Models\Oficial\Academico\PersonalInstitucional;
use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Sistema\User;
use App\Services\InstitutionalAuthorityService;
use App\Services\ReferenciaDocumentalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ReferenciaDocumentalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        (require base_path('database/migrations/Academico/2026_10_04_000021_create_referencias_documentales_table.php'))->up();
        Storage::fake('local');
    }

    private function autoridad(string $pin, string $usuario): void
    {
        $director = new PersonalInstitucional(['cod_pin'=>$pin]);
        $persona = new Persona;
        $persona->setRelation('usuario', (new User)->forceFill(['cod_usu'=>$usuario]));
        $director->setRelation('persona', $persona);
        $this->mock(InstitutionalAuthorityService::class)->shouldReceive('current')->andReturn(['status'=>'ACTIVO','name'=>'Director de prueba','director'=>$director]);
    }

    public function test_cambio_director_excluye_firma_anterior_y_conserva_sello(): void
    {
        foreach (['FIRMA'=>'DIRECTOR_ANTERIOR','SELLO'=>null] as $tipo=>$pin) ReferenciaDocumental::create(['id'=>(string)\Illuminate\Support\Str::uuid(),'tipo'=>$tipo,'cod_pin'=>$pin,'titular'=>'Referencia de prueba','ruta'=>'referencias/prueba.png','sha256'=>str_repeat('a',64),'vigente'=>true]);
        $this->autoridad('DIRECTOR_NUEVO','USUARIO_NUEVO');
        $r = app(ReferenciaDocumentalService::class)->actuales();
        $this->assertNull($r['firma']);
        $this->assertSame('SELLO',$r['sello']->tipo);
        $this->assertSame(2,ReferenciaDocumental::count());
        $this->assertFalse(app(ReferenciaDocumentalService::class)->comparar('no-existe.pdf')['coinciden']);
    }

    public function test_otro_director_no_puede_administrar_las_referencias_vigentes(): void
    {
        $this->autoridad('DIRECTOR_NUEVO','USUARIO_NUEVO');
        $usuario = Mockery::mock(User::class)->makePartial();
        $usuario->forceFill(['cod_usu'=>'USUARIO_ANTERIOR']);
        $usuario->shouldReceive('hasRole')->with('Administrador')->andReturn(false);
        $usuario->shouldReceive('hasRole')->with('Director')->andReturn(true);
        $this->actingAs($usuario);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(ReferenciaDocumentalService::class)->autorizar();
    }
}
