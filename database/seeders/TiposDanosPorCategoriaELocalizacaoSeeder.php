<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TiposDanosPorCategoriaELocalizacaoSeeder extends Seeder
{
    /**
     * @var array<string, array{interior: string[], exterior: string[]}>
     */
    private array $templates = [
        'Lataria e pintura' => [
            'interior' => [
                'Acabamento interno riscado ou amassado',
                'Soleira interna riscada',
                'Lataria interna do porta-malas amassada',
            ],
            'exterior' => [
                'Lataria externa amassada ou riscada',
                'Parachoque riscado ou desalinhado',
                'Pintura externa descascada ou manchada',
                'Grade frontal quebrada',
            ],
        ],
        'Vidros e espelhos' => [
            'interior' => [
                'Espelho interno quebrado ou ausente',
                'Película interna com bolhas ou rasgada',
                'Espelho do quebra-sol quebrado ou ausente',
            ],
            'exterior' => [
                'Vidro ou retrovisor externo quebrado',
                'Para-brisa trincado',
                'Vidro traseiro trincado',
                'Capa do retrovisor riscada ou ausente',
            ],
        ],
        'Iluminacao' => [
            'interior' => [
                'Luz interna queimada ou quebrada',
                'Iluminação do painel apagada',
                'Luz do porta-malas sem funcionamento',
            ],
            'exterior' => [
                'Farol ou lanterna externa quebrada',
                'Seta dianteira quebrada',
                'Farol de neblina avariado',
                'Luz de placa sem funcionamento',
            ],
        ],
        'Rodas e pneus' => [
            'interior' => [
                'Estepe ou suporte interno avariado',
                'Compartimento do estepe quebrado',
                'Trava interna do estepe danificada',
            ],
            'exterior' => [
                'Pneu ou roda externa avariada',
                'Roda ralada ou amassada',
                'Calota quebrada ou ausente',
                'Pneu com bolha, corte ou rasgo',
            ],
        ],
        'Interior' => [
            'interior' => [
                'Banco, painel ou forro danificado',
                'Carpete manchado, rasgado ou molhado',
                'Console central quebrado',
                'Volante descascando ou rasgado',
                'Cinto de segurança com defeito',
            ],
            'exterior' => [
                'Acabamento externo relacionado ao interior danificado',
                'Borracha de vedação externa ressecada ou solta',
                'Acabamento externo da soleira danificado',
            ],
        ],
        'Acessorios e itens' => [
            'interior' => [
                'Acessório interno ausente ou quebrado',
                'Tapetes ausentes',
                'Triângulo, macaco ou chave de roda ausentes',
                'Central multimídia ou rádio com defeito',
                'Porta-luvas quebrado',
            ],
            'exterior' => [
                'Acessório externo ausente ou quebrado',
                'Antena externa quebrada ou ausente',
                'Sensor de estacionamento avariado',
                'Câmera de ré externa opaca ou quebrada',
            ],
        ],
        'Limpadores e palhetas' => [
            'interior' => [
                'Comando interno do limpador avariado',
                'Motor do limpador sem funcionamento',
                'Reservatório do esguicho com vazamento',
            ],
            'exterior' => [
                'Palheta ou braço do limpador avariado',
                'Palheta ressecada ou rasgada',
                'Braço do limpador torto',
                'Esguicho externo entupido ou quebrado',
            ],
        ],
        'Mecanica e estrutural' => [
            'interior' => [
                'Componente mecânico interno avariado',
                'Bateria descarregada ou ausente',
                'Fiação interna exposta ou cortada',
                'Máquina de vidro sem funcionamento',
            ],
            'exterior' => [
                'Componente estrutural externo avariado',
                'Protetor de cárter amassado ou ausente',
                'Escapamento solto ou furado',
                'Suspensão com vazamento ou avaria',
            ],
        ],
    ];

    public function run(): void
    {
        if (!Schema::hasTable('categoria_danos') || !Schema::hasTable('danos')) {
            $this->command?->warn('Tabelas de danos não encontradas. Seeder ignorado.');
            return;
        }

        $empresas = $this->empresaIdsParaSeed();
        foreach ($empresas as $empresaId) {
            $this->seedCategoriasBase($empresaId);
            $this->seedCombosPorCategoria($empresaId);
        }

        $this->command?->info('Seeder de tipos de danos por categoria e localização concluído.');
    }

    /**
     * @return array<int|null>
     */
    private function empresaIdsParaSeed(): array
    {
        if (!Schema::hasColumn('categoria_danos', 'empresa_id') || !Schema::hasColumn('danos', 'empresa_id')) {
            return [null];
        }

        if (!Schema::hasTable('empresas')) {
            return [1];
        }

        $ids = DB::table('empresas')->pluck('id')->map(fn ($id) => (int) $id)->all();

        return empty($ids) ? [1] : $ids;
    }

    private function seedCategoriasBase(?int $empresaId): void
    {
        foreach (array_keys($this->templates) as $categoriaNome) {
            $where = ['nome' => $categoriaNome];
            if ($this->hasColumn('categoria_danos', 'empresa_id')) {
                $where['empresa_id'] = $empresaId;
            }

            $data = $where;
            if ($this->hasColumn('categoria_danos', 'ativo')) {
                $data['ativo'] = 'S';
            }

            $this->upsert('categoria_danos', $where, $data);
        }
    }

    private function seedCombosPorCategoria(?int $empresaId): void
    {
        $categorias = DB::table('categoria_danos')
            ->when($this->hasColumn('categoria_danos', 'empresa_id'), fn ($query) => $query->where('empresa_id', $empresaId))
            ->get(['id', 'nome']);

        foreach ($categorias as $categoria) {
            $template = $this->templates[$categoria->nome] ?? $this->templateGenerico((string) $categoria->nome);

            foreach (['interior', 'exterior'] as $localizacao) {
                foreach ($template[$localizacao] as $nomeDano) {
                    $where = [
                        'categoria_id' => (int) $categoria->id,
                        'localizacao' => $localizacao,
                        'nome' => $nomeDano,
                    ];

                    if ($this->hasColumn('danos', 'empresa_id')) {
                        $where['empresa_id'] = $empresaId;
                    }

                    $data = $where;
                    if ($this->hasColumn('danos', 'ativo')) {
                        $data['ativo'] = 'S';
                    }

                    $this->upsert('danos', $where, $data);
                }
            }
        }
    }

    /**
     * @return array{interior: string[], exterior: string[]}
     */
    private function templateGenerico(string $categoriaNome): array
    {
        return [
            'interior' => ['Dano interno - ' . $categoriaNome],
            'exterior' => ['Dano externo - ' . $categoriaNome],
        ];
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    private function upsert(string $table, array $where, array $data): void
    {
        $query = DB::table($table);
        foreach ($where as $column => $value) {
            if ($value === null) {
                $query->whereNull($column);
            } else {
                $query->where($column, $value);
            }
        }

        $existing = $query->first();
        $payload = $this->filterColumns($table, $data);

        if ($existing && isset($existing->id)) {
            if ($this->hasColumn($table, 'updated_at')) {
                $payload['updated_at'] = now();
            }

            DB::table($table)->where('id', (int) $existing->id)->update($payload);
            return;
        }

        if ($this->hasColumn($table, 'created_at')) {
            $payload['created_at'] = now();
        }
        if ($this->hasColumn($table, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table($table)->insert($payload);
    }

    private function filterColumns(string $table, array $data): array
    {
        $columns = Schema::getColumnListing($table);

        return array_filter(
            $data,
            fn ($value, $column) => in_array($column, $columns, true),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
