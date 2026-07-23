<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plano_motorista_app_grupo_frota')) {
            Schema::create('plano_motorista_app_grupo_frota', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('plano_motorista_app_id')
                    ->constrained('planos_motorista_app')
                    ->cascadeOnDelete();
                $table->integer('grupo_frota_id');
                
                $table->foreign('grupo_frota_id')
                    ->references('id')
                    ->on('grupos_frota')
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique(
                    ['plano_motorista_app_id', 'grupo_frota_id'],
                    'plano_motorista_app_grupo_frota_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plano_motorista_app_grupo_frota');
    }
};
