<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // En PostgreSQL y MySQL se permite que ci_per sea nulo cuando no se cuenta con el CI oficial.
        Schema::table('persona', function (Blueprint $table) {
            $table->string('ci_per', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            $table->string('ci_per', 20)->nullable(false)->change();
        });
    }
};
