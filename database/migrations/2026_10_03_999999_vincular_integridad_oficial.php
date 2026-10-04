<?php

declare(strict_types=1);

use Database\Migrations\Oficial\Soporte\InstalacionCanonica;
use Illuminate\Database\Migrations\Migration;

require_once __DIR__.'/Oficial/Soporte/InstalacionCanonica.php';

return new class extends Migration
{
    public function up(): void
    {
        InstalacionCanonica::relaciones();
    }

    public function down(): void
    {
        InstalacionCanonica::bloquearReversion();
    }
};
