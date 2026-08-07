<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Empresa;
use App\Models\RegraTributaria;
use App\Models\CreditoTributario;
use App\Models\ClassificacaoFiscal;
use App\Modules\Fiscal\Enums\TipoOperacaoFiscal;

class FiscalSeeder extends Seeder
{
    public function run(): void
    {
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

        // 2. Seed Default Tax Rules (Reforma Tributária IBS/CBS)
        $regras = [
            // CBS - 2026 Trial Phase (0.9%)
            [
                'tributo' => 'CBS',
                'tipo_operacao' => TipoOperacaoFiscal::LOCACAO_VEICULO->value,
                'aliquota' => 0.9000,
                'data_inicio' => '2026-01-01',
                'data_fim' => '2026-12-31',
                'gera_credito' => false,
                'ativo' => true,
            ],
            // IBS - 2026 Trial Phase (0.1%)
            [
                'tributo' => 'IBS',
                'tipo_operacao' => TipoOperacaoFiscal::LOCACAO_VEICULO->value,
                'aliquota' => 0.1000,
                'data_inicio' => '2026-01-01',
                'data_fim' => '2026-12-31',
                'gera_credito' => false,
                'ativo' => true,
            ],
            // CBS - 2027+ Parameterized rate (estimate 8.8%)
            [
                'tributo' => 'CBS',
                'tipo_operacao' => TipoOperacaoFiscal::LOCACAO_VEICULO->value,
                'aliquota' => 8.8000,
                'data_inicio' => '2027-01-01',
                'data_fim' => null,
                'gera_credito' => true,
                'ativo' => true,
            ],
            // IBS - 2027+ Parameterized rate (estimate 17.7%)
            [
                'tributo' => 'IBS',
                'tipo_operacao' => TipoOperacaoFiscal::LOCACAO_VEICULO->value,
                'aliquota' => 17.7000,
                'data_inicio' => '2027-01-01',
                'data_fim' => null,
                'gera_credito' => true,
                'ativo' => true,
            ],
        ];

        foreach ($regras as $regra) {
            RegraTributaria::firstOrCreate(
                [
                    'tributo' => $regra['tributo'],
                    'tipo_operacao' => $regra['tipo_operacao'],
                    'data_inicio' => $regra['data_inicio']
                ],
                $regra
            );
        }

        // 3. Seed active companies
        $empresas = Empresa::all();

        if ($empresas->isEmpty()) {
            $empresa = Empresa::create([
                'nome' => 'Locadora Padrão DeCars',
                'slug' => 'locadora-padrao-decars',
                'cnpj' => '12345678000199',
                'ativo' => true,
            ]);
            $empresas->push($empresa);
        }

        foreach ($empresas as $empresa) {
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

            $hasCredits = CreditoTributario::where('empresa_id', $empresa->id)->exists();
            if (!$hasCredits) {
                CreditoTributario::create([
                    'empresa_id' => $empresa->id,
                    'tributo' => 'CBS',
                    'valor_original' => 15000.00,
                    'valor_utilizado' => 0.00,
                    'saldo' => 15000.00,
                    'origem' => 'NF-e nº 4509 - Aquisição de frota Chevrolet Onix',
                    'data_apropriacao' => now()->subDays(5),
                    'status' => 'DISPONIVEL',
                ]);
                
                CreditoTributario::create([
                    'empresa_id' => $empresa->id,
                    'tributo' => 'IBS',
                    'valor_original' => 30000.00,
                    'valor_utilizado' => 0.00,
                    'saldo' => 30000.00,
                    'origem' => 'NF-e nº 4509 - Aquisição de frota Chevrolet Onix',
                    'data_apropriacao' => now()->subDays(5),
                    'status' => 'DISPONIVEL',
                ]);
            }
        }
    }
}
