<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('estacoes')) {
            Schema::table('estacoes', function (Blueprint $table) {
                if (!Schema::hasColumn('estacoes', 'nfse_municipio_codigo')) {
                    $table->string('nfse_municipio_codigo', 10)->nullable()->after('cidade');
                }
                if (!Schema::hasColumn('estacoes', 'nfse_inscricao_municipal')) {
                    $table->string('nfse_inscricao_municipal', 30)->nullable()->after('nfse_municipio_codigo');
                }
            });
        }

        if (!Schema::hasTable('tributos_municipais')) {
            Schema::create('tributos_municipais', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id');
                $table->string('municipio_codigo', 10); // IBGE
                $table->string('codigo_servico', 20);   // cTribNac
                $table->decimal('aliquota_iss', 5, 2);
                $table->unsignedTinyInteger('tributacao_issqn')->nullable();
                $table->string('inscricao_municipal', 30)->nullable();
                $table->string('cnae', 20)->nullable();
                $table->text('descricao_servico')->nullable();
                $table->char('ativo', 1)->default('S');
                $table->timestamps();

                $table->unique(['empresa_id', 'municipio_codigo', 'codigo_servico'], 'tributos_municipais_emp_mun_serv_unique');
                $table->index(['empresa_id', 'municipio_codigo'], 'tributos_municipais_emp_mun_index');
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('tributos_municipais')) {
            Schema::drop('tributos_municipais');
        }

        if (Schema::hasTable('estacoes')) {
            Schema::table('estacoes', function (Blueprint $table) {
                foreach (['nfse_inscricao_municipal', 'nfse_municipio_codigo'] as $col) {
                    if (Schema::hasColumn('estacoes', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::enableForeignKeyConstraints();
    }
};

