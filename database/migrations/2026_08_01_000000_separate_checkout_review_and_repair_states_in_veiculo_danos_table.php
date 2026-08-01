<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('veiculo_danos')) {
            return;
        }

        Schema::table('veiculo_danos', function (Blueprint $table) {
            if (! Schema::hasColumn('veiculo_danos', 'status_revisao')) {
                $table->string('status_revisao', 32)->nullable()->after('status');
            }

            if (! Schema::hasColumn('veiculo_danos', 'status_reparo')) {
                $table->string('status_reparo', 32)->default('pendente')->after('status_revisao');
            }

            if (! Schema::hasColumn('veiculo_danos', 'decisao_revisada_em')) {
                $table->dateTime('decisao_revisada_em')->nullable()->after('status_reparo');
            }

            if (! Schema::hasColumn('veiculo_danos', 'decisao_revisada_por')) {
                $table->unsignedBigInteger('decisao_revisada_por')->nullable()->after('decisao_revisada_em');
            }

            if (! Schema::hasColumn('veiculo_danos', 'justificativa_decisao')) {
                $table->text('justificativa_decisao')->nullable()->after('decisao_revisada_por');
            }

            if (! Schema::hasColumn('veiculo_danos', 'nao_requer_reparo_em')) {
                $table->dateTime('nao_requer_reparo_em')->nullable()->after('justificativa_decisao');
            }

            if (! Schema::hasColumn('veiculo_danos', 'nao_requer_reparo_por')) {
                $table->unsignedBigInteger('nao_requer_reparo_por')->nullable()->after('nao_requer_reparo_em');
            }

            if (! Schema::hasColumn('veiculo_danos', 'nao_requer_reparo_justificativa')) {
                $table->text('nao_requer_reparo_justificativa')->nullable()->after('nao_requer_reparo_por');
            }

            if (! Schema::hasColumn('veiculo_danos', 'ordem_servico_id')) {
                $table->unsignedBigInteger('ordem_servico_id')->nullable()->after('nao_requer_reparo_justificativa');
            }
        });

        DB::table('veiculo_danos')
            ->whereNull('status_revisao')
            ->update([
                'status_revisao' => DB::raw("CASE
                    WHEN status = 'descartada' THEN 'nao_cobrar'
                    WHEN status = 'corrigida' THEN 'cobrar_cliente'
                    WHEN status = 'parcialmente_corrigida' THEN 'cobrar_cliente'
                    ELSE 'pendente'
                END"),
            ]);

        DB::table('veiculo_danos')
            ->whereNull('status_reparo')
            ->update([
                'status_reparo' => DB::raw("CASE
                    WHEN status = 'corrigida' THEN 'corrigida'
                    WHEN status = 'parcialmente_corrigida' THEN 'corrigida'
                    WHEN status = 'descartada' THEN 'nao_requer_reparo'
                    ELSE 'pendente'
                END"),
            ]);

        DB::table('veiculo_danos')
            ->whereNotNull('correcao_data')
            ->whereNull('decisao_revisada_em')
            ->update([
                'decisao_revisada_em' => DB::raw('correcao_data'),
                'decisao_revisada_por' => DB::raw('correcao_user_id'),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('veiculo_danos')) {
            return;
        }

        Schema::table('veiculo_danos', function (Blueprint $table) {
            foreach ([
                'ordem_servico_id',
                'nao_requer_reparo_justificativa',
                'nao_requer_reparo_por',
                'nao_requer_reparo_em',
                'justificativa_decisao',
                'decisao_revisada_por',
                'decisao_revisada_em',
                'status_reparo',
                'status_revisao',
            ] as $column) {
                if (Schema::hasColumn('veiculo_danos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
