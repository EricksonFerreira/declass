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
        Schema::create('pais', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('sigla',2)->nullable();
            $table->timestamps();
    });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pais');
        Schema::enableForeignKeyConstraints();
    }
};
