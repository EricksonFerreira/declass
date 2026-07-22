<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lancamentos_financeiros')) {
            Schema::create('lancamentos_financeiros', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('cascade');
                $table->string('descricao');
                $table->decimal('valor', 15, 2);
                $table->enum('tipo', ['receita', 'despesa']);
                $table->foreignId('categoria_financeira_id')->nullable()->constrained('categorias_financeiras')->onDelete('set null');
                $table->date('data_vencimento');
                $table->date('data_pagamento')->nullable();
                $table->enum('status', ['pendente', 'pago', 'cancelado'])->default('pendente');
                $table->string('metodo_pagamento')->nullable();
                $table->unsignedInteger('veiculo_id')->nullable();
                $table->unsignedInteger('reserva_id')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('observacoes')->nullable();
                $table->timestamps();

                $table->foreign('veiculo_id')->references('id')->on('veiculos')->onDelete('cascade');
                $table->foreign('reserva_id')->references('id')->on('reservas')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lancamentos_financeiros');
    }
};
