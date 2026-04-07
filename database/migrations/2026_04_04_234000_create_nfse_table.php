<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('nfse')) {
            return;
        }

        Schema::create('nfse', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('reserva_id')->nullable();
            $table->string('status', 30)->nullable();
            $table->string('referencia', 60)->nullable();
            $table->string('ambiente', 20)->nullable();
            $table->string('nfse_id', 80)->nullable();
            $table->string('dps_id', 80)->nullable();
            $table->string('numero', 40)->nullable();
            $table->string('serie', 20)->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->json('cancelamento_response')->nullable();
            $table->timestamp('cancelada_em')->nullable();
            $table->timestamps();
            $table->index(['empresa_id', 'reserva_id']);
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('nfse');
        Schema::enableForeignKeyConstraints();
    }
};
