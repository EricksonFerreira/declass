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
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('disponibilidade_inicial_veiculos')) { return; }
        Schema::create('disponibilidade_inicial_veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('disponibilidade_inicial_veiculos');
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }
};
