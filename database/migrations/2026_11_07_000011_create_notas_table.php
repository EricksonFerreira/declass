<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nota: resultado de um aluno (matricula) em uma avaliacao.
 * nota NULL = ainda nao lancada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('avaliacao_id')->constrained('avaliacoes')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->decimal('nota', 6, 2)->nullable();
            $table->text('observacao')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['avaliacao_id', 'matricula_id'], 'notas_unique');
            $table->index('matricula_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
