<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('empresas')) {
            Schema::table('empresas', function (Blueprint $table): void {
                if (!Schema::hasColumn('empresas', 'aluguel_app_campos_personalizados')) {
                    $table->json('aluguel_app_campos_personalizados')
                        ->nullable()
                        ->after('aluguel_app_documento_adicional_label');
                }
            });
        }

        if (!Schema::hasTable('motorista_app_solicitacoes')) {
            Schema::create('motorista_app_solicitacoes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('plano_motorista_app_id')->nullable()->constrained('planos_motorista_app')->nullOnDelete();
                $table->string('status', 20)->default('pendente');
                $table->json('dados_json')->nullable();
                $table->json('documentos_json')->nullable();
                $table->timestamps();

                $table->index(['empresa_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('motorista_app_solicitacoes')) {
            Schema::dropIfExists('motorista_app_solicitacoes');
        }

        if (Schema::hasTable('empresas')) {
            Schema::table('empresas', function (Blueprint $table): void {
                if (Schema::hasColumn('empresas', 'aluguel_app_campos_personalizados')) {
                    $table->dropColumn('aluguel_app_campos_personalizados');
                }
            });
        }
    }
};
