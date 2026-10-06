<?php

namespace Tests\Unit;

use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Academico\PersonalInstitucional;
use App\Services\InstitutionalAuthorityService;
use PHPUnit\Framework\TestCase;

class InstitutionalAuthorityServiceTest extends TestCase
{
    private function director(string $id, string $directorStatus = 'ACTIVO', string $staffStatus = 'ACTIVO'): PersonalInstitucional
    {
        $person = new Persona(['nom_per' => 'Ana María', 'ape_pat_per' => 'Quispe', 'ape_mat_per' => 'Rojas']);
        $staff = new PersonalInstitucional(['est_pin' => $directorStatus === 'ACTIVO' ? $staffStatus : 'INACTIVO']);
        $staff->forceFill(['cod_pin' => $id]);
        $staff->setRelation('persona', $person);
        return $staff;
    }

    public function test_absent_inactive_and_ambiguous_director_are_blocked(): void
    {
        $service = new InstitutionalAuthorityService();
        $this->assertSame('SIN_DIRECTOR', $service->resolve(collect())['status']);
        $this->assertSame('SIN_DIRECTOR', $service->resolve(collect([$this->director('DIR_1', 'INACTIVO')]))['status']);
        $this->assertSame('SIN_DIRECTOR', $service->resolve(collect([$this->director('DIR_1', 'ACTIVO', 'INACTIVO')]))['status']);
        $this->assertSame('CONFIGURACION_INSTITUCIONAL_AMBIGUA', $service->resolve(collect([$this->director('DIR_1'), $this->director('DIR_2')]))['status']);
    }

    public function test_valid_director_uses_real_person_name(): void
    {
        $result = (new InstitutionalAuthorityService())->resolve(collect([$this->director('DIR_1')]));
        $this->assertSame('ACTIVO', $result['status']);
        $this->assertSame('Ana María Quispe Rojas', $result['name']);
    }
}
