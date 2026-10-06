<?php
namespace App\Console\Commands;

use App\Models\Oficial\Sistema\User;
use App\Services\RotacionRegenciaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class RotarRegencia extends Command
{
    protected $signature='regencia:rotar {--aplicar : Aplicar la regla anual sin sobrescribir designaciones}';
    protected $description='Aplica la rotación de regencia al iniciar una gestión, con prioridad para Dirección.';
    public function handle(RotacionRegenciaService $servicio): int
    {
        if(!$this->option('aplicar')){$this->info('Consulta sin escrituras. Usa --aplicar únicamente para ejecutar la regla anual autorizada.');return self::SUCCESS;}
        $actores=User::role('Administrador')->where('est_usu','ACTIVO')->get()->filter(fn($u)=>$u->can('regencia.asignaciones.gestionar'));
        if($actores->count()!==1){$this->error('No existe una autoridad administrativa única habilitada; la rotación no se aplica.');return self::FAILURE;}
        Auth::setUser($actores->first());
        $this->info('Asignaciones creadas: '.$servicio->aplicar($actores->first()));
        return self::SUCCESS;
    }
}
