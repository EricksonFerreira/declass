<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('documentacao_veiculo_parcelas')) {
            return;
        }

        Schema::create('documentacao_veiculo_parcelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documentacao_veiculo_id');
            $table->integer('numero_parcela');
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->decimal('valor', 15, 2)->default(0);
            $table->string('status')->default('pendente');
            $table->timestamps();

            $table->foreign('documentacao_veiculo_id')
                ->references('id')
                ->on('documentacoes_veiculo')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentacao_veiculo_parcelas');
    }
};
