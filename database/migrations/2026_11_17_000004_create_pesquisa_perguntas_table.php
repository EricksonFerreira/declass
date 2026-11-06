<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesquisa_perguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesquisa_id')->constrained('pesquisas')->cascadeOnDelete();
            $table->unsignedSmallInteger('ordem')->default(1);
            $table->string('pergunta');
            $table->string('tipo', 20)->default('nota'); // nota|sim_nao|texto
            $table->char('obrigatoria', 1)->default('S');
            $table->timestamps();

            $table->index(['pesquisa_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesquisa_perguntas');
    }
};
