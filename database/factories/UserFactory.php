<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $persona = \App\Models\Persona::create([
            'nom_per' => fake()->firstName(),
            'ape_pat_per' => fake()->lastName(),
            'ape_mat_per' => fake()->lastName(),
            'ci_per' => (string) fake()->unique()->numerify('########'),
            'exp_per' => 'LP',
            'fec_nac_per' => '2000-01-01',
            'gen_per' => 'MASCULINO',
            'tel_per' => fake()->numerify('7#######'),
            'ema_per' => fake()->unique()->safeEmail(),
            'dir_per' => 'Av. Principal 123',
            'est_per' => 'ACTIVO',
        ]);

        $ultimo = \App\Models\User::where('cod_usu', 'like', 'USU_%')
            ->orderByDesc('cod_usu')
            ->value('cod_usu');
        $numero = $ultimo ? ((int) str_replace('USU_', '', $ultimo)) + 1 : 1;
        $codUsu = 'USU_' . str_pad($numero, 4, '0', STR_PAD_LEFT);

        return [
            'cod_usu' => $codUsu,
            'cod_per' => $persona->cod_per,
            'email' => $persona->ema_per,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null,
            'est_usu' => 'ACTIVO',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user should have a personal team.
     */
    public function withPersonalTeam(?callable $callback = null): static
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(fn (array $attributes, User $user) => [
                    'name' => $user->name.'\'s Team',
                    'user_id' => $user->id,
                    'personal_team' => true,
                ])
                ->when(is_callable($callback), $callback),
            'ownedTeams'
        );
    }
}
