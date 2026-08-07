<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Classificações Fiscais
        Schema::create('classificacoes_fiscais', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->string('ncm', 8)->nullable();
            $table->string('tipo_bem', 50)->nullable();
            $table->timestamps();
        });

        // 2. Add classification to Veiculos
        Schema::table('veiculos', function (Blueprint $table) {
            $table->unsignedBigInteger('classificacao_fiscal_id')->nullable()->after('empresa_id');
            $table->foreign('classificacao_fiscal_id')->references('id')->on('classificacoes_fiscais')->nullOnDelete();
        });

        // 3. Regras Tributárias
        Schema::create('regras_tributarias', function (Blueprint $table) {
            $table->id();
            $table->string('tributo', 20);
            $table->string('tipo_operacao', 50);
            $table->decimal('aliquota', 8, 4);
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->boolean('gera_credito')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // 4. Operações Fiscais
        Schema::create('operacoes_fiscais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('locacao_id')->nullable(); // Maps to reserva_id
            $table->unsignedBigInteger('fatura_id')->nullable();
            $table->string('tipo', 50);
            $table->dateTime('data_fato_gerador');
            $table->decimal('valor_operacao', 15, 2);
            $table->decimal('valor_base_calculo', 15, 2);
            $table->string('status', 30);
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
        });

        // 5. Operações Fiscais Tributos
        Schema::create('operacoes_fiscais_tributos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operacao_fiscal_id');
            $table->string('tributo', 20);
            $table->string('cst', 10)->nullable();
            $table->decimal('base_calculo', 15, 2);
            $table->decimal('aliquota', 10, 4);
            $table->decimal('valor', 15, 2);
            $table->timestamps();

            $table->foreign('operacao_fiscal_id')->references('id')->on('operacoes_fiscais')->cascadeOnDelete();
        });

        // 6. Documentos Fiscais
        Schema::create('documentos_fiscais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('operacao_fiscal_id');
            $table->string('tipo', 20); // e.g. DPS, NFSE
            $table->string('numero_dps', 50)->nullable();
            $table->string('numero_nfse', 50)->nullable();
            $table->string('chave_acesso', 100)->nullable();
            $table->string('codigo_tributacao', 20);
            $table->string('status', 30);
            $table->longText('xml')->nullable();
            $table->longText('xml_retorno')->nullable();
            $table->string('protocolo', 100)->nullable();
            $table->string('erro_codigo', 50)->nullable();
            $table->text('erro_mensagem')->nullable();
            $table->dateTime('emitida_em')->nullable();
            $table->dateTime('cancelada_em')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->foreign('operacao_fiscal_id')->references('id')->on('operacoes_fiscais')->cascadeOnDelete();
        });

        // 7. Créditos Tributários
        Schema::create('creditos_tributarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('documento_fiscal_entrada_id')->nullable();
            $table->string('tributo', 20);
            $table->decimal('valor_original', 15, 2);
            $table->decimal('valor_utilizado', 15, 2)->default(0.00);
            $table->decimal('saldo', 15, 2);
            $table->string('origem', 100);
            $table->dateTime('data_apropriacao');
            $table->string('status', 30);
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
        });

        // 8. Add configuration fields to Empresas
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('layout_nfse', 20)->default('RTC')->after('nfse_ambiente');
            $table->string('versao_layout', 20)->default('1.04.00')->after('layout_nfse');
        });

        // 9. Add split payment fields to Transacoes
        Schema::table('transacoes', function (Blueprint $table) {
            $table->decimal('valor_bruto', 15, 2)->nullable()->after('valor');
            $table->decimal('valor_ibs', 15, 2)->nullable()->after('valor_bruto');
            $table->decimal('valor_cbs', 15, 2)->nullable()->after('valor_ibs');
            $table->decimal('valor_retido', 15, 2)->nullable()->after('valor_cbs');
            $table->decimal('valor_liquido', 15, 2)->nullable()->after('valor_retido');
        });

        // 10. Recebimentos Tributos
        Schema::create('recebimentos_tributos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transacao_id');
            $table->string('tributo', 20);
            $table->decimal('valor', 15, 2);
            $table->timestamps();

            $table->foreign('transacao_id')->references('id')->on('transacoes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recebimentos_tributos');

        Schema::table('transacoes', function (Blueprint $table) {
            $table->dropColumn(['valor_bruto', 'valor_ibs', 'valor_cbs', 'valor_retido', 'valor_liquido']);
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['layout_nfse', 'versao_layout']);
        });

        Schema::dropIfExists('creditos_tributarios');
        Schema::dropIfExists('documentos_fiscais');
        Schema::dropIfExists('operacoes_fiscais_tributos');
        Schema::dropIfExists('operacoes_fiscais');
        Schema::dropIfExists('regras_tributarias');

        Schema::table('veiculos', function (Blueprint $table) {
            $table->dropForeign(['classificacao_fiscal_id']);
            $table->dropColumn('classificacao_fiscal_id');
        });

        Schema::dropIfExists('classificacoes_fiscais');
    }
};
