<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plano_motorista_app_opcionais')) {
            Schema::create('plano_motorista_app_opcionais', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('plano_motorista_app_id')
                    ->constrained('planos_motorista_app', 'id', 'pmapp_opc_plano_fk')
                    ->cascadeOnDelete();
                $table->integer('opcional_id');
                $table->foreign('opcional_id', 'pmapp_opc_item_fk')
                    ->references('id')
                    ->on('opcionais')
                    ->cascadeOnDelete();
                $table->timestamps();

                $table->unique(
                    ['plano_motorista_app_id', 'opcional_id'],
                    'plano_motorista_app_opcional_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plano_motorista_app_opcionais');
    }
};
