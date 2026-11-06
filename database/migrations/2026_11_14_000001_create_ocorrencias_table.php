<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ocorrencia disciplinar/pedagogica registrada sobre um aluno:
 * elogio, advertencia, ocorrencia grave etc., com gravidade,
 * descricao, medidas adotadas e responsavel pelo registro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocorrencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('data');
            $table->string('tipo', 30);
            $table->string('gravidade', 20)->default('leve');
            $table->string('local', 100)->nullable();
            $table->text('descricao');
            $table->text('medida_adotada')->nullable();
            $table->boolean('notifica_responsavel')->default(false);
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'aluno_id', 'data']);
            $table->index(['empresa_id', 'escola_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocorrencias');
    }
};
