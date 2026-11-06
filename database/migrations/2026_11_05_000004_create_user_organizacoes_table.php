<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vinculo usuario <-> organizacao (ex.: administrador da rede).
 * Um usuario pode pertencer a uma ou varias organizacoes do tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_organizacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organizacao_id')->constrained('organizacoes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'organizacao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_organizacoes');
    }
};
