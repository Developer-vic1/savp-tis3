<?php
// Solo lectura. Nunca imprime contraseñas, hashes ni credenciales de acceso.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\Oficial\Sistema\{Permission, User};
use Illuminate\Support\Facades\{DB, Hash};
DB::beginTransaction();
try {
    if (DB::getDriverName() === 'pgsql') DB::statement('SET TRANSACTION READ ONLY');
    $docentes = User::with(['persona', 'roles'])->whereHas('persona', fn ($q) =>
        $q->whereRaw('LOWER(ape_pat_per) = ?', ['mendoza'])->where(fn ($n) =>
            $n->whereRaw('LOWER(nom_per) LIKE ?', ['%felix%'])->orWhereRaw('LOWER(nom_per) LIKE ?', ['%feliz%'])))
        ->get()->map(fn ($u) => ['cuenta' => $u->getKey(), 'nombre' => $u->persona->nom_per,
            ... (in_array('--acceso', $argv, true) ? ['correo_acceso' => $u->email] : []),
            'rol' => $u->roles->pluck('name')->all(),
            'credencial_inicial_coincide' => Hash::check(mb_strtoupper(trim($u->persona->ape_pat_per)).'123', $u->password),
            'permisos' => $u->getAllPermissions()->pluck('name')->all()]);
    echo json_encode(['docentes' => $docentes, 'sesion_dominio' => config('session.domain'),
        'catalogo' => in_array('--acceso', $argv, true) ? Permission::count() : Permission::orderBy('name')->get(['id', 'name', 'guard_name'])], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} finally { DB::rollBack(); }
