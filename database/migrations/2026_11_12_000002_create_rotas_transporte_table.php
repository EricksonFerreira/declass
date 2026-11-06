<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rota de transporte escolar de uma escola: vinculo opcional
 * com o veiculo que a opera e horarios de ida/volta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotas_transporte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->nullOnDelete();
            $table->string('nome', 80);
            $table->time('horario_ida')->nullable();
            $table->time('horario_volta')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'escola_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotas_transporte');
    }
};
