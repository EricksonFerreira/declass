<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('ordem_servico_anexos')) {
            return;
        }

        Schema::create('ordem_servico_anexos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ordem_servico_id');
            $table->string('path', 512);
            $table->string('original_name', 255)->nullable();
            $table->string('mime', 128)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['ordem_servico_id', 'created_at']);
            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico')->cascadeOnDelete();
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ordem_servico_anexos');
        Schema::enableForeignKeyConstraints();
    }
};

