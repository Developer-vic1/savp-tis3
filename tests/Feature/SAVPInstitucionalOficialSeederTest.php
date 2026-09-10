<?php

namespace Tests\Feature;

use App\Models\Administrador;
use App\Models\Asignatura;
use App\Models\ConfiguracionCalendarioGestion;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\EspecialidadTecnica;
use App\Models\Estudiante;
use App\Models\GestionAcademica;
use App\Models\Horario;
use App\Models\HorarioDetalle;
use App\Models\Paralelo;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\PlanAsignatura;
use App\Models\PlantillaHoraria;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\SAVPInstitucionalOficialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SAVPInstitucionalOficialSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que el seeder oficial cargue la estructura institucional real de forma limpia e idempotente.
     */
    public function test_seeder_institucional_oficial_carga_datos_correctos_e_idempotentes(): void
    {
        // 1. Primera ejecución
        $this->seed(SAVPInstitucionalOficialSeeder::class);

        // Conteo tras primera ejecución
        $conteoUsuarios1 = User::count();
        $conteoPersonas1 = Persona::count();
        $conteoPersonal1 = PersonalInstitucional::count();
        $conteoAdmin1 = Administrador::count();
        $conteoGestiones1 = GestionAcademica::count();
        $conteoCal1 = ConfiguracionCalendarioGestion::count();
        $conteoTurnos1 = Turno::count();
        $conteoPlantillas1 = PlantillaHoraria::count();
        $conteoCursos1 = Curso::count();
        $conteoParalelos1 = Paralelo::count();
        $conteoAsignaturas1 = Asignatura::count();
        $conteoEspecialidades1 = EspecialidadTecnica::count();

        // 2. Segunda ejecución (Idempotencia)
        $this->seed(SAVPInstitucionalOficialSeeder::class);

        $this->assertSame($conteoUsuarios1, User::count(), 'La segunda ejecución no debe duplicar usuarios.');
        $this->assertSame($conteoPersonas1, Persona::count(), 'La segunda ejecución no debe duplicar personas.');
        $this->assertSame($conteoPersonal1, PersonalInstitucional::count(), 'La segunda ejecución no debe duplicar personal.');
        $this->assertSame($conteoAdmin1, Administrador::count(), 'La segunda ejecución no debe duplicar administradores.');
        $this->assertSame($conteoGestiones1, GestionAcademica::count(), 'La segunda ejecución no debe duplicar gestiones.');
        $this->assertSame($conteoCal1, ConfiguracionCalendarioGestion::count(), 'La segunda ejecución no debe duplicar configuración de calendario.');
        $this->assertSame($conteoTurnos1, Turno::count(), 'La segunda ejecución no debe duplicar turnos.');
        $this->assertSame($conteoPlantillas1, PlantillaHoraria::count(), 'La segunda ejecución no debe duplicar plantillas horarias.');
        $this->assertSame($conteoCursos1, Curso::count(), 'La segunda ejecución no debe duplicar cursos.');
        $this->assertSame($conteoParalelos1, Paralelo::count(), 'La segunda ejecución no debe duplicar paralelos.');
        $this->assertSame($conteoAsignaturas1, Asignatura::count(), 'La segunda ejecución no debe duplicar asignaturas.');
        $this->assertSame($conteoEspecialidades1, EspecialidadTecnica::count(), 'La segunda ejecución no debe duplicar especialidades.');

        // 3. Administrador único oficial
        $adminUser = User::where('email', 'asturizagavictor@gmail.com')->first();
        $this->assertNotNull($adminUser, 'El administrador oficial debe existir.');
        $this->assertSame('USU_0001', $adminUser->cod_usu);
        $this->assertSame('ACTIVO', $adminUser->est_usu);
        $this->assertTrue($adminUser->hasRole('Administrador'), 'El usuario debe tener rol Administrador.');

        $personaAdmin = $adminUser->persona;
        $this->assertNotNull($personaAdmin, 'El administrador debe tener una Persona asociada.');
        $this->assertSame('PER_0001', $personaAdmin->cod_per);
        $this->assertTrue($personaAdmin->est_per);

        $personalAdmin = PersonalInstitucional::where('cod_per', $personaAdmin->cod_per)->first();
        $this->assertNotNull($personalAdmin, 'Debe existir registro en PersonalInstitucional.');
        $this->assertSame('PIN_0001', $personalAdmin->cod_pin);
        $this->assertSame('ACTIVO', $personalAdmin->est_pin);

        // 4. Gestión 2026 única
        $gestion = GestionAcademica::where('ani_gea', 2026)->first();
        $this->assertNotNull($gestion);
        $this->assertSame('GEA_0001', $gestion->cod_gea);
        $this->assertSame('ACTIVO', $gestion->est_gea);

        // 5. Configuración de calendario 66 / 68 / 66 = 200 días
        $this->assertSame(200, ConfiguracionCalendarioGestion::totalDiasGestion('GEA_0001'));
        $distribucion = ConfiguracionCalendarioGestion::distribucionPorTrimestre('GEA_0001');
        $this->assertSame([1 => 66, 2 => 68, 3 => 66], $distribucion);

        // 6. Turnos oficiales
        $this->assertSame(2, Turno::count());
        $turnoManana = Turno::where('cod_tur', 'TUR_0001')->first();
        $this->assertNotNull($turnoManana);
        $this->assertSame('07:45', substr($turnoManana->hor_ini_tur, 0, 5));
        $this->assertSame('13:20', substr($turnoManana->hor_fin_tur, 0, 5));

        $turnoTarde = Turno::where('cod_tur', 'TUR_0002')->first();
        $this->assertNotNull($turnoTarde);
        $this->assertSame('14:00', substr($turnoTarde->hor_ini_tur, 0, 5));
        $this->assertSame('18:00', substr($turnoTarde->hor_fin_tur, 0, 5));

        // 7. Plantillas horarias conocidas (3 plantillas)
        $this->assertSame(3, PlantillaHoraria::count());
        $this->assertDatabaseHas('plantilla_horaria', ['cod_pho' => 'PHO_0001', 'tip_pho' => 'REGULAR']);
        $this->assertDatabaseHas('plantilla_horaria', ['cod_pho' => 'PHO_0002', 'tip_pho' => 'INVIERNO']);
        $this->assertDatabaseHas('plantilla_horaria', ['cod_pho' => 'PHO_0003', 'tip_pho' => 'REGULAR']);

        // 8. 6 Cursos oficiales de Secundaria
        $this->assertSame(6, Curso::count());
        $cursosEsperados = [
            'CUR_0001' => '1ro de Secundaria',
            'CUR_0002' => '2do de Secundaria',
            'CUR_0003' => '3ro de Secundaria',
            'CUR_0004' => '4to de Secundaria',
            'CUR_0005' => '5to de Secundaria',
            'CUR_0006' => '6to de Secundaria',
        ];
        foreach ($cursosEsperados as $cod => $nom) {
            $this->assertDatabaseHas('curso', ['cod_cur' => $cod, 'nom_cur' => $nom, 'est_cur' => 'ACTIVO']);
        }

        // 9. Paralelos oficiales
        $this->assertSame(4, Paralelo::count());
        foreach (['PAR_0001' => 'A', 'PAR_0002' => 'B', 'PAR_0003' => 'C', 'PAR_0004' => 'D'] as $cod => $nom) {
            $this->assertDatabaseHas('paralelo', ['cod_par' => $cod, 'nom_par' => $nom, 'est_par' => 'ACTIVO']);
        }

        // 10. 14 Asignaturas oficiales
        $this->assertSame(14, Asignatura::count());
        $asignaturasEsperadas = [
            'ASI_0001' => ['nom' => 'Comunicación y Lenguaje', 'hor' => 4],
            'ASI_0002' => ['nom' => 'Matemática', 'hor' => 5],
            'ASI_0003' => ['nom' => 'Ciencias Sociales', 'hor' => 4],
            'ASI_0004' => ['nom' => 'Ciencias Biológicas', 'hor' => 3],
            'ASI_0005' => ['nom' => 'Física', 'hor' => 3],
            'ASI_0006' => ['nom' => 'Química', 'hor' => 3],
            'ASI_0007' => ['nom' => 'Educación Física', 'hor' => 2],
            'ASI_0008' => ['nom' => 'Lengua Extranjera - Inglés', 'hor' => 2],
            'ASI_0009' => ['nom' => 'Valores y Espiritualidades', 'hor' => 2],
            'ASI_0010' => ['nom' => 'Cosmovisiones y Filosofía', 'hor' => 2],
            'ASI_0011' => ['nom' => 'Psicología', 'hor' => 2],
            'ASI_0012' => ['nom' => 'Educación Musical', 'hor' => 2],
            'ASI_0013' => ['nom' => 'Artes Plásticas y Visuales', 'hor' => 2],
            'ASI_0014' => ['nom' => 'Técnica Tecnología General', 'hor' => 4],
        ];
        foreach ($asignaturasEsperadas as $cod => $info) {
            $this->assertDatabaseHas('asignatura', [
                'cod_asi' => $cod,
                'nom_asi' => $info['nom'],
                'hor_asi' => $info['hor'],
                'est_asi' => 'ACTIVO',
            ]);
        }

        // 11. 9 Especialidades técnicas oficiales
        $this->assertSame(9, EspecialidadTecnica::count());
        $especialidadesEsperadas = [
            'ESP_0001' => 'Técnica Tecnología General',
            'ESP_0002' => 'Técnica Tecnología Especializada',
            'ESP_0003' => 'Sistemas Informáticos',
            'ESP_0004' => 'Contabilidad',
            'ESP_0005' => 'Electrónica',
            'ESP_0006' => 'Mecánica Industrial',
            'ESP_0007' => 'Mecánica Automotriz',
            'ESP_0008' => 'Gastronomía',
            'ESP_0009' => 'Textiles y Confección',
        ];
        foreach ($especialidadesEsperadas as $cod => $nom) {
            $this->assertDatabaseHas('especialidad_tecnica', ['cod_esp' => $cod, 'nom_esp' => $nom, 'est_esp' => 'ACTIVO']);
        }

        // 12. Validar que NO se hayan creado docentes ficticios, estudiantes demo ni asignaciones
        $this->assertSame(0, Docente::count(), 'No deben existir docentes creados en la base oficial.');
        $this->assertSame(0, Estudiante::count(), 'No deben existir estudiantes creados en la base oficial.');
        $this->assertSame(0, PlanAsignatura::count(), 'No deben existir planes de asignatura artificiales.');
        $this->assertSame(0, Horario::count(), 'No deben existir horarios académicos.');
        $this->assertSame(0, HorarioDetalle::count(), 'No deben existir detalles de horarios.');
    }
}
