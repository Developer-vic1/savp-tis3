<?php

namespace Database\Seeders\OFICIAL;

use Database\Seeders\AulaVirtualPermissionSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesYPermisosOficialSeeder extends Seeder
{
    /**
     * Registra y sincroniza roles y permisos indispensables de SAVP.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Ejecutar los catálogos y asignaciones de roles y permisos base
        $this->call([
            RolSeeder::class,
            AulaVirtualPermissionSeeder::class,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
