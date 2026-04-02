<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'moeda_pagamento')) {
                $table->string('moeda_pagamento', 10)->default('BRL')->after('gateway_pagamento');
            }

            if (!Schema::hasColumn('empresas', 'stripe_public_key')) {
                $table->string('stripe_public_key')->nullable()->after('moeda_pagamento');
            }

            if (!Schema::hasColumn('empresas', 'stripe_secret')) {
                $table->text('stripe_secret')->nullable()->after('stripe_public_key');
            }

            if (!Schema::hasColumn('empresas', 'mercado_livre_public_key')) {
                $table->string('mercado_livre_public_key')->nullable()->after('stripe_secret');
            }

            if (!Schema::hasColumn('empresas', 'mercado_livre_access_token')) {
                $table->text('mercado_livre_access_token')->nullable()->after('mercado_livre_public_key');
            }

            if (!Schema::hasColumn('empresas', 'paypal_client_id')) {
                $table->string('paypal_client_id')->nullable()->after('mercado_livre_access_token');
            }

            if (!Schema::hasColumn('empresas', 'paypal_client_secret')) {
                $table->text('paypal_client_secret')->nullable()->after('paypal_client_id');
            }

            if (!Schema::hasColumn('empresas', 'paypal_mode')) {
                $table->string('paypal_mode', 20)->default('sandbox')->after('paypal_client_secret');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            foreach ([
                'moeda_pagamento',
                'stripe_public_key',
                'stripe_secret',
                'mercado_livre_public_key',
                'mercado_livre_access_token',
                'paypal_client_id',
                'paypal_client_secret',
                'paypal_mode',
            ] as $column) {
                if (Schema::hasColumn('empresas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
