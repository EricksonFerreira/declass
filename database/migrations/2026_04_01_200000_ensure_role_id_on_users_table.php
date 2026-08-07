<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('users')) {
            return;
        }

        if (!Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable();
                $table->index('role_id', 'users_role_id_index');
            });
        }

        if (Schema::hasColumn('users', 'tipo_usuario_id')) {
            DB::statement("
                UPDATE users
                SET role_id = CAST(tipo_usuario_id AS UNSIGNED)
                WHERE role_id IS NULL
                  AND tipo_usuario_id IS NOT NULL
                  AND tipo_usuario_id <> ''
            ");
        }
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'role_id')) {
            return;
        }

        if (Schema::hasColumn('users', 'tipo_usuario_id')) {
            DB::statement("
                UPDATE users
                SET tipo_usuario_id = CAST(role_id AS CHAR)
                WHERE role_id IS NOT NULL
            ");
        }

        if (!Schema::hasTable('users')) { Schema::enableForeignKeyConstraints(); return; }
        Schema::table('users', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('users'))->pluck('name')->toArray();
            if (in_array('users_role_id_index', $indexes)) {
                $table->dropIndex('users_role_id_index');
            }
            $table->dropColumn('role_id');
        });
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }
};

