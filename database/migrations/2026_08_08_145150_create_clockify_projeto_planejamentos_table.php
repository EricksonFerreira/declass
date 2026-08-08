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
        Schema::create('clockify_projeto_planejamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clockify_projeto_id');
            $table->date('data');
            $table->decimal('horas', 5, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('clockify_projeto_id')->references('id')->on('clockify_projetos')->onDelete('cascade');
            $table->unique(['clockify_projeto_id', 'data']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clockify_projeto_planejamentos');
    }
};
