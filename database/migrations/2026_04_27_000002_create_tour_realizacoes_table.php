<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tour_realizacoes')) return;

        Schema::create('tour_realizacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tour_tela_id')->constrained('tour_telas')->onDelete('cascade');
            $table->boolean('feito')->default(false);
            $table->timestamp('feito_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tour_tela_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_realizacoes');
    }
};
