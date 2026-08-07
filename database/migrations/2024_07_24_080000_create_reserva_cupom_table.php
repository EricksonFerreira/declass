<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('reserva_cupom')) {
            Schema::create('reserva_cupom', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
                $table->foreignId('cupom_desconto_id')->constrained('cupom_desconto')->cascadeOnDelete();
                $table->timestamps();
            });
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('reserva_cupom');
        Schema::enableForeignKeyConstraints();
    }
};
?>
