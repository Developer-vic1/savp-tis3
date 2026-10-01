<?php

namespace Tests\Unit;

use App\Support\InstitutionalRoleGovernance;
use PHPUnit\Framework\TestCase;

class InstitutionalRoleGovernanceTest extends TestCase
{
    private InstitutionalRoleGovernance $engine;

    private array $roles = ['Administrador', 'Director', 'Secretaria', 'Regente', 'Docente', 'Estudiante'];

    private array $permissions = ['reportes.ver.institucional', 'usuarios.asignar_roles', 'estudiantes.ver.global', 'estudiantes.ver.institucional'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new InstitutionalRoleGovernance;
    }

    private function analyze(string $name, string $functions, array $permissions = ['reportes.ver.institucional'], string $reason = 'Se necesita distribuir un trabajo institucional concreto y verificable.'): array
    {
        return $this->engine->analyze($name, $reason, $functions, $permissions, $this->roles, $this->permissions);
    }

    public function test_reserved_and_existing_roles_are_rejected(): void
    {
        $this->assertSame('DUPLICA_ROL_EXISTENTE', $this->analyze('SuperAdmin', str_repeat('Analizar reportes institucionales. ', 2))['status']);
        $this->assertSame('DUPLICA_ROL_EXISTENTE', $this->analyze('Regente 2', str_repeat('Analizar reportes institucionales. ', 2))['status']);
        $this->assertSame('DUPLICA_ROL_EXISTENTE', $this->analyze('SECRETARIA', str_repeat('Analizar reportes institucionales. ', 2))['status']);
    }

    public function test_suggests_secretaria_and_regente_from_functions(): void
    {
        $secretary = $this->analyze('Oficinista', 'Registrar estudiantes, administrar inscripciones y actualizar información personal.');
        $regent = $this->analyze('Jefe de asistencia', 'Revisar asistencia, seguimiento de faltas y supervisión de estudiantes por grados.');
        $this->assertSame('Secretaria', $secretary['suggested_role']);
        $this->assertSame('Regente', $regent['suggested_role']);
    }

    public function test_blocks_critical_permission_and_recommends_smaller_scope(): void
    {
        $critical = $this->analyze('Encargado de Biblioteca', 'Organizar libros y controlar préstamos de materiales bibliográficos.', ['usuarios.asignar_roles']);
        $global = $this->analyze('Analista de Datos', 'Preparar reportes estadísticos para dirección institucional.', ['estudiantes.ver.global']);
        $this->assertSame('PERMISOS_INCOMPATIBLES', $critical['status']);
        $this->assertContains('usuarios.asignar_roles', $critical['blocked_permissions']);
        $this->assertNotEmpty($global['warnings']);
    }

    public function test_accepts_coherent_request_and_rejects_vague_justification(): void
    {
        $valid = $this->analyze('Analista de Datos', 'Preparar reportes estadísticos para dirección institucional.');
        $vague = $this->engine->analyze('Analista de Datos', 'porque se necesita', 'Preparar reportes estadísticos para dirección institucional.', ['reportes.ver.institucional'], $this->roles, $this->permissions);
        $this->assertSame('APTO', $valid['status']);
        $this->assertSame('RECHAZADO', $vague['status']);
    }

    public function test_unsupported_function_requires_review(): void
    {
        $result = $this->analyze('Responsable de Infraestructura', 'Elaborar reportes de inventario y mantenimiento de ambientes escolares.');
        $this->assertSame('REQUIERE_REVISION', $result['status']);
    }

    public function test_legacy_permission_and_unrelated_domain_are_blocked(): void
    {
        $available = [...$this->permissions, 'Gestion_Usuarios', 'calificaciones.ver.curso'];
        $result = $this->engine->analyze('Analista de Datos', 'Se necesita distribuir un trabajo institucional concreto y verificable.',
            'Preparar reportes estadísticos para dirección institucional.', ['Gestion_Usuarios', 'calificaciones.ver.curso'], $this->roles, $available);
        $this->assertSame('PERMISOS_INCOMPATIBLES', $result['status']);
        $this->assertCount(2, $result['blocked_permissions']);
    }

    public function test_secretaria_academica_is_a_cargo_and_not_an_additional_actor(): void
    {
        $result = $this->analyze('Secretaria Académica', 'Preparar reportes estadísticos para dirección institucional.');
        $this->assertSame('Secretaria', $result['suggested_role']);
        $this->assertSame('DUPLICA_ROL_EXISTENTE', $result['status']);
        $this->assertNotEmpty($result['reasons']);
    }
}
