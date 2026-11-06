<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Veiculo da frota de transporte de uma escola:
 * placa, modelo, capacidade e dados do motorista.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->string('placa', 12);
            $table->string('modelo', 60);
            $table->unsignedSmallInteger('ano')->nullable();
            $table->unsignedSmallInteger('capacidade')->nullable();
            $table->string('motorista_nome', 100)->nullable();
            $table->string('motorista_telefone', 20)->nullable();
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
        Schema::dropIfExists('veiculos');
    }
};
