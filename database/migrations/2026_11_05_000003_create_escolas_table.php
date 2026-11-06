<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Escola/unidade: instituicao de ensino vinculada a um tenant
 * e, opcionalmente, a uma organizacao (rede).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escolas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('organizacao_id')->nullable()->constrained('organizacoes')->nullOnDelete();
            $table->string('nome');
            $table->string('slug');
            $table->string('cnpj', 18)->nullable();
            $table->string('email')->nullable();
            $table->string('telefone', 30)->nullable();
            $table->text('endereco')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->unsignedInteger('capacidade_alunos')->nullable();
            $table->string('turno_padrao', 30)->nullable(); // matutino | vespertino | integral
            $table->string('logo_path')->nullable();
            $table->string('cor_primaria', 7)->nullable();
            $table->string('cor_secundaria', 7)->nullable();
            $table->string('ativa', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'slug']);
            $table->index(['empresa_id', 'ativa']);
            $table->index('organizacao_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escolas');
    }
};
