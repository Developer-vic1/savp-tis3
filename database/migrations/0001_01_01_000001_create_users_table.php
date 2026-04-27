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

            // ✅ ESTADO DE LA CUENTA
            $table->enum('est_usu', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');

            // 🔁 SESIÓN
            $table->rememberToken();

            // ⚙️ Jetstream
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
            $table->string('id')->primary();
            $table->string('user_id', 20)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 📝 Historial de cambios de estado
        Schema::create('user_status_logs', function (Blueprint $table) {
            $table->id();
            $table->string('cod_usu', 20);
            $table->enum('est_usu', ['ACTIVO', 'INACTIVO']);
            $table->string('motivo')->nullable();
            $table->string('cod_usu_admin', 20)->nullable();
            $table->timestamps();

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('cod_usu_admin')
                ->references('cod_usu')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_status_logs');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
