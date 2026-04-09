<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('multas', function (Blueprint $table) {
            $table->enum('destino_cobranca', ['empresa', 'cliente'])->default('empresa')->after('observacao')
                ->comment('Quem absorve o custo da multa');
            $table->enum('forma_pagamento', ['dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'boleto', 'debito_automatico'])->nullable()->after('destino_cobranca');
            $table->timestamp('pago_em')->nullable()->after('forma_pagamento');
            $table->unsignedBigInteger('pago_por_user_id')->nullable()->after('pago_em');
            $table->string('referencia_pagamento', 100)->nullable()->after('pago_por_user_id')
                ->comment('Nº do comprovante, NSU, código PIX etc.');
            $table->foreign('pago_por_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('sinistros', function (Blueprint $table) {
            $table->enum('forma_pagamento_empresa', ['dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'boleto', 'transferencia'])->nullable()->after('valor_custo_empresa')
                ->comment('Forma de pagamento do custo absorvido pela empresa');
            $table->timestamp('pago_empresa_em')->nullable()->after('forma_pagamento_empresa');
            $table->unsignedBigInteger('pago_empresa_por_user_id')->nullable()->after('pago_empresa_em');
            $table->string('referencia_pagamento_empresa', 100)->nullable()->after('pago_empresa_por_user_id');
            $table->enum('forma_pagamento_cliente', ['dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'boleto'])->nullable()->after('referencia_pagamento_empresa')
                ->comment('Forma de pagamento do custo cobrado do cliente');
            $table->timestamp('pago_cliente_em')->nullable()->after('forma_pagamento_cliente');
            $table->unsignedBigInteger('pago_cliente_por_user_id')->nullable()->after('pago_cliente_em');
            $table->string('referencia_pagamento_cliente', 100)->nullable()->after('pago_cliente_por_user_id');
            $table->foreign('pago_empresa_por_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('pago_cliente_por_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('multas', function (Blueprint $table) {
            $table->dropForeign(['pago_por_user_id']);
            $table->dropColumn(['destino_cobranca', 'forma_pagamento', 'pago_em', 'pago_por_user_id', 'referencia_pagamento']);
        });

        Schema::table('sinistros', function (Blueprint $table) {
            $table->dropForeign(['pago_empresa_por_user_id']);
            $table->dropForeign(['pago_cliente_por_user_id']);
            $table->dropColumn([
                'forma_pagamento_empresa', 'pago_empresa_em', 'pago_empresa_por_user_id', 'referencia_pagamento_empresa',
                'forma_pagamento_cliente', 'pago_cliente_em', 'pago_cliente_por_user_id', 'referencia_pagamento_cliente',
            ]);
        });
    }
};
