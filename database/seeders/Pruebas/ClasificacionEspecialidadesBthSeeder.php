<?php

namespace Database\Seeders\Pruebas;

use App\Models\EspecialidadTecnica;
use App\Support\Academico\EspecialidadTecnicaInteligente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ClasificacionEspecialidadesBthSeeder extends Seeder
{
    public function run(): void
    {
        // Check if BTH fields have been migrated in the database
        if (!Schema::hasColumn('especialidad_tecnica', 'clas_bth_esp')) {
            $this->command?->warn('No se puede ejecutar la clasificación BTH: Las columnas extendidas no existen en la base de datos.');
            $this->command?->info('Por favor, ejecute primero las migraciones pendientes en producción.');
            return;
        }

        $especialidades = EspecialidadTecnica::all();

        if ($especialidades->isEmpty()) {
            $this->command?->warn('No se encontraron especialidades técnicas registradas.');
            return;
        }

        $soporte = app(EspecialidadTecnicaInteligente::class);
        $contador = 0;

        foreach ($especialidades as $registro) {
            $analisis = $soporte->analizar([
                'nom_esp' => $registro->nom_esp,
                'des_esp' => $registro->des_esp,
                'est_esp' => $registro->est_esp,
            ], $registro->cod_esp);

            $datosPersistibles = $soporte->mapearCamposPersistibles($registro->toArray(), $analisis);

            // Dynamically intersect key-value pairs matching active database columns
            $existingColumns = Schema::getColumnListing('especialidad_tecnica');
            $datosFiltrados = array_intersect_key($datosPersistibles, array_flip($existingColumns));

            $registro->update($datosFiltrados);
            $contador++;

            $this->command?->info("Especialidad '{$registro->nom_esp}' clasificada con estado: {$registro->est_int_esp} (Confianza: {$registro->conf_esp}%).");
        }

        $this->command?->info("Proceso completado. Se clasificaron {$contador} especialidades técnicas exitosamente.");
    }
}
