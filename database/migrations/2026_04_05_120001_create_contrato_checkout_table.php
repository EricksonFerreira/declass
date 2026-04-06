<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_checkout', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('reserva_id');
            $table->unsignedInteger('veiculo_id');
            $table->unsignedInteger('empresa_id')->nullable();
            $table->decimal('quilometragem_devolucao', 12, 2);
            $table->string('nivel_combustivel_devolucao', 32);
            $table->date('data_devolucao');
            $table->string('hora_devolucao', 8);
            $table->text('notas_devolucao')->nullable();
            $table->text('observacoes_devolucao')->nullable();
            $table->timestamps();

            $table->unique('reserva_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_checkout');
    }
};
