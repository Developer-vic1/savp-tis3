<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Ruta conservada para la prueba auxiliar que requiere este archivo explícitamente. */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite' && Schema::hasTable('orientacion_actividades')) {
            (require __DIR__.'/Legado/2026_10_01_000001_extend_orientation_peter3_snapshots.php')->up();
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite' && Schema::hasTable('orientacion_actividades')) {
            (require __DIR__.'/Legado/2026_10_01_000001_extend_orientation_peter3_snapshots.php')->down();
        }
    }
};
