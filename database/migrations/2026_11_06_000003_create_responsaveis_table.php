<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Responsavel: responsavel legal/pedagogico/financeiro de alunos.
 * Dados pessoais em `pessoas`; aqui ficam dados profissionais
 * e o vinculo opcional com conta de usuario (portal futuro).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsaveis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('pessoa_id')->unique()->constrained('pessoas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('profissao', 120)->nullable();
            $table->string('local_trabalho', 160)->nullable();
            $table->text('observacoes')->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsaveis');
    }
};
