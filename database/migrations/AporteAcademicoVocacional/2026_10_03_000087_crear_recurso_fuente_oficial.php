<?php

declare(strict_types=1);

use Database\Migrations\Oficial\Soporte\InstalacionCanonica;
use Illuminate\Database\Migrations\Migration;

require_once dirname(__DIR__).'/Oficial/Soporte/InstalacionCanonica.php';

/** Entrada visible en artisan; definición organizada por dominio. */
return new class extends Migration
{
    public function up(): void
    {
        InstalacionCanonica::crear('recurso_fuente', 'AporteAcademicoVocacional');
    }

    public function down(): void
    {
        InstalacionCanonica::bloquearReversion();
    }
};
