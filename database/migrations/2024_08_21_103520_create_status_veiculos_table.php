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
        if (!Schema::hasTable('status_veiculos')) {
            Schema::create('status_veiculos', function (Blueprint $table) {
                $table->id();
                $table->string('nome')->nullable();
                $table->timestamps();
    });
        }
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('status_veiculos');
        Schema::enableForeignKeyConstraints();
    }
};
