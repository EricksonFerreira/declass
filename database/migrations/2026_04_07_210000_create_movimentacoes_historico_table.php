<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes_historico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movimentacao_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('campo')->nullable();        // campo alterado
            $table->text('valor_anterior')->nullable();
            $table->text('valor_novo')->nullable();
            $table->string('acao', 50)->default('update'); // create, update, concluir
            $table->text('observacao')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();

            $table->index(['movimentacao_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_historico');
    }
};
