<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_requests', function (Blueprint $table) {
            $table->id();
            $table->string('requested_name', 80);
            $table->text('justification');
            $table->text('institutional_reason');
            $table->text('functions');
            $table->string('scope', 120);
            $table->text('observations')->nullable();
            $table->json('requested_permissions');
            $table->json('analysis_result')->nullable();
            $table->string('status', 40);
            $table->string('requested_by', 20);
            $table->string('director_id', 20);
            $table->string('reviewed_by', 20)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->string('document_path');
            $table->char('document_hash', 64)->index();
            $table->string('document_original_name');
            $table->string('document_mime', 80);
            $table->unsignedBigInteger('document_size');
            $table->json('document_analysis')->nullable();
            $table->unsignedBigInteger('created_role_id')->nullable();
            $table->timestamps();
            $table->foreign('requested_by')->references('cod_usu')->on('users');
            $table->foreign('reviewed_by')->references('cod_usu')->on('users');
            $table->foreign('director_id')->references('cod_dir')->on('director');
        });
    }

    public function down(): void { Schema::dropIfExists('role_requests'); }
};
