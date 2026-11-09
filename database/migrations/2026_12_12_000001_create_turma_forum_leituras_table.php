<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marcacao de leitura do forum por usuario e topico: guarda ate
 * quando cada pessoa leu cada conversa — base para o contador de
 * nao lidas e o divisor estilo WhatsApp no chat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turma_forum_leituras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('post_id')->constrained('turma_forum_posts')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('lido_em');
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turma_forum_leituras');
    }
};
