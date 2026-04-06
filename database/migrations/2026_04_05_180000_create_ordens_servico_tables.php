<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 32)->nullable()->unique();
            $table->unsignedInteger('empresa_id')->nullable();
            $table->unsignedInteger('veiculo_id');
            $table->unsignedInteger('reserva_id')->nullable();
            $table->unsignedBigInteger('user_id_abertura');
            $table->unsignedBigInteger('user_id_encerramento')->nullable();
            $table->string('status', 24)->default('aberta'); // aberta | em_andamento | concluida | cancelada
            $table->text('observacoes')->nullable();
            $table->text('relatorio_conclusao')->nullable();
            $table->timestamp('data_conclusao')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('veiculo_id');
        });

        Schema::create('ordem_servico_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ordem_servico_id');
            $table->unsignedInteger('veiculo_dano_id')->nullable();
            $table->text('descricao_servico');
            $table->text('resultado')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico')->cascadeOnDelete();
            $table->index('veiculo_dano_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordem_servico_itens');
        Schema::dropIfExists('ordens_servico');
    }
};
