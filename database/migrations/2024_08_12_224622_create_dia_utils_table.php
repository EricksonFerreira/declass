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
        if (!Schema::hasTable('dias_uteis')) {
            Schema::create('dias_uteis', function (Blueprint $table) {
                $table->id();
                $table->integer('dia')->nullable();
                $table->string('nome')->nullable();
                $table->string('ativo',2)->default('S');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dias_uteis');
    }
};
