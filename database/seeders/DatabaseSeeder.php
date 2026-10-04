<?php

namespace Database\Seeders;

use Database\Seeders\Oficial\HistorialInstitucionalSeeder;
use Database\Seeders\Oficial\REALES\ADMINISTRADOR\AdministradorSistemaSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HistorialInstitucionalSeeder::class,
            AdministradorSistemaSeeder::class,
        ]);
        // El administrador se configura después del cierre del historial.
        // Reflejar sus cambios autorizados en el conteo final, sin datos secretos.
        $archivo = storage_path('app/private/integracion/RESULTADO_SEEDERS.json');
        $resultado = json_decode(file_get_contents($archivo), true, flags: JSON_THROW_ON_ERROR);
        $usuario = DB::table('users')->where('email', 'asturizagavictor@gmail.com')->sole();
        $resultado['administrador'] = ['correo' => $usuario->email, 'cod_per' => $usuario->cod_per, 'cod_usu' => $usuario->cod_usu, 'rol' => 'Administrador'];
        foreach (array_keys($resultado['por_tabla']) as $tabla) {
            $resultado['por_tabla'][$tabla] = DB::table($tabla)->count();
        }
        $resultado['base_ejecutada'] = DB::connection()->getDatabaseName();
        file_put_contents($archivo, json_encode($resultado, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
