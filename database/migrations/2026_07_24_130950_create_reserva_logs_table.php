<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('reserva_logs')) {
            return;
        }

        Schema::create('reserva_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reserva_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('acao', 120)->nullable();
            $table->enum('nivel', ['info', 'success', 'warning', 'danger'])->default('info');
            $table->text('detalhes')->nullable();
            $table->timestamps();

            $table->foreign('reserva_id')
                ->references('id')
                ->on('reservas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva_logs');
    }
};
