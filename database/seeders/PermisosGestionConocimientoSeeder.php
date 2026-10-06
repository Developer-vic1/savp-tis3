<?php

namespace Database\Seeders;

use App\Models\Oficial\Sistema\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermisosGestionConocimientoSeeder extends Seeder
{
    public const PERMISSIONS = [
        'conocimiento.ver' => ['Administrador', 'Director'],
        'conocimiento.proponer' => ['Administrador', 'Director'],
        'conocimiento.revisar' => ['Administrador'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        DB::transaction(function (): void {
            foreach (self::PERMISSIONS as $name => $roleNames) {
                $roles = array_map(fn ($roleName) => Role::firstOrCreate([
                    'name' => $roleName, 'guard_name' => 'web',
                ]), $roleNames);
                $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $permission->syncRoles($roles);
            }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
