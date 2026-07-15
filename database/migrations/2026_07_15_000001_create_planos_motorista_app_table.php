<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('planos_motorista_app')) {
            Schema::create('planos_motorista_app', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
                $table->string('nome', 120);
                $table->decimal('valor_periodo', 10, 2);
                $table->enum('periodo', ['semana', 'quinze', 'mes', 'trimestre', 'semestre', 'ano']);
                $table->enum('km_tipo', ['livre', 'limitado'])->default('livre');
                $table->unsignedInteger('km_por_periodo')->nullable();
                $table->decimal('calcao', 10, 2)->nullable();
                $table->enum('ativo', ['S', 'N'])->default('S');
                $table->unsignedInteger('ordem')->default(0);
                $table->timestamps();

                $table->index(['empresa_id', 'ativo', 'ordem'], 'plano_motorista_app_empresa_ativo_ordem_idx');
            });
        }

        if (! Schema::hasTable('plano_motorista_app_opcional_grupo_veiculo')) {
            Schema::create('plano_motorista_app_opcional_grupo_veiculo', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('plano_motorista_app_id')
                    ->constrained('planos_motorista_app', 'id', 'pmapp_plano_fk')
                    ->cascadeOnDelete();
                $table->foreignId('opcional_grupo_veiculo_id')
                    ->constrained('opcional_grupo_veiculos', 'id', 'pmapp_opcional_fk')
                    ->cascadeOnDelete();
                $table->timestamps();

                $table->unique(
                    ['plano_motorista_app_id', 'opcional_grupo_veiculo_id'],
                    'plano_motorista_app_opcional_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plano_motorista_app_opcional_grupo_veiculo');
        Schema::dropIfExists('planos_motorista_app');
    }
};
