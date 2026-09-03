<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_current_profile_information_is_available(): void
    {
        $persona = Persona::create([
            'nom_per' => 'ANA',
            'ape_pat_per' => 'LOPEZ',
            'ci_per' => '99887766',
            'exp_per' => 'LP',
            'fec_nac_per' => '1992-04-10',
            'gen_per' => 'F',
            'est_per' => 1,
            'tel_per' => '77123456',
        ]);

        $user = User::factory()->create([
            'cod_per' => $persona->cod_per,
            'email' => 'ana.lopez@savp.edu.bo',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(UpdateProfileInformationForm::class);

        $this->assertEquals($user->email, $component->state['email']);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $persona = Persona::create([
            'nom_per' => 'ANA',
            'ape_pat_per' => 'LOPEZ',
            'ci_per' => '99887765',
            'exp_per' => 'LP',
            'fec_nac_per' => '1992-04-10',
            'gen_per' => 'F',
            'est_per' => 1,
            'tel_per' => '77123456',
        ]);

        $user = User::factory()->create([
            'cod_per' => $persona->cod_per,
            'email' => 'ana.update@savp.edu.bo',
        ]);

        $this->actingAs($user);

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', [
                'email' => 'ana.nuevo@savp.edu.bo',
                'tel_per' => '78999999',
            ])
            ->call('updateProfileInformation');

        $this->assertEquals('ana.nuevo@savp.edu.bo', $user->fresh()->email);
        $this->assertEquals('78999999', $persona->fresh()->tel_per);
    }
}
