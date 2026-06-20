<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'auth_provider')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('auth_provider')->default('local')->change();
        });

        DB::table('users')
            ->whereNull('auth_provider')
            ->update(['auth_provider' => 'local']);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'auth_provider')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('auth_provider')->nullable()->change();
        });
    }
};
