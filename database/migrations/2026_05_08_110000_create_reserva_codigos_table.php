<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reserva_codigos')) {
            Schema::create('reserva_codigos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->string('codigo', 60)->unique();
                $table->string('tipo_origem', 30)->default('reserva')->comment('reserva, motorista_app');
                $table->unsignedBigInteger('reserva_id')->nullable();
                $table->unsignedBigInteger('reserva_motorista_app_id')->nullable();
                $table->string('status_label', 40)->nullable();
                $table->dateTime('data_inicio')->nullable();
                $table->dateTime('data_fim')->nullable();
                $table->decimal('valor_total', 12, 2)->nullable();
                $table->timestamps();

                $table->index('empresa_id');
                $table->index('reserva_id');
                $table->index('reserva_motorista_app_id');
                $table->index('tipo_origem');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_codigos');
    }
};
