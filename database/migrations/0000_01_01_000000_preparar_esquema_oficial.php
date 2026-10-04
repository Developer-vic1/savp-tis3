<?php

declare(strict_types=1);

use Database\Migrations\Oficial\Soporte\InstalacionCanonica;
use Illuminate\Database\Migrations\Migration;

require_once __DIR__.'/Oficial/Soporte/InstalacionCanonica.php';

/** Preparación PostgreSQL; las migraciones originales del framework no cambian. */
return new class extends Migration
{
    public function up(): void
    {
        InstalacionCanonica::preparar();
    }

    public function down(): void
    {
        InstalacionCanonica::bloquearReversion();
    }
};
