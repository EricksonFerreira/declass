<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('gateway_pagamento', 30)->nullable()->after('site_aviso');
            $table->text('gateway_api_key')->nullable();
            $table->string('gateway_ambiente', 10)->default('sandbox');
            $table->string('gateway_webhook_token', 64)->nullable();
        });

        Schema::table('mensalidades', function (Blueprint $table) {
            $table->string('gateway', 30)->nullable()->after('boleto_nome');
            $table->string('gateway_payment_id', 60)->nullable()->index();
            $table->string('gateway_status', 30)->nullable();
            $table->string('boleto_url')->nullable();
            $table->string('linha_digitavel', 80)->nullable();
            $table->text('pix_copia_cola')->nullable();
            $table->timestamp('gateway_synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['gateway_pagamento', 'gateway_api_key', 'gateway_ambiente', 'gateway_webhook_token']);
        });
        Schema::table('mensalidades', function (Blueprint $table) {
            $table->dropIndex(['gateway_payment_id']);
            $table->dropColumn(['gateway', 'gateway_payment_id', 'gateway_status', 'boleto_url', 'linha_digitavel', 'pix_copia_cola', 'gateway_synced_at']);
        });
    }
};
