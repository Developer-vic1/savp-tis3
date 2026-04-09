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
        Schema::create('users', function (Blueprint $table) {

            // 🔑 PK personalizada
            $table->string('cod_usu', 20)->primary();

            // 🔗 RELACIÓN CON PERSONA
            $table->string('cod_per', 20)->unique();
            $table->foreign('cod_per')
                ->references('cod_per')
                ->on('persona')
                ->cascadeOnDelete();

            // 🔐 LOGIN
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // 🔁 SESIÓN
            $table->rememberToken();

            // ⚙️ Jetstream (puedes mantenerlos)
            $table->unsignedBigInteger('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();

            $table->timestamps();
        });

        // 🔐 Reset de contraseña
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 🔁 Sesiones
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // Código sesión
            $table->string('user_id', 20)->nullable()->index(); // Usuario
            $table->string('ip_address', 45)->nullable(); // Dirección IP
            $table->text('user_agent')->nullable(); // Agente de usuario
            $table->longText('payload'); // Contenido sesión
            $table->integer('last_activity')->index(); // Última actividad
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
