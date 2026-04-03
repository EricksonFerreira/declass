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
            if (!Schema::hasColumn('empresas', 'site_apresentacao_ativo')) {
                $table->string('site_apresentacao_ativo', 1)->default('S')->after('dominio');
            }

            if (!Schema::hasColumn('empresas', 'site_apresentacao_titulo')) {
                $table->string('site_apresentacao_titulo', 120)->nullable()->after('site_apresentacao_ativo');
            }

            if (!Schema::hasColumn('empresas', 'site_apresentacao_subtitulo')) {
                $table->string('site_apresentacao_subtitulo', 180)->nullable()->after('site_apresentacao_titulo');
            }

            if (!Schema::hasColumn('empresas', 'site_apresentacao_descricao')) {
                $table->text('site_apresentacao_descricao')->nullable()->after('site_apresentacao_subtitulo');
            }

            if (!Schema::hasColumn('empresas', 'site_apresentacao_botao_texto')) {
                $table->string('site_apresentacao_botao_texto', 60)->nullable()->after('site_apresentacao_descricao');
            }

            if (!Schema::hasColumn('empresas', 'site_apresentacao_botao_url')) {
                $table->string('site_apresentacao_botao_url')->nullable()->after('site_apresentacao_botao_texto');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            $columns = [
                'site_apresentacao_ativo',
                'site_apresentacao_titulo',
                'site_apresentacao_subtitulo',
                'site_apresentacao_descricao',
                'site_apresentacao_botao_texto',
                'site_apresentacao_botao_url',
            ];

            $existingColumns = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('empresas', $column)) {
                    $existingColumns[] = $column;
                }
            }

            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
