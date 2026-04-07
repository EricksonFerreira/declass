<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            if (!Schema::hasColumn('empresas', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('dominio');
            }

            if (!Schema::hasColumn('empresas', 'cor_primaria')) {
                $table->string('cor_primaria', 7)->nullable()->after('logo_path');
            }

            if (!Schema::hasColumn('empresas', 'cor_secundaria')) {
                $table->string('cor_secundaria', 7)->nullable()->after('cor_primaria');
            }

            if (!Schema::hasColumn('empresas', 'cor_terciaria')) {
                $table->string('cor_terciaria', 7)->nullable()->after('cor_secundaria');
            }
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            $columns = ['logo_path', 'cor_primaria', 'cor_secundaria', 'cor_terciaria'];
            $existing = [];

            foreach ($columns as $column) {
                if (Schema::hasColumn('empresas', $column)) {
                    $existing[] = $column;
                }
            }

            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
        Schema::enableForeignKeyConstraints();
    }
};
