<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            if (! Schema::hasColumn('empresas', 'aluguel_app_exigir_aprovacao_documentos')) {
                $table->enum('aluguel_app_exigir_aprovacao_documentos', ['S', 'N'])
                    ->default('N')
                    ->after('aluguel_app_ativo');
            }

            if (! Schema::hasColumn('empresas', 'aluguel_app_documentos_obrigatorios')) {
                $table->json('aluguel_app_documentos_obrigatorios')
                    ->nullable()
                    ->after('aluguel_app_exigir_aprovacao_documentos');
            }

            if (! Schema::hasColumn('empresas', 'aluguel_app_documento_adicional_label')) {
                $table->string('aluguel_app_documento_adicional_label', 120)
                    ->nullable()
                    ->after('aluguel_app_documentos_obrigatorios');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table): void {
            if (Schema::hasColumn('empresas', 'aluguel_app_documento_adicional_label')) {
                $table->dropColumn('aluguel_app_documento_adicional_label');
            }

            if (Schema::hasColumn('empresas', 'aluguel_app_documentos_obrigatorios')) {
                $table->dropColumn('aluguel_app_documentos_obrigatorios');
            }

            if (Schema::hasColumn('empresas', 'aluguel_app_exigir_aprovacao_documentos')) {
                $table->dropColumn('aluguel_app_exigir_aprovacao_documentos');
            }
        });
    }
};
