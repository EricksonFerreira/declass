<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Livro: acervo da biblioteca de cada escola.
 * exemplares = total de copias; a disponibilidade e derivada
 * dos emprestimos ativos (nao negativa estoque).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->string('titulo', 180);
            $table->string('autor', 140)->nullable();
            $table->string('editora', 140)->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('categoria', 30)->default('outro');
            $table->unsignedSmallInteger('ano_publicacao')->nullable();
            $table->unsignedSmallInteger('exemplares')->default(1);
            $table->text('descricao')->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'escola_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livros');
    }
};
