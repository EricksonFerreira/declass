<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('permissions')) {
            try {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->dropUnique(['name', 'guard_name']);
    });
            } catch (\Throwable $e) {
                // Index may already be removed.
            }

            try {
                if (!Schema::hasTable('permissions')) { Schema::enableForeignKeyConstraints(); return; }
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
                if (!Schema::hasTable('roles')) { Schema::enableForeignKeyConstraints(); return; }
                Schema::table('roles', function (Blueprint $table) {
                    $table->unique(['empresa_id', 'name', 'guard_name'], 'roles_empresa_name_guard_unique');
                });
            } catch (\Throwable $e) {
                // Index may already exist.
            }
        }
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('permissions')) {
            try {
                Schema::table('permissions', function (Blueprint $table) {
                    $table->dropUnique('permissions_empresa_name_guard_unique');
                });
            } catch (\Throwable $e) {
                // Ignore.
            }

            try {
                if (!Schema::hasTable('permissions')) { Schema::enableForeignKeyConstraints(); return; }
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
                if (!Schema::hasTable('roles')) { Schema::enableForeignKeyConstraints(); return; }
                Schema::table('roles', function (Blueprint $table) {
                    $table->unique(['name', 'guard_name']);
                });
            } catch (\Throwable $e) {
                // Ignore.
            }
        }
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }
};
