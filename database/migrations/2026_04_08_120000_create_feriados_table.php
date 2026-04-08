<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feriados', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('estacao_id')->nullable();
            $table->date('data');
            $table->string('nome', 255);
            $table->char('global', 1)->default('N'); // 'S' = global (system-wide / empresa-independent), 'N' = specific
            $table->char('ativo', 1)->default('S');
            $table->timestamps();

            $table->index(['empresa_id']);
            $table->index(['estacao_id']);
            $table->unique(['empresa_id', 'estacao_id', 'data'], 'u_feriados_empresa_estacao_data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feriados');
    }
};
