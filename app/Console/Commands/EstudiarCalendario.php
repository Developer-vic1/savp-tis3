<?php

namespace App\Console\Commands;

use App\Services\Academico\EstudioCalendarioMinisterial;
use Illuminate\Console\Command;

class EstudiarCalendario extends Command
{
    protected $signature = 'academica:estudiar-calendario {--seguir : Mantener el trabajador de pendientes activo}';
    protected $description = 'Revisa pendientes del calendario oficial sin modificar datos institucionales.';

    public function handle(EstudioCalendarioMinisterial $estudio): int
    {
        $archivo = fopen($estudio->directorio().'/trabajador.lock', 'c+');
        if (! $archivo || ! flock($archivo, LOCK_EX | LOCK_NB)) {
            return self::SUCCESS;
        }
        try {
            do {
                $estudio->procesarPendientes();
                if ($this->option('seguir')) {
                    sleep(15);
                }
            } while ($this->option('seguir'));
        } finally {
            flock($archivo, LOCK_UN);
            fclose($archivo);
        }
        return self::SUCCESS;
    }
}
