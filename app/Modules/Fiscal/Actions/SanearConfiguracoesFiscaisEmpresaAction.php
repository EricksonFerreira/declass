<?php

namespace App\Modules\Fiscal\Actions;

use App\Models\Empresa;
use App\Models\CreditoTributario;
use App\Models\ClassificacaoFiscal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SanearConfiguracoesFiscaisEmpresaAction
{
    public function handle(int $empresaId): void
    {
        $empresa = Empresa::findOrFail($empresaId);

        // 1. Seed Main NCM Classifications (Classificações Fiscais)
        $ncms = [
            [
                'descricao' => 'Veículos de passageiros flex (cilindrada > 1000cm3 e <= 1500cm3)',
                'ncm' => '87032210',
                'tipo_bem' => 'Veículo Terrestre (Hatch/Sedan)',
            ],
            [
                'descricao' => 'Veículos utilitários/SUVs (cilindrada > 1500cm3 e <= 3000cm3)',
                'ncm' => '87032310',
                'tipo_bem' => 'Veículo Terrestre (SUV/Médio)',
            ],
            [
                'descricao' => 'Veículos de transporte coletivo (Vans/Minibus)',
                'ncm' => '87021000',
                'tipo_bem' => 'Veículo Terrestre (Vans)',
            ],
            [
                'descricao' => 'Peças e Acessórios para Frota (Manutenção)',
                'ncm' => '87082999',
                'tipo_bem' => 'Peças/Insumos',
            ],
        ];

        foreach ($ncms as $ncmData) {
            ClassificacaoFiscal::firstOrCreate(
                ['ncm' => $ncmData['ncm']],
                $ncmData
            );
        }

        // 2. Update company default fiscal configurations
        $empresa->update([
            'nfse_ativo' => 'S',
            'nfse_provedor' => $empresa->nfse_provedor ?: 'nuvemfiscal',
            'nfse_ambiente' => $empresa->nfse_ambiente ?: 'homologacao',
            'layout_nfse' => $empresa->layout_nfse ?: 'RTC',
            'versao_layout' => $empresa->versao_layout ?: '1.04.00',
            'nfse_municipio_codigo' => $empresa->nfse_municipio_codigo ?: '4314902',
            'nfse_codigo_servico' => $empresa->nfse_codigo_servico ?: '99.04.01',
            'nfse_aliquota' => $empresa->nfse_aliquota ?: 5.00,
            'nfse_tributacao_issqn' => $empresa->nfse_tributacao_issqn ?: 1,
        ]);

        // 3. Seed Municipal Settings if not exist
        if (Schema::hasTable('tributos_municipais')) {
            $hasMunicipal = DB::table('tributos_municipais')
                ->where('empresa_id', $empresa->id)
                ->where('municipio_codigo', $empresa->nfse_municipio_codigo)
                ->where('codigo_servico', '99.04.01')
                ->exists();

            if (!$hasMunicipal) {
                DB::table('tributos_municipais')->insert([
                    'empresa_id' => $empresa->id,
                    'municipio_codigo' => $empresa->nfse_municipio_codigo,
                    'codigo_servico' => '99.04.01',
                    'aliquota_iss' => 5.00,
                    'tributacao_issqn' => 1,
                    'inscricao_municipal' => $empresa->nfse_inscricao_municipal ?: '999999-9',
                    'cnae' => '7711000',
                    'descricao_servico' => 'Locacao de Bens Moveis - Veiculo Automotor',
                    'ativo' => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Seed mock Credits if company has none
        $hasCredits = CreditoTributario::where('empresa_id', $empresa->id)->exists();
        if (!$hasCredits) {
            // CBS Credit
            CreditoTributario::create([
                'empresa_id' => $empresa->id,
                'tributo' => 'CBS',
                'valor_original' => 15000.00,
                'valor_utilizado' => 0.00,
                'saldo' => 15000.00,
                'origem' => 'NF-e nº 4509 - Aquisição de frota Chevrolet Onix (Saneamento)',
                'data_apropriacao' => now(),
                'status' => 'DISPONIVEL',
            ]);

            // IBS Credit
            CreditoTributario::create([
                'empresa_id' => $empresa->id,
                'tributo' => 'IBS',
                'valor_original' => 30000.00,
                'valor_utilizado' => 0.00,
                'saldo' => 30000.00,
                'origem' => 'NF-e nº 4509 - Aquisição de frota Chevrolet Onix (Saneamento)',
                'data_apropriacao' => now(),
                'status' => 'DISPONIVEL',
            ]);
        }
    }
}
