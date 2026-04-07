<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('transacoes')) {
            return;
        }

        Schema::table('transacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('transacoes', 'moeda')) {
                $table->string('moeda', 10)->default('BRL')->after('valor');
            }
            if (!Schema::hasColumn('transacoes', 'gateway_transaction_id')) {
                $table->string('gateway_transaction_id')->nullable()->after('metodo_pagamento');
            }
            if (!Schema::hasColumn('transacoes', 'info_pagamento')) {
                $table->json('info_pagamento')->nullable()->after('status');
            }
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('transacoes')) {
            return;
        }

        Schema::table('transacoes', function (Blueprint $table) {
            foreach (['moeda', 'gateway_transaction_id', 'info_pagamento'] as $column) {
                if (Schema::hasColumn('transacoes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        Schema::enableForeignKeyConstraints();
    }
};
