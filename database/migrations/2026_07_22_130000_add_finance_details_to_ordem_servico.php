<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->decimal('valor_pecas', 10, 2)->default(0.00)->after('status');
            $table->decimal('valor_mao_obra', 10, 2)->default(0.00)->after('valor_pecas');
            $table->decimal('valor_total', 10, 2)->default(0.00)->after('valor_mao_obra');
        });

        Schema::table('ordem_servico_itens', function (Blueprint $table) {
            $table->string('tipo_item', 16)->default('servico')->after('descricao_servico'); // peca | servico
            $table->integer('quantidade')->default(1)->after('tipo_item');
            $table->decimal('valor_unitario', 10, 2)->default(0.00)->after('quantidade');
            $table->decimal('valor_total', 10, 2)->default(0.00)->after('valor_unitario');
        });
    }

    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropColumn(['valor_pecas', 'valor_mao_obra', 'valor_total']);
        });

        Schema::table('ordem_servico_itens', function (Blueprint $table) {
            $table->dropColumn(['tipo_item', 'quantidade', 'valor_unitario', 'valor_total']);
        });
    }
};
