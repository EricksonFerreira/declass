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
            if (!Schema::hasColumn('empresas', 'nfse_ativo')) {
                $table->char('nfse_ativo', 1)->default('N')->after('paypal_mode');
            }
            if (!Schema::hasColumn('empresas', 'nfse_ambiente')) {
                $table->string('nfse_ambiente', 20)->nullable()->after('nfse_ativo');
            }
            if (!Schema::hasColumn('empresas', 'nfse_provedor')) {
                $table->string('nfse_provedor', 50)->nullable()->after('nfse_ambiente');
            }
            if (!Schema::hasColumn('empresas', 'nfse_client_id')) {
                $table->string('nfse_client_id', 150)->nullable()->after('nfse_provedor');
            }
            if (!Schema::hasColumn('empresas', 'nfse_client_secret')) {
                $table->text('nfse_client_secret')->nullable()->after('nfse_client_id');
            }
            if (!Schema::hasColumn('empresas', 'nfse_inscricao_municipal')) {
                $table->string('nfse_inscricao_municipal', 30)->nullable()->after('nfse_client_secret');
            }
            if (!Schema::hasColumn('empresas', 'nfse_municipio_codigo')) {
                $table->string('nfse_municipio_codigo', 10)->nullable()->after('nfse_inscricao_municipal');
            }
            if (!Schema::hasColumn('empresas', 'nfse_codigo_servico')) {
                $table->string('nfse_codigo_servico', 20)->nullable()->after('nfse_municipio_codigo');
            }
            if (!Schema::hasColumn('empresas', 'nfse_cnae')) {
                $table->string('nfse_cnae', 20)->nullable()->after('nfse_codigo_servico');
            }
            if (!Schema::hasColumn('empresas', 'nfse_descricao_servico')) {
                $table->text('nfse_descricao_servico')->nullable()->after('nfse_cnae');
            }
            if (!Schema::hasColumn('empresas', 'nfse_aliquota')) {
                $table->decimal('nfse_aliquota', 5, 2)->nullable()->after('nfse_descricao_servico');
            }
            if (!Schema::hasColumn('empresas', 'nfse_tributacao_issqn')) {
                $table->unsignedTinyInteger('nfse_tributacao_issqn')->nullable()->after('nfse_aliquota');
            }
            if (!Schema::hasColumn('empresas', 'nfse_tomador_numero_padrao')) {
                $table->string('nfse_tomador_numero_padrao', 20)->nullable()->after('nfse_tributacao_issqn');
            }
            if (!Schema::hasColumn('empresas', 'nfse_tomador_bairro_padrao')) {
                $table->string('nfse_tomador_bairro_padrao', 80)->nullable()->after('nfse_tomador_numero_padrao');
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
                'nfse_tomador_bairro_padrao',
                'nfse_tomador_numero_padrao',
                'nfse_tributacao_issqn',
                'nfse_aliquota',
                'nfse_descricao_servico',
                'nfse_cnae',
                'nfse_codigo_servico',
                'nfse_municipio_codigo',
                'nfse_inscricao_municipal',
                'nfse_client_secret',
                'nfse_client_id',
                'nfse_provedor',
                'nfse_ambiente',
                'nfse_ativo',
            ] as $column) {
                if (Schema::hasColumn('empresas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
