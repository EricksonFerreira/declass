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
        if (Schema::hasTable('status_reservas')) {
            return;
        }

        Schema::create('status_reservas', function (Blueprint $table) {
            $table->id();
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
        Schema::dropIfExists('status_reservas');
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }
};
