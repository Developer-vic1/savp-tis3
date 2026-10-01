<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_cannot_create_an_unlinked_institutional_account(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->post('/register', [
            'name' => 'Test User', 'email' => 'test@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
