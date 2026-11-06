<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Frequencia: presenca de um aluno (matricula) em uma aula
 * registrada no diario. Chamada feita em lote por aula.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frequencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('diario_id')->constrained('diarios')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->string('status', 15)->default('presente');
            $table->text('justificativa')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['matricula_id', 'diario_id'], 'frequencias_unique');
            $table->index('diario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frequencias');
    }
};
