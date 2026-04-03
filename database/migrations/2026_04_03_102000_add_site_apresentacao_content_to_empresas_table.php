<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            if (!Schema::hasColumn('empresas', 'site_apresentacao_textos')) {
                $table->longText('site_apresentacao_textos')->nullable()->after('site_apresentacao_botao_url');
            }

            if (!Schema::hasColumn('empresas', 'site_apresentacao_imagens')) {
                $table->longText('site_apresentacao_imagens')->nullable()->after('site_apresentacao_textos');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            $columns = [];
            foreach (['site_apresentacao_textos', 'site_apresentacao_imagens'] as $column) {
                if (Schema::hasColumn('empresas', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
