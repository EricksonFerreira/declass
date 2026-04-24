<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_plano_pagamentos', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id', 200)->nullable()->after('gateway_payment_id');
            $table->text('pix_qr_code_image')->nullable()->after('pix_chave');   // base64 da imagem
            $table->text('pix_qr_code_text')->nullable()->after('pix_qr_code_image'); // copia-e-cola
            $table->timestamp('pix_expira_em')->nullable()->after('pix_qr_code_text');
        });
    }

    public function down(): void
    {
        Schema::table('empresa_plano_pagamentos', function (Blueprint $table) {
            $table->dropColumn(['stripe_payment_intent_id', 'pix_qr_code_image', 'pix_qr_code_text', 'pix_expira_em']);
        });
    }
};
