<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('reserva_cupom')) {
            Schema::create('reserva_cupom', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reserva_id');
                $table->unsignedBigInteger('cupom_desconto_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva_cupom');
    }
};
?>
