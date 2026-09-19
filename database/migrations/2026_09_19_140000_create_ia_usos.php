<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia_usos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provedor', 20);
            $table->string('modelo', 60)->nullable();
            // Onde a IA foi usada: licitacao.analise, resumo.processo…
            $table->string('recurso', 50);
            $table->unsignedInteger('tokens_entrada')->default(0);
            $table->unsignedInteger('tokens_saida')->default(0);
            // Custo estimado em USD pela tabela de preços do IaService
            $table->decimal('custo_estimado', 12, 6)->nullable();
            $table->string('status', 10)->default('ok'); // ok | erro
            $table->string('erro', 500)->nullable();
            $table->unsignedInteger('duracao_ms')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'created_at']);
            $table->index(['recurso', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_usos');
    }
};
