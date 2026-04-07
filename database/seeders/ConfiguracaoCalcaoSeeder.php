<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cria uma configuração padrão de calção para cada empresa existente.
 * Regra: valor fixo de R$ 500,00 por empresa (sem limite de grupo/modelo).
 *
 * Para personalizar por grupo ou modelo, adicione registros adicionais
 * em configuracoes_calcao com grupo_frota_id ou modelo_id preenchidos.
 */
class ConfiguracaoCalcaoSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('configuracoes_calcao') || !Schema::hasTable('empresas')) {
            $this->command->warn('Tabelas nao encontradas. Execute as migrations primeiro.');
            return;
        }

        $empresas = DB::table('empresas')->pluck('id');

        foreach ($empresas as $empresaId) {
            $existe = DB::table('configuracoes_calcao')
                ->where('empresa_id', $empresaId)
                ->whereNull('grupo_frota_id')
                ->whereNull('modelo_id')
                ->exists();

            if ($existe) {
                continue;
            }

            DB::table('configuracoes_calcao')->insert([
                'empresa_id'       => $empresaId,
                'grupo_frota_id'   => null,
                'modelo_id'        => null,
                'tipo_regra_valor' => 'fixo',
                'valor'            => 500.00,
                'valor_minimo'     => null,
                'valor_maximo'     => null,
                'ativo'            => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $this->command->info("Configuracao de calcao padrao criada para empresa #{$empresaId}");
        }

        $this->command->info('ConfiguracaoCalcaoSeeder concluido.');
    }
}
