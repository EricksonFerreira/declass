<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Etapa: estagio de ensino (educacao infantil, fundamental,
 * medio, EJA...) configuravel por tenant. As turmas referenciam
 * a etapa que cursam.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('segmento', 30)->default('outro');
            $table->smallInteger('ordem')->default(0);
            $table->text('descricao')->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'segmento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etapas');
    }
};
