<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** MIG-003. Laravel Notifiable con PK institucional de texto, nunca bigint morphs. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->string('notifiable_id', 20);
            $table->string('event_key', 64)->nullable();
            $table->jsonb('data');
            $table->timestampTz('read_at')->nullable();
            $table->timestampsTz();
            $table->foreign('notifiable_id')->references('cod_usu')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->index(['notifiable_type', 'notifiable_id', 'read_at', 'created_at'], 'notifications_recipient_unread_idx');
            $table->unique(['notifiable_id', 'event_key'], 'notifications_recipient_event_unique');
        });
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notification_payload_check CHECK (jsonb_typeof(data) = 'object')");
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications') && DB::table('notifications')->exists()) {
            throw new RuntimeException('Rollback cerrado: conservar los avisos y estados de lectura antes de retirar la tabla.');
        }
        Schema::dropIfExists('notifications');
    }
};
