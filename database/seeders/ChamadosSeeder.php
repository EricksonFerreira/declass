<?php

namespace Database\Seeders;

use App\Models\ChamadoCategoria;
use App\Models\ChamadoStatus;
use App\Models\ChamadoSubcategoria;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class ChamadosSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::query()->pluck('id');

        foreach ($empresas as $empresaId) {
            $this->criarStatusPadrao((int) $empresaId);
            $this->criarCategoriasESubcategorias((int) $empresaId);
        }

        $this->command?->info('ChamadosSeeder concluido.');
    }

    private function criarStatusPadrao(int $empresaId): void
    {
        $status = [
            ['slug' => 'aguardando_analise', 'nome' => 'Aguardando análise', 'cor' => 'warning'],
            ['slug' => 'em_atendimento', 'nome' => 'Em atendimento', 'cor' => 'info'],
            ['slug' => 'cancelado', 'nome' => 'Cancelado', 'cor' => 'danger'],
            ['slug' => 'concluido', 'nome' => 'Concluído', 'cor' => 'success'],
        ];

        foreach ($status as $item) {
            ChamadoStatus::firstOrCreate(
                ['empresa_id' => $empresaId, 'slug' => $item['slug']],
                [
                    'nome' => $item['nome'],
                    'cor' => $item['cor'],
                    'ativo' => true,
                ]
            );
        }
    }

    private function criarCategoriasESubcategorias(int $empresaId): void
    {
        $categorias = [
            'Financeiro' => [
                ['nome' => 'Cobrança', 'prazo_horas' => 4],
                ['nome' => 'Pagamento em duplicidade', 'prazo_horas' => 4],
                ['nome' => 'Baixa manual', 'prazo_horas' => 8],
            ],
            'Reserva' => [
                ['nome' => 'Dúvida sobre reserva', 'prazo_horas' => 8],
                ['nome' => 'Alteração de dados', 'prazo_horas' => 8],
                ['nome' => 'Cancelamento', 'prazo_horas' => 4],
            ],
            'Operacional' => [
                ['nome' => 'Problema no check-in', 'prazo_horas' => 4],
                ['nome' => 'Problema no check-out', 'prazo_horas' => 4],
                ['nome' => 'Veículo indisponível', 'prazo_horas' => 2],
            ],
            'Aplicativo' => [
                ['nome' => 'Erro de acesso', 'prazo_horas' => 4],
                ['nome' => 'Falha de carregamento', 'prazo_horas' => 4],
                ['nome' => 'Problema de sincronização', 'prazo_horas' => 6],
            ],
        ];

        foreach ($categorias as $nomeCategoria => $subcategorias) {
            $categoria = ChamadoCategoria::firstOrCreate(
                ['empresa_id' => $empresaId, 'nome' => $nomeCategoria],
                [
                    'descricao' => null,
                    'ativo' => true,
                ]
            );

            foreach ($subcategorias as $subcategoria) {
                ChamadoSubcategoria::firstOrCreate(
                    [
                        'empresa_id' => $empresaId,
                        'chamado_categoria_id' => $categoria->id,
                        'nome' => $subcategoria['nome'],
                    ],
                    [
                        'descricao' => null,
                        'prazo_horas' => $subcategoria['prazo_horas'],
                        'ativo' => true,
                    ]
                );
            }
        }
    }
}
