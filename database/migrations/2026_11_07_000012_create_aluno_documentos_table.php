<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documento do aluno: arquivo digitalizado vinculado ao aluno
 * (RG, certidao, atestados, laudos, historico etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aluno_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->string('tipo', 40)->default('outro');
            $table->string('titulo', 150);
            $table->text('descricao')->nullable();
            $table->string('arquivo_path')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'aluno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_documentos');
    }
};
