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
        if (Schema::hasTable('horarios_diarios')) {
            return;
        }

        Schema::create('horarios_diarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            $table->char('ativo', 1)->default('S');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_diarios');
    }
};
