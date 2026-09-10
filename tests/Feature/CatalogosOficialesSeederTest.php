<?php

namespace Tests\Feature;

use App\Models\Director;
use App\Models\Docente;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\PlanAsignatura;
use App\Models\PlanEspecialidad;
use App\Models\Regente;
use App\Models\SecretariaGeneral;
use App\Models\User;
use Database\Seeders\SAVPInstitucionalOficialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogosOficialesSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SAVPInstitucionalOficialSeeder::class);
    }

    public function test_seeders_oficiales_son_idempotentes_y_cargan_catalogos_reales(): void
    {
        // Re-seeding debe ser totalmente idempotente
        $this->seed(SAVPInstitucionalOficialSeeder::class);

        // Periodos de Evaluación 2026
        $this->assertDatabaseCount('periodo_evaluacion', 3);
        $this->assertDatabaseHas('periodo_evaluacion', [
            'cod_pev' => 'PEV_0001',
            'nom_pev' => 'Primer Trimestre',
            'ord_pev' => 1,
            'cod_gea' => 'GEA_0001',
            'fii_pev' => '2026-02-02',
            'ffi_pev' => '2026-05-15',
            'est_pev' => 'ACTIVO',
        ]);
        $this->assertDatabaseHas('periodo_evaluacion', [
            'cod_pev' => 'PEV_0002',
            'nom_pev' => 'Segundo Trimestre',
            'ord_pev' => 2,
            'cod_gea' => 'GEA_0001',
            'fii_pev' => '2026-05-18',
            'ffi_pev' => '2026-08-28',
            'est_pev' => 'ACTIVO',
        ]);
        $this->assertDatabaseHas('periodo_evaluacion', [
            'cod_pev' => 'PEV_0003',
            'nom_pev' => 'Tercer Trimestre',
            'ord_pev' => 3,
            'cod_gea' => 'GEA_0001',
            'fii_pev' => '2026-09-01',
            'ffi_pev' => '2026-12-04',
            'est_pev' => 'ACTIVO',
        ]);

        // Estados de Asistencia
        $this->assertDatabaseCount('estado_asistencia', 5);
        $this->assertDatabaseHas('estado_asistencia', [
            'cod_est_asi' => 'EASI_0001',
            'nom_est_asi' => 'Presente',
            'abr_est_asi' => 'P',
        ]);
        $this->assertDatabaseHas('estado_asistencia', [
            'cod_est_asi' => 'EASI_0002',
            'nom_est_asi' => 'Tardanza',
            'abr_est_asi' => 'T',
        ]);
        $this->assertDatabaseHas('estado_asistencia', [
            'cod_est_asi' => 'EASI_0003',
            'nom_est_asi' => 'Falta',
            'abr_est_asi' => 'F',
        ]);
        $this->assertDatabaseHas('estado_asistencia', [
            'cod_est_asi' => 'EASI_0004',
            'nom_est_asi' => 'Justificado',
            'abr_est_asi' => 'J',
        ]);
        $this->assertDatabaseHas('estado_asistencia', [
            'cod_est_asi' => 'EASI_0005',
            'nom_est_asi' => 'Licencia',
            'abr_est_asi' => 'L',
        ]);

        // Tipos de Vinculación
        $this->assertDatabaseCount('tipo_vinculacion_estudiante', 3);
        $this->assertDatabaseHas('tipo_vinculacion_estudiante', [
            'cod_tve' => 'TVE_0001',
            'nom_tve' => 'Regular',
        ]);
        $this->assertDatabaseHas('tipo_vinculacion_estudiante', [
            'cod_tve' => 'TVE_0002',
            'nom_tve' => 'Traslado',
        ]);
        $this->assertDatabaseHas('tipo_vinculacion_estudiante', [
            'cod_tve' => 'TVE_0003',
            'nom_tve' => 'Reincorporado',
        ]);

        // Personal Institucional Oficial 2026
        $this->assertSame(1, User::count());
        $this->assertSame(56, Persona::count());
        $this->assertSame(56, PersonalInstitucional::count());
        $this->assertSame(48, Docente::count());
        $this->assertSame(1, Director::count());
        $this->assertSame(1, SecretariaGeneral::count());
        $this->assertSame(0, Regente::count());

        // Planes reales
        $this->assertSame(290, PlanAsignatura::count());
        $this->assertSame(68, PlanEspecialidad::count());
    }

    public function test_anti_invencion_garantiza_cero_datos_ficticios_en_personal(): void
    {
        // 1. Usuarios: solo USU_0001 (Administrador)
        $this->assertSame(1, User::count());
        $user = User::first();
        $this->assertSame('USU_0001', $user->cod_usu);
        $this->assertSame('asturizagavictor@gmail.com', $user->email);

        // 2. Ningún docente tiene correo, CI ni fecha generada
        $docentesPersonas = Persona::where('cod_per', '!=', 'PER_0001')->get();
        foreach ($docentesPersonas as $persona) {
            $this->assertNull($persona->ema_per, "No debe existir correo para {$persona->cod_per}");
            $this->assertNull($persona->ci_per, "No debe existir CI para {$persona->cod_per}");
            $this->assertNull($persona->fec_nac_per, "No debe existir fecha de nacimiento para {$persona->cod_per}");
            $this->assertNull($persona->gen_per, "No debe existir género para {$persona->cod_per}");
            $this->assertNull($persona->dir_per, "No debe existir dirección para {$persona->cod_per}");
        }

        // 3. No existen nombres falsos del seeder anterior
        $nombresFalsos = [
            'Marcos Antonio', 'Roxana Patricia', 'Silvia Gabriela', 'Edwin Marcelo',
            'Monica Lizeth', 'Pedro Javier', 'Claudia Paola', 'Fabiola Mariela',
            'Mauricio Javier', 'Patricia Alejandra', 'Oscar Daniel', 'Mariela Ruth',
            'Carolina Vanessa', 'Alvaro Sebastian', 'Natalia Sofia', 'Katherine Lucia',
            'Gustavo Adolfo', 'Eliana Gabriela', 'Samuel Rodrigo', 'Dayana Noelia',
            'Ramiro Esteban', 'Lourdes Beatriz', 'Joel Andres', 'Pamela Gabriela',
        ];
        foreach ($nombresFalsos as $nombreFalso) {
            $this->assertDatabaseMissing('persona', [
                'nom_per' => $nombreFalso,
                'cod_per' => 'PER_DOC_%',
            ]);
        }
    }
}
