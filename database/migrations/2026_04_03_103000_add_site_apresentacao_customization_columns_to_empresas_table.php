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
            if (!Schema::hasColumn('empresas', 'site_apresentacao_cores')) {
                $table->longText('site_apresentacao_cores')
                    ->nullable()
                    ->after('site_apresentacao_imagens');
            }

            if (!Schema::hasColumn('empresas', 'site_apresentacao_imagens_config')) {
                $table->longText('site_apresentacao_imagens_config')
                    ->nullable()
                    ->after('site_apresentacao_cores');
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
            $columns = [];

            foreach (['site_apresentacao_cores', 'site_apresentacao_imagens_config'] as $column) {
                if (Schema::hasColumn('empresas', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
        Schema::enableForeignKeyConstraints();
    }
};
