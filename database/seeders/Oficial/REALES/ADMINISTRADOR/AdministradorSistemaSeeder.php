<?php

declare(strict_types=1);

namespace Database\Seeders\Oficial\REALES\ADMINISTRADOR;

use App\Models\Oficial\Academico\Persona;
use App\Models\Oficial\Sistema\User;
use Database\Seeders\RolSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Datos confirmados por el titular; reutiliza persona, cuenta y rol oficiales. */
final class AdministradorSistemaSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new \RuntimeException('El administrador oficial requiere PostgreSQL.');
        }
        $email = 'asturizagavictor@gmail.com';
        $password = 'victor123';
        DB::transaction(function () use ($email, $password): void {
            DB::select('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', ['SAVP:administrador:13381086']);
            $rol = Role::query()->where('name', 'Administrador')->where('guard_name', 'web')->first();
            if (! $rol) {
                // En una BD nueva, reutilizar la matriz vigente de módulos,
                // acciones y alcances antes de crear la primera cuenta.
                $this->call(RolSeeder::class);
                $rol = Role::query()->where('name', 'Administrador')->where('guard_name', 'web')->sole();
            }
            $fuente = require dirname(__DIR__).'/FUENTES/roles_permisos_data.local.php';
            foreach ($fuente['role_has_permissions']['Administrador'] as $nombrePermiso) {
                $rol->givePermissionTo(Permission::firstOrCreate(['name' => $nombrePermiso, 'guard_name' => 'web']));
            }
            $personas = Persona::query()->where('ci_per', '13381086')->orWhereRaw('lower(ema_per) = ?', [$email])->lockForUpdate()->get();
            if ($personas->count() > 1) {
                throw new \RuntimeException('CI y correo corresponden a personas distintas; no se fusionan automáticamente.');
            }
            $persona = $personas->first() ?? new Persona;
            $persona->fill([
                'nom_per' => 'VICTOR GROVER',
                'ape_pat_per' => 'ASTURIZAGA',
                'ape_mat_per' => 'PLATA',
                'ci_per' => '13381086',
                'exp_per' => 'LP',
                'fec_nac_per' => '2006-06-11',
                'ema_per' => $email,
                'tel_per' => '75836807',
                'dir_per' => 'CALLE TOCOPILLA 1480, ZONA BAJO TEJAR, LA PAZ',
                'est_per' => true,
            ]);
            $persona->save();
            $usuarios = User::query()->where('cod_per', $persona->getKey())->orWhereRaw('lower(email) = ?', [$email])->lockForUpdate()->get();
            if ($usuarios->count() > 1 || ($usuarios->first() && $usuarios->first()->cod_per !== $persona->getKey())) {
                throw new \RuntimeException('El correo del administrador pertenece a otra cuenta; no se reasigna.');
            }
            $usuario = $usuarios->first() ?? new User;
            $usuario->fill(['cod_per' => $persona->getKey(), 'email' => $email, 'est_usu' => 'ACTIVO', 'auth_provider' => 'local']);
            if (! $usuario->password || ! Hash::check($password, $usuario->password)) {
                $usuario->password = Hash::make($password);
            }
            if (! $usuario->email_verified_at) {
                $usuario->email_verified_at = now();
            }
            $usuario->save();
            $usuario->assignRole($rol);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command?->info('ADMINISTRADOR OFICIAL CONFIGURADO: asturizagavictor@gmail.com');
    }
}
