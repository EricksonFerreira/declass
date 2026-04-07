<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('transacoes')) { return; }
        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('reserva_id')->constrained()->onDelete('cascade');
            $table->decimal('valor', 10, 2);
            $table->string('metodo_pagamento');
            $table->foreignId('metodo_pagamento_id')->constrained()->onDelete('cascade'); // traduzir para o nome do metodo de pagamento
            $table->foreignId('tipo_pagamento_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacoes');
    }
};
