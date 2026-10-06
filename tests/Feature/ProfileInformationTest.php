<?php

namespace Tests\Feature;

use App\Models\Oficial\Sistema\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_profile_information_is_available(): void
    {
        $this->actingAs($user = User::factory()->create());

        $component = Livewire::test(UpdateProfileInformationForm::class);

        $this->assertEquals($user->cod_per, $component->state['cod_per']);
        $this->assertEquals($user->email, $component->state['email']);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', ['tel_per' => '76543210', 'dir_per' => 'Dirección de prueba', 'email' => 'test@example.com'])
            ->call('updateProfileInformation');

        $this->assertEquals('76543210', $user->fresh()->persona->tel_per);
        $this->assertEquals('Dirección de prueba', $user->fresh()->persona->dir_per);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }
}
