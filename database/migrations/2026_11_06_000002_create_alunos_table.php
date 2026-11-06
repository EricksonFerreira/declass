<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aluno: vinculo academico de uma pessoa com a escola.
 * Dados pessoais ficam em `pessoas`; aqui ficam os dados
 * escolares, de saude/acessibilidade e acesso ao portal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('pessoa_id')->unique()->constrained('pessoas')->cascadeOnDelete();
            $table->foreignId('escola_id')->nullable()->constrained('escolas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('registro_academico', 30)->nullable();
            $table->date('data_ingresso')->nullable();
            $table->string('turno_preferencial', 20)->nullable();
            $table->text('necessidades_especiais')->nullable();
            $table->text('informacoes_saude')->nullable();
            $table->text('contatos_emergencia')->nullable();
            $table->text('restricoes_acesso')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('empresa_id');
            $table->index('escola_id');
            $table->index('registro_academico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};
