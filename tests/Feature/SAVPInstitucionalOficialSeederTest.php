<?php

namespace Tests\Feature;

use App\Models\Administrador;
use App\Models\Asignatura;
use App\Models\ConfiguracionCalendarioGestion;
use App\Models\Curso;
use App\Models\Director;
use App\Models\Docente;
use App\Models\EspecialidadTecnica;
use App\Models\Estudiante;
use App\Models\GestionAcademica;
use App\Models\Paralelo;
use App\Models\Persona;
use App\Models\PersonalInstitucional;
use App\Models\PlanAsignatura;
use App\Models\PlanEspecialidad;
use App\Models\PlantillaHoraria;
use App\Models\Regente;
use App\Models\SecretariaGeneral;
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
        $conteoDocentes1 = Docente::count();
        $conteoDirector1 = Director::count();
        $conteoSecretaria1 = SecretariaGeneral::count();
        $conteoAdmin1 = Administrador::count();
        $conteoGestiones1 = GestionAcademica::count();
        $conteoCal1 = ConfiguracionCalendarioGestion::count();
        $conteoTurnos1 = Turno::count();
        $conteoPlantillas1 = PlantillaHoraria::count();
        $conteoCursos1 = Curso::count();
        $conteoParalelos1 = Paralelo::count();
        $conteoAsignaturas1 = Asignatura::count();
        $conteoEspecialidades1 = EspecialidadTecnica::count();
        $conteoPlanesAsig1 = PlanAsignatura::count();
        $conteoPlanesEsp1 = PlanEspecialidad::count();

        // 2. Segunda ejecución (Idempotencia)
        $this->seed(SAVPInstitucionalOficialSeeder::class);

        $this->assertSame($conteoUsuarios1, User::count(), 'La segunda ejecución no debe duplicar usuarios.');
        $this->assertSame($conteoPersonas1, Persona::count(), 'La segunda ejecución no debe duplicar personas.');
        $this->assertSame($conteoPersonal1, PersonalInstitucional::count(), 'La segunda ejecución no debe duplicar personal.');
        $this->assertSame($conteoDocentes1, Docente::count(), 'La segunda ejecución no debe duplicar docentes.');
        $this->assertSame($conteoDirector1, Director::count(), 'La segunda ejecución no debe duplicar directores.');
        $this->assertSame($conteoSecretaria1, SecretariaGeneral::count(), 'La segunda ejecución no debe duplicar secretarias.');
        $this->assertSame($conteoAdmin1, Administrador::count(), 'La segunda ejecución no debe duplicar administradores.');
        $this->assertSame($conteoGestiones1, GestionAcademica::count(), 'La segunda ejecución no debe duplicar gestiones.');
        $this->assertSame($conteoCal1, ConfiguracionCalendarioGestion::count(), 'La segunda ejecución no debe duplicar configuración de calendario.');
        $this->assertSame($conteoTurnos1, Turno::count(), 'La segunda ejecución no debe duplicar turnos.');
        $this->assertSame($conteoPlantillas1, PlantillaHoraria::count(), 'La segunda ejecución no debe duplicar plantillas horarias.');
        $this->assertSame($conteoCursos1, Curso::count(), 'La segunda ejecución no debe duplicar cursos.');
        $this->assertSame($conteoParalelos1, Paralelo::count(), 'La segunda ejecución no debe duplicar paralelos.');
        $this->assertSame($conteoAsignaturas1, Asignatura::count(), 'La segunda ejecución no debe duplicar asignaturas.');
        $this->assertSame($conteoEspecialidades1, EspecialidadTecnica::count(), 'La segunda ejecución no debe duplicar especialidades.');
        $this->assertSame($conteoPlanesAsig1, PlanAsignatura::count(), 'La segunda ejecución no debe duplicar planes de asignatura.');
        $this->assertSame($conteoPlanesEsp1, PlanEspecialidad::count(), 'La segunda ejecución no debe duplicar planes de especialidad.');

        // 3. Administrador único oficial en users (exactamente 1 usuario)
        $this->assertSame(1, User::count(), 'Debe existir exactamente 1 usuario en la tabla users.');
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

        // 11. Especialidades técnicas oficiales
        $this->assertSame(11, EspecialidadTecnica::count());

        // 12. Validar personal institucional de la nómina
        $this->assertSame(56, Persona::count(), 'Deben existir 56 personas (1 admin + 55 personal de nómina).');
        $this->assertSame(56, PersonalInstitucional::count(), 'Deben existir 56 registros de personal institucional.');
        $this->assertSame(48, Docente::count(), 'Deben existir exactamente 48 docentes oficiales.');
        $this->assertSame(1, Director::count(), 'Debe existir exactamente 1 director oficial.');
        $this->assertSame(1, SecretariaGeneral::count(), 'Debe existir exactamente 1 secretaria oficial.');
        $this->assertSame(0, Regente::count(), 'No debe existir regente nuevo inventado.');
        $this->assertSame(0, Estudiante::count(), 'No deben existir estudiantes en la base oficial.');

        // 13. Anti-invención: Cero correos/CIs/fechas/género inventados para el personal de nómina
        $personalNomina = Persona::where('cod_per', '!=', 'PER_0001')->get();
        foreach ($personalNomina as $per) {
            $this->assertNull($per->ci_per, "Persona {$per->cod_per} ({$per->nom_per}) no debe tener CI inventado.");
            $this->assertNull($per->ema_per, "Persona {$per->cod_per} ({$per->nom_per}) no debe tener correo inventado.");
            $this->assertNull($per->fec_nac_per, "Persona {$per->cod_per} ({$per->nom_per}) no debe tener fecha de nacimiento inventada.");
            $this->assertNull($per->gen_per, "Persona {$per->cod_per} ({$per->nom_per}) no debe tener género inferido.");
            $this->assertNull($per->dir_per, "Persona {$per->cod_per} ({$per->nom_per}) no debe tener dirección inventada.");
            $this->assertNotNull($per->tel_per, "Persona {$per->cod_per} ({$per->nom_per}) debe conservar su teléfono celular real.");
        }

        // 14. Validación de casos directos obligatorios
        // Aida Garzofino Mamani (DOC_FT3_0027): MAT en 2B, 3B, 4B, 5B, 6B
        $docGarzofino = Docente::where('cod_doc', 'DOC_FT3_0027')->first();
        $this->assertNotNull($docGarzofino);
        $planesGarzofino = PlanAsignatura::where('cod_doc', $docGarzofino->cod_doc)->get();
        $this->assertCount(5, $planesGarzofino);

        // Lupe Vedia Rodriguez (DOC_FT3_0029): MAT en 1A, 2A, 3A, 4A, 5A, 6A
        $docVedia = Docente::where('cod_doc', 'DOC_FT3_0029')->first();
        $this->assertNotNull($docVedia);
        $planesVedia = PlanAsignatura::where('cod_doc', $docVedia->cod_doc)->get();
        $this->assertCount(6, $planesVedia);

        // Romer Gutierrez Troche (DOC_FT3_0030): MAT en 2C, 3C, 4C, 5C, 6C
        $docGutierrez = Docente::where('cod_doc', 'DOC_FT3_0030')->first();
        $this->assertNotNull($docGutierrez);
        $planesGutierrez = PlanAsignatura::where('cod_doc', $docGutierrez->cod_doc)->get();
        $this->assertCount(5, $planesGutierrez);

        // Reynaldo Vargas Gamboa (DOC_FT3_0037): Contabilidad en 5A-D y 6A-D (8 planes)
        $docVargas = Docente::where('cod_doc', 'DOC_FT3_0037')->first();
        $this->assertNotNull($docVargas);
        $planesVargas = PlanEspecialidad::where('cod_doc', $docVargas->cod_doc)->get();
        $this->assertCount(8, $planesVargas);

        // Guadalupe Morales Limachi (DOC_FT3_0038): Gastronomía en 5A-D y 6A-D (8 planes)
        $docMorales = Docente::where('cod_doc', 'DOC_FT3_0038')->first();
        $this->assertNotNull($docMorales);
        $planesMorales = PlanEspecialidad::where('cod_doc', $docMorales->cod_doc)->get();
        $this->assertCount(8, $planesMorales);

        // Edgar Rios Chuquimia (DOC_FT3_0040): Sistemas Informáticos en 5A-D y 6A-D (8 planes)
        $docRios = Docente::where('cod_doc', 'DOC_FT3_0040')->first();
        $this->assertNotNull($docRios);
        $planesRios = PlanEspecialidad::where('cod_doc', $docRios->cod_doc)->get();
        $this->assertCount(8, $planesRios);

        // Jhonny Callejas Blanco (DOC_FT3_0043): Electrónica en 5A-D y 6A-D (8 planes)
        $docCallejas = Docente::where('cod_doc', 'DOC_FT3_0043')->first();
        $this->assertNotNull($docCallejas);
        $planesCallejas = PlanEspecialidad::where('cod_doc', $docCallejas->cod_doc)->get();
        $this->assertCount(8, $planesCallejas);
    }
}
