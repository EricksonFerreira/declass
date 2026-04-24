<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // empresa_planos: trocar 'automatico' por 'cartao' e adicionar renovacao_automatica
        DB::statement("ALTER TABLE empresa_planos MODIFY COLUMN metodo_cobranca ENUM('boleto','pix','cartao','manual') NOT NULL DEFAULT 'manual'");

        Schema::table('empresa_planos', function (Blueprint $table) {
            $table->boolean('renovacao_automatica')->default(false)->after('dia_vencimento');
            $table->string('cartao_ultimos_digitos', 4)->nullable()->after('renovacao_automatica');
            $table->string('cartao_bandeira', 30)->nullable()->after('cartao_ultimos_digitos');
        });

        // empresa_plano_pagamentos: garantir que 'cartao' já existe no enum metodo
        DB::statement("ALTER TABLE empresa_plano_pagamentos MODIFY COLUMN metodo ENUM('boleto','cartao','pix','manual','transferencia') NOT NULL DEFAULT 'boleto'");
    }

    public function down(): void
    {
        Schema::table('empresa_planos', function (Blueprint $table) {
            $table->dropColumn(['renovacao_automatica', 'cartao_ultimos_digitos', 'cartao_bandeira']);
        });

        DB::statement("ALTER TABLE empresa_planos MODIFY COLUMN metodo_cobranca ENUM('boleto','pix','automatico','manual') NOT NULL DEFAULT 'manual'");
        DB::statement("ALTER TABLE empresa_plano_pagamentos MODIFY COLUMN metodo ENUM('boleto','cartao','pix','manual','transferencia') NOT NULL DEFAULT 'boleto'");
    }
};
