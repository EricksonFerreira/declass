<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['email']);
            });
        } catch (\Throwable $e) {
            // Index may already have been removed.
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique(['email', 'empresa_id'], 'users_email_empresa_id_unique');
            });
        } catch (\Throwable $e) {
            // Index may already exist.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_email_empresa_id_unique');
            });
        } catch (\Throwable $e) {
            // Index may not exist.
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email');
            });
        } catch (\Throwable $e) {
            // Index may already exist.
        }
    }
};
