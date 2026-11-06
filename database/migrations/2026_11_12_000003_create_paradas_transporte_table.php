<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parada de uma rota de transporte: ponto de embarque com
 * ordem e horario previsto opcionais.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paradas_transporte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('rota_id')->constrained('rotas_transporte')->cascadeOnDelete();
            $table->string('nome', 80);
            $table->unsignedSmallInteger('ordem')->default(1);
            $table->time('horario_previsto')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'rota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paradas_transporte');
    }
};
