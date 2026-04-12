<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reserva_satisfacoes')) {
            Schema::dropIfExists('reserva_satisfacoes');
        }

        Schema::create('reserva_satisfacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->integer('reserva_id')->unique();
            $table->unsignedBigInteger('respondente_user_id')->nullable()->index();
            $table->unsignedTinyInteger('satisfacao_geral');
            $table->unsignedTinyInteger('facilidade_reserva');
            $table->unsignedTinyInteger('nps_nota');
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->foreign('reserva_id')->references('id')->on('reservas')->cascadeOnDelete();
            $table->foreign('respondente_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_satisfacoes');
    }
};
