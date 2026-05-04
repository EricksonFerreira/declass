<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('estacaos');
    }

    public function down(): void
    {
        Schema::create('estacaos', function ($table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->timestamps();
        });
    }
};
