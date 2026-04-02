<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permissions')) {
            try {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->dropUnique(['name', 'guard_name']);
                });
            } catch (\Throwable $e) {
                // Index may already be removed.
            }

            try {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->unique(['empresa_id', 'name', 'guard_name'], 'permissions_empresa_name_guard_unique');
                });
            } catch (\Throwable $e) {
                // Index may already exist.
            }
        }

        if (Schema::hasTable('roles')) {
            try {
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropUnique(['name', 'guard_name']);
                });
            } catch (\Throwable $e) {
                // Index may already be removed.
            }

            try {
                Schema::table('roles', function (Blueprint $table) {
                    $table->unique(['empresa_id', 'name', 'guard_name'], 'roles_empresa_name_guard_unique');
                });
            } catch (\Throwable $e) {
                // Index may already exist.
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            try {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->dropUnique('permissions_empresa_name_guard_unique');
                });
            } catch (\Throwable $e) {
                // Ignore.
            }

            try {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->unique(['name', 'guard_name']);
                });
            } catch (\Throwable $e) {
                // Ignore.
            }
        }

        if (Schema::hasTable('roles')) {
            try {
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropUnique('roles_empresa_name_guard_unique');
                });
            } catch (\Throwable $e) {
                // Ignore.
            }

            try {
                Schema::table('roles', function (Blueprint $table) {
                    $table->unique(['name', 'guard_name']);
                });
            } catch (\Throwable $e) {
                // Ignore.
            }
        }
    }
};
