<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmpresaPadraoOpcionaisEDanosSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = $this->empresaPadraoId();
        if (! $empresaId) {
            $this->command?->warn('Nao foi possivel localizar a empresa padrao (slug=empresa-padrao).');
            return;
        }

        $this->seedOpcionais((int) $empresaId);
        $this->seedDanos((int) $empresaId);
    }

    private function empresaPadraoId(): ?int
    {
        if (! Schema::hasTable('empresas')) {
            return null;
        }

        $id = DB::table('empresas')->where('slug', 'empresa-padrao')->value('id');
        if (! $id) {
            return null;
        }

        return (int) $id;
    }

    private function seedOpcionais(int $empresaId): void
    {
        if (! Schema::hasTable('tipo_opcional') || ! Schema::hasTable('opcionais')) {
            $this->command?->warn('Tabelas de opcionais nao encontradas (tipo_opcional/opcionais). Pulando seed de opcionais.');
            return;
        }

        $tipoCols = $this->columns('tipo_opcional');
        $opCols = $this->columns('opcionais');

        $tipos = [
            'Protecoes',
            'Condutores',
            'Acessorios',
            'Servicos',
        ];

        $tipoIds = [];
        foreach ($tipos as $nome) {
            $tipoIds[$nome] = $this->upsertAndGetId(
                'tipo_opcional',
                $this->filterColumns('tipo_opcional', [
                    'empresa_id' => $empresaId,
                    'nome' => $nome,
                ], false),
                $this->filterColumns('tipo_opcional', [
                    'ativo' => 'S',
                    'updated_at' => now(),
                ], true)
            );
        }

        $opcionais = [
            [
                'tipo' => 'Condutores',
                'nome' => 'Condutor adicional',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 29.90,
                'descricao' => 'Permite incluir outro motorista no contrato (normalmente no balcao/retirada) e estender protecoes/seguros ao condutor adicional.',
            ],
            [
                'tipo' => 'Acessorios',
                'nome' => 'Cadeirinha infantil',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 25.00,
                'descricao' => 'Cadeirinha para criancas (sujeito a disponibilidade).',
            ],
            [
                'tipo' => 'Acessorios',
                'nome' => 'Assento elevatorio',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 20.00,
                'descricao' => 'Assento elevatorio para criancas (sujeito a disponibilidade).',
            ],
            [
                'tipo' => 'Acessorios',
                'nome' => 'Bebe conforto',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 30.00,
                'descricao' => 'Bebe conforto para recem-nascidos (sujeito a disponibilidade).',
            ],
            [
                'tipo' => 'Acessorios',
                'nome' => 'GPS',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 19.90,
                'descricao' => 'Navegacao por GPS (quando disponivel).',
            ],
            [
                'tipo' => 'Acessorios',
                'nome' => 'Wi-Fi 4G',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 24.90,
                'descricao' => 'Roteador/Plano de internet para viagem (quando disponivel).',
            ],
            [
                'tipo' => 'Servicos',
                'nome' => 'Tag de pedagio (Sem Parar/ConectCar)',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 9.90,
                'descricao' => 'Facilita passagem em pedagios/estacionamentos. Custos de uso cobrados a parte.',
            ],
            [
                'tipo' => 'Protecoes',
                'nome' => 'Protecao basica (colisao/roubo)',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 39.90,
                'descricao' => 'Cobertura basica para danos ao veiculo e roubo/furto (conforme regras do contrato).',
            ],
            [
                'tipo' => 'Protecoes',
                'nome' => 'Protecao total (franquia reduzida)',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 69.90,
                'descricao' => 'Cobertura ampliada com reducao de franquia (conforme regras do contrato).',
            ],
            [
                'tipo' => 'Protecoes',
                'nome' => 'Protecao vidros e pneus',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 14.90,
                'descricao' => 'Cobertura para incidentes comuns em vidros e pneus (conforme regras do contrato).',
            ],
            [
                'tipo' => 'Servicos',
                'nome' => 'Assistencia 24h',
                'tipo_cobranca' => 'por_dia',
                'preco_por_dia' => 12.90,
                'descricao' => 'Assistencia em estrada (reboque/pane) conforme regras do contrato.',
            ],
            [
                'tipo' => 'Servicos',
                'nome' => 'Lavagem do veiculo',
                'tipo_cobranca' => 'valor_integral',
                'preco_integral' => 79.90,
                'descricao' => 'Lavagem/limpeza interna e externa (quando aplicavel).',
            ],
            [
                'tipo' => 'Servicos',
                'nome' => 'Tanque cheio (servico de abastecimento)',
                'tipo_cobranca' => 'valor_integral',
                'preco_integral' => 120.00,
                'descricao' => 'Servico de combustivel/abastecimento conforme politica da empresa.',
            ],
            [
                'tipo' => 'Servicos',
                'nome' => 'Entrega e retirada fora da agencia',
                'tipo_cobranca' => 'valor_integral',
                'preco_integral' => 150.00,
                'descricao' => 'Entrega/retirada em local combinado (quando disponivel).',
            ],
        ];

        $opIds = [];
        foreach ($opcionais as $op) {
            $tipoNome = (string) $op['tipo'];
            $tipoId = (int) ($tipoIds[$tipoNome] ?? 0);

            $row = [
                'empresa_id' => $empresaId,
                'tipo_opcional_id' => $tipoId > 0 ? $tipoId : null,
                'nome' => $op['nome'],
                'tipo_cobranca' => $op['tipo_cobranca'] ?? null,
                'preco_por_dia' => $op['preco_por_dia'] ?? null,
                'preco_integral' => $op['preco_integral'] ?? null,
                'descricao' => $op['descricao'] ?? null,
                'ativo' => 'S',
                'updated_at' => now(),
            ];

            $where = [
                'nome' => $op['nome'],
            ];
            if (in_array('empresa_id', $opCols, true)) {
                $where['empresa_id'] = $empresaId;
            }

            $id = $this->upsertAndGetId('opcionais', $this->filterColumns('opcionais', $where, false), $this->filterColumns('opcionais', $row, true));
            $opIds[] = $id;
        }

        // Vincula opcionais a todos os grupos de frota da empresa padrao (quando a tabela existir).
        if (Schema::hasTable('grupos_frota') && Schema::hasTable('grupo_frota_opcional')) {
            $pivotCols = $this->columns('grupo_frota_opcional');
            $grupoIds = DB::table('grupos_frota')
                ->when(in_array('empresa_id', $this->columns('grupos_frota'), true), fn ($q) => $q->where('empresa_id', $empresaId))
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->values()
                ->all();

            foreach ($grupoIds as $grupoId) {
                foreach ($opIds as $opId) {
                    $where = [
                        'grupo_frota_id' => $grupoId,
                        'opcional_id' => $opId,
                    ];
                    if (in_array('empresa_id', $pivotCols, true)) {
                        $where['empresa_id'] = $empresaId;
                    }
                    DB::table('grupo_frota_opcional')->updateOrInsert(
                        $this->filterColumns('grupo_frota_opcional', $where, false),
                        $this->filterColumns('grupo_frota_opcional', array_merge($where, [
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]), true)
                    );
                }
            }
        }

        $this->command?->info('Seed de opcionais concluido para empresa-padrao.');
    }

    private function seedDanos(int $empresaId): void
    {
        if (! Schema::hasTable('categoria_danos') || ! Schema::hasTable('danos')) {
            $this->command?->warn('Tabelas de danos nao encontradas (categoria_danos/danos). Pulando seed de danos.');
            return;
        }

        $catCols = $this->columns('categoria_danos');
        $dCols = $this->columns('danos');

        $categorias = [
            'Lataria e pintura' => [
                ['nome' => 'Parachoque dianteiro', 'loc' => 'exterior'],
                ['nome' => 'Parachoque traseiro', 'loc' => 'exterior'],
                ['nome' => 'Porta', 'loc' => 'exterior'],
                ['nome' => 'Capo', 'loc' => 'exterior'],
                ['nome' => 'Teto', 'loc' => 'exterior'],
                ['nome' => 'Paralama', 'loc' => 'exterior'],
                ['nome' => 'Risco na pintura', 'loc' => 'exterior'],
                ['nome' => 'Amassado', 'loc' => 'exterior'],
            ],
            'Vidros e espelhos' => [
                ['nome' => 'Parabrisa trincado', 'loc' => 'exterior'],
                ['nome' => 'Vidro lateral', 'loc' => 'exterior'],
                ['nome' => 'Vidro traseiro', 'loc' => 'exterior'],
                ['nome' => 'Retrovisor quebrado', 'loc' => 'exterior'],
            ],
            'Iluminacao' => [
                ['nome' => 'Farol baixo', 'loc' => 'exterior'],
                ['nome' => 'Farol alto', 'loc' => 'exterior'],
                ['nome' => 'Lanterna traseira', 'loc' => 'exterior'],
                ['nome' => 'Pisca/Seta', 'loc' => 'exterior'],
                ['nome' => 'Luz de freio', 'loc' => 'exterior'],
                ['nome' => 'Luz de re', 'loc' => 'exterior'],
                ['nome' => 'Farol de neblina', 'loc' => 'exterior'],
            ],
            'Rodas e pneus' => [
                ['nome' => 'Pneu furado', 'loc' => 'exterior'],
                ['nome' => 'Pneu careca', 'loc' => 'exterior'],
                ['nome' => 'Roda amassada', 'loc' => 'exterior'],
                ['nome' => 'Calota danificada/ausente', 'loc' => 'exterior'],
                ['nome' => 'Estepe ausente', 'loc' => 'exterior'],
            ],
            'Interior' => [
                ['nome' => 'Banco rasgado', 'loc' => 'interior'],
                ['nome' => 'Banco manchado', 'loc' => 'interior'],
                ['nome' => 'Carpete sujo/molhado', 'loc' => 'interior'],
                ['nome' => 'Painel danificado', 'loc' => 'interior'],
                ['nome' => 'Cheiro de cigarro', 'loc' => 'interior'],
            ],
            'Acessorios e itens' => [
                ['nome' => 'Tapetes ausentes', 'loc' => 'interior'],
                ['nome' => 'Triangulo ausente', 'loc' => 'interior'],
                ['nome' => 'Macaco ausente', 'loc' => 'interior'],
                ['nome' => 'Chave de roda ausente', 'loc' => 'interior'],
                ['nome' => 'Extintor ausente', 'loc' => 'interior'],
                ['nome' => 'Antena ausente/quebrada', 'loc' => 'exterior'],
            ],
            'Limpadores e palhetas' => [
                ['nome' => 'Palheta dianteira gasta', 'loc' => 'exterior'],
                ['nome' => 'Palheta traseira gasta', 'loc' => 'exterior'],
            ],
        ];

        foreach ($categorias as $catNome => $danos) {
            $catWhere = ['nome' => $catNome];
            if (in_array('empresa_id', $catCols, true)) {
                $catWhere['empresa_id'] = $empresaId;
            }

            $catId = $this->upsertAndGetId(
                'categoria_danos',
                $this->filterColumns('categoria_danos', $catWhere, false),
                $this->filterColumns('categoria_danos', array_merge($catWhere, [
                    'ativo' => 'S',
                    'updated_at' => now(),
                ]))
            );

            foreach ($danos as $d) {
                $where = ['nome' => $d['nome']];
                if (in_array('empresa_id', $dCols, true)) {
                    $where['empresa_id'] = $empresaId;
                }

                $row = [
                    'empresa_id' => $empresaId,
                    'nome' => $d['nome'],
                    'categoria_id' => $catId,
                    'localizacao' => $d['loc'],
                    'ativo' => 'S',
                    'updated_at' => now(),
                ];

                $this->upsertAndGetId('danos', $this->filterColumns('danos', $where, false), $this->filterColumns('danos', $row, true));
            }
        }

        $this->command?->info('Seed de categoria_danos e danos concluido para empresa-padrao.');
    }

    /**
     * @return string[]
     */
    private function columns(string $table): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return Schema::getColumnListing($table);
    }

    private function filterColumns(string $table, array $data, bool $withTimestamps = true): array
    {
        $cols = $this->columns($table);
        if (empty($cols)) {
            return $data;
        }

        $out = [];
        foreach ($data as $k => $v) {
            if (in_array($k, $cols, true)) {
                $out[$k] = $v;
            }
        }

        if ($withTimestamps) {
            if (in_array('created_at', $cols, true) && ! array_key_exists('created_at', $out)) {
                $out['created_at'] = now();
            }
            if (in_array('updated_at', $cols, true) && ! array_key_exists('updated_at', $out)) {
                $out['updated_at'] = now();
            }
        }

        return $out;
    }

    private function upsertAndGetId(string $table, array $where, array $data): int
    {
        $query = DB::table($table);
        foreach ($where as $k => $v) {
            $query->where($k, $v);
        }
        $existing = $query->first();

        if ($existing && isset($existing->id)) {
            DB::table($table)->where('id', (int) $existing->id)->update($data);
            return (int) $existing->id;
        }

        $id = DB::table($table)->insertGetId(array_merge($where, $data));
        return (int) $id;
    }
}
