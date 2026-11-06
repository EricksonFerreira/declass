<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vinculo usuario <-> escola/unidade.
 * Um usuario pode atuar em uma ou varias escolas do tenant
 * (ex.: professor que da aula em duas unidades).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_escolas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'escola_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_escolas');
    }
};
