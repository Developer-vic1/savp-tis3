<?php

namespace Database\Seeders\OFICIAL;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesYPermisosOficialSeeder extends Seeder
{
    /**
     * Reconstruye de forma determinística e idempotente los 6 roles oficiales,
     * 49 permisos institucionales y las 128 asignaciones de permisos a roles.
     */
    public function run(): void
    {
        // Limpiar caché de permisos Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $fuente = __DIR__.'/FUENTES/roles_permisos_data.local.php';
        if (! file_exists($fuente)) {
            throw new \RuntimeException("Fuente no encontrada: {$fuente}");
        }

        $data = require $fuente;

        // 1. Roles (6)
        foreach ($data['roles'] as $r) {
            Role::firstOrCreate(
                ['name' => $r['name'], 'guard_name' => $r['guard_name'] ?? 'web']
            );
        }

        // 2. Permisos (49)
        foreach ($data['permissions'] as $p) {
            Permission::firstOrCreate(
                ['name' => $p['name'], 'guard_name' => $p['guard_name'] ?? 'web']
            );
        }

        // 3. Sincronizar Permisos por Rol (128 relaciones)
        foreach ($data['role_has_permissions'] as $roleName => $permsList) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->syncPermissions($permsList);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
