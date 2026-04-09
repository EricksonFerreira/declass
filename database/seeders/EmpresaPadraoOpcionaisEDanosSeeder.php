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
                ['nome' => 'Parachoque dianteiro amassado ou riscado', 'loc' => 'exterior'],
                ['nome' => 'Parachoque traseiro amassado ou riscado', 'loc' => 'exterior'],
                ['nome' => 'Porta dianteira esquerda amassada/riscada', 'loc' => 'exterior'],
                ['nome' => 'Porta dianteira direita amassada/riscada', 'loc' => 'exterior'],
                ['nome' => 'Porta traseira esquerda amassada/riscada', 'loc' => 'exterior'],
                ['nome' => 'Porta traseira direita amassada/riscada', 'loc' => 'exterior'],
                ['nome' => 'Capo amassado ou riscado', 'loc' => 'exterior'],
                ['nome' => 'Teto externo amassado', 'loc' => 'exterior'],
                ['nome' => 'Paralama esquerdo amassado/riscado', 'loc' => 'exterior'],
                ['nome' => 'Paralama direito amassado/riscado', 'loc' => 'exterior'],
                ['nome' => 'Tampa do porta-malas amassada', 'loc' => 'exterior'],
                ['nome' => 'Risco superficial na pintura', 'loc' => 'exterior'],
                ['nome' => 'Risco profundo (atingiu primer)', 'loc' => 'exterior'],
                ['nome' => 'Pintura queimada ou manchada', 'loc' => 'exterior'],
                ['nome' => 'Grade frontal quebrada', 'loc' => 'exterior'],
                ['nome' => 'Macaneta externa riscada/quebrada', 'loc' => 'exterior'],
                ['nome' => 'Soleira das portas riscada (interna)', 'loc' => 'interior'],
                ['nome' => 'Lataria interna do porta-malas amassada', 'loc' => 'interior'],
                ['nome' => 'Pintura interna da porta riscada/manchada', 'loc' => 'interior'],
                ['nome' => 'Coluna interna (A/B/C) amassada ou riscada', 'loc' => 'interior'],
                ['nome' => 'Assoalho danificado/furado', 'loc' => 'interior'],
            ],
            'Vidros e espelhos' => [
                ['nome' => 'Parabrisa trincado', 'loc' => 'exterior'],
                ['nome' => 'Parabrisa quebrado/estilhacado', 'loc' => 'exterior'],
                ['nome' => 'Vidro lateral dianteiro esquerdo', 'loc' => 'exterior'],
                ['nome' => 'Vidro lateral dianteiro direito', 'loc' => 'exterior'],
                ['nome' => 'Vidro lateral traseiro esquerdo', 'loc' => 'exterior'],
                ['nome' => 'Vidro lateral traseiro direito', 'loc' => 'exterior'],
                ['nome' => 'Vidro traseiro (vigia) trincado', 'loc' => 'exterior'],
                ['nome' => 'Retrovisor externo esquerdo quebrado', 'loc' => 'exterior'],
                ['nome' => 'Retrovisor externo direito quebrado', 'loc' => 'exterior'],
                ['nome' => 'Capa do retrovisor riscada ou ausente', 'loc' => 'exterior'],
                ['nome' => 'Teto solar trincado/quebrado', 'loc' => 'exterior'],
                ['nome' => 'Retrovisor interno central quebrado', 'loc' => 'interior'],
                ['nome' => 'Espelho do quebra-sol quebrado/ausente', 'loc' => 'interior'],
                ['nome' => 'Pelicula (Insulfilm) rasgada ou com bolhas', 'loc' => 'interior'],
                ['nome' => 'Desembacador traseiro interno danificado', 'loc' => 'interior'],
            ],
            'Iluminacao' => [
                ['nome' => 'Farol principal esquerdo quebrado', 'loc' => 'exterior'],
                ['nome' => 'Farol principal direito quebrado', 'loc' => 'exterior'],
                ['nome' => 'Lanterna traseira esquerda quebrada', 'loc' => 'exterior'],
                ['nome' => 'Lanterna traseira direita quebrada', 'loc' => 'exterior'],
                ['nome' => 'Pisca/Seta dianteiro esquerdo', 'loc' => 'exterior'],
                ['nome' => 'Pisca/Seta dianteiro direito', 'loc' => 'exterior'],
                ['nome' => 'Pisca/Seta lateral retrovisor quebrado', 'loc' => 'exterior'],
                ['nome' => 'Farol de neblina (milha) quebrado', 'loc' => 'exterior'],
                ['nome' => 'Luz de placa queimada/quebrada', 'loc' => 'exterior'],
                ['nome' => 'Brake light externo (terceira luz de freio)', 'loc' => 'exterior'],
                ['nome' => 'Luz de teto central interna quebrada/queimada', 'loc' => 'interior'],
                ['nome' => 'Luz de leitura do motorista/passageiro', 'loc' => 'interior'],
                ['nome' => 'Luz interna do porta-luvas queimada', 'loc' => 'interior'],
                ['nome' => 'Luz interna do porta-malas apagada', 'loc' => 'interior'],
                ['nome' => 'Luz de cortesia das portas vazada/quebrada', 'loc' => 'interior'],
                ['nome' => 'Iluminacao do painel de instrumentos apagada', 'loc' => 'interior'],
            ],
            'Rodas e pneus' => [
                ['nome' => 'Pneu dianteiro esquerdo furado/rasgado', 'loc' => 'exterior'],
                ['nome' => 'Pneu dianteiro direito furado/rasgado', 'loc' => 'exterior'],
                ['nome' => 'Pneu traseiro esquerdo furado/rasgado', 'loc' => 'exterior'],
                ['nome' => 'Pneu traseiro direito furado/rasgado', 'loc' => 'exterior'],
                ['nome' => 'Pneu careca (abaixo do limite TWI)', 'loc' => 'exterior'],
                ['nome' => 'Bolha no pneu ou avaria no ombro', 'loc' => 'exterior'],
                ['nome' => 'Roda de liga leve arranhada/ralada', 'loc' => 'exterior'],
                ['nome' => 'Roda de ferro amassada/torta', 'loc' => 'exterior'],
                ['nome' => 'Calota quebrada, riscada ou ausente', 'loc' => 'exterior'],
                ['nome' => 'Bico (valvula) do pneu quebrado', 'loc' => 'exterior'],
                ['nome' => 'Parafuso ou porca da roda faltando', 'loc' => 'exterior'],
                ['nome' => 'Estepe furado ou rasgado', 'loc' => 'interior'],
                ['nome' => 'Estepe careca', 'loc' => 'interior'],
                ['nome' => 'Roda do estepe amassada', 'loc' => 'interior'],
                ['nome' => 'Tampa do compartimento do estepe ausente/quebrada', 'loc' => 'interior'],
                ['nome' => 'Trava do estepe interna quebrada', 'loc' => 'interior'],
            ],
            'Interior' => [
                ['nome' => 'Banco do motorista rasgado ou furado', 'loc' => 'interior'],
                ['nome' => 'Banco do passageiro rasgado ou furado', 'loc' => 'interior'],
                ['nome' => 'Banco traseiro rasgado ou furado', 'loc' => 'interior'],
                ['nome' => 'Bancos manchados ou sujos', 'loc' => 'interior'],
                ['nome' => 'Carpete rasgado', 'loc' => 'interior'],
                ['nome' => 'Carpete manchado, sujo ou molhado', 'loc' => 'interior'],
                ['nome' => 'Teto interno (forro) sujo ou rasgado', 'loc' => 'interior'],
                ['nome' => 'Painel tabelier riscado, rachado ou quebrado', 'loc' => 'interior'],
                ['nome' => 'Console central quebrado ou riscado', 'loc' => 'interior'],
                ['nome' => 'Cheiro de cigarro ou odores fortes', 'loc' => 'interior'],
                ['nome' => 'Puxador de porta interna quebrado', 'loc' => 'interior'],
                ['nome' => 'Forro de porta riscado ou rasgado', 'loc' => 'interior'],
                ['nome' => 'Difusores de ar condicionado quebrados', 'loc' => 'interior'],
                ['nome' => 'Volante descascando ou rasgado', 'loc' => 'interior'],
                ['nome' => 'Manopla do cambio gasta ou solta', 'loc' => 'interior'],
                ['nome' => 'Porta-luvas quebrado (tampa, trava ou dobradica)', 'loc' => 'interior'],
                ['nome' => 'Borrachas de vedacao das portas/teto ressecadas', 'loc' => 'exterior'],
                ['nome' => 'Isolamento acustico/termico externo danificado', 'loc' => 'exterior'],
                ['nome' => 'Acabamento plastico externo dos estribos danificados', 'loc' => 'exterior'],
            ],
            'Acessorios e itens' => [
                ['nome' => 'Jogo de tapetes ausente', 'loc' => 'interior'],
                ['nome' => 'Triangulo de sinalizacao ausente ou quebrado', 'loc' => 'interior'],
                ['nome' => 'Macaco ausente ou com defeito', 'loc' => 'interior'],
                ['nome' => 'Chave de roda ausente', 'loc' => 'interior'],
                ['nome' => 'Chave reserva ou manual do proprietario ausente', 'loc' => 'interior'],
                ['nome' => 'Extintor de incendio ausente ou vencido', 'loc' => 'interior'],
                ['nome' => 'Central Multimidia ou Radio com tela quebrada', 'loc' => 'interior'],
                ['nome' => 'Cinto de seguranca cortado, desfiado ou sem trava', 'loc' => 'interior'],
                ['nome' => 'Tampao (bagagito) do porta-malas quebrado/ausente', 'loc' => 'interior'],
                ['nome' => 'Acendedor de cigarro ou tomadas USB quebrados', 'loc' => 'interior'],
                ['nome' => 'Antena de teto ausente, dobrada ou quebrada', 'loc' => 'exterior'],
                ['nome' => 'Rack de teto ou barras longitudinais quebradas', 'loc' => 'exterior'],
                ['nome' => 'Emblemas (logotipos) furtados ou danificados', 'loc' => 'exterior'],
                ['nome' => 'Calhas de chuva descoladas ou quebradas', 'loc' => 'exterior'],
                ['nome' => 'Sensor de estacionamento externo afundado', 'loc' => 'exterior'],
                ['nome' => 'Camera de re externa arranhada ou opaca', 'loc' => 'exterior'],
                ['nome' => 'Capa protetora de cacamba / lona maritima rasgada', 'loc' => 'exterior'],
            ],
            'Limpadores e palhetas' => [
                ['nome' => 'Palheta dianteira esquerda gasta/rasgada', 'loc' => 'exterior'],
                ['nome' => 'Palheta dianteira direita gasta/rasgada', 'loc' => 'exterior'],
                ['nome' => 'Palheta traseira gasta ou rasgada', 'loc' => 'exterior'],
                ['nome' => 'Braco do limpador de parabrisa torto ou oxidado', 'loc' => 'exterior'],
                ['nome' => 'Braco do limpador traseiro solto ou quebrado', 'loc' => 'exterior'],
                ['nome' => 'Brucutu (esguicho) entupido ou quebrado', 'loc' => 'exterior'],
                ['nome' => 'Alavanca do limpador na coluna de direcao quebrada', 'loc' => 'interior'],
                ['nome' => 'Motor do limpador de parabrisa inoperante (interno)', 'loc' => 'interior'],
                ['nome' => 'Reservatorio de agua do esguicho furado/vazando', 'loc' => 'interior'],
            ],
            'Mecanica e estrutural' => [
                ['nome' => 'Bateria descarregada, avariada ou furtada', 'loc' => 'interior'],
                ['nome' => 'Tampa decorativa do motor ausente ou quebrada', 'loc' => 'interior'],
                ['nome' => 'Fiacao de chicotes internos exposta ou cortada', 'loc' => 'interior'],
                ['nome' => 'Maquina de vidro eletrico traseira/dianteira inoperante', 'loc' => 'interior'],
                ['nome' => 'Caixa de fusiveis faltando a tampa', 'loc' => 'interior'],
                ['nome' => 'Vareta medidora de oleo quebrada', 'loc' => 'interior'],
                ['nome' => 'Tampa do reservatorio de expansao rachada', 'loc' => 'interior'],
                ['nome' => 'Protetor de carter amassado, caindo ou ausente', 'loc' => 'exterior'],
                ['nome' => 'Ponteira ou silencioso do escapamento amassado/furado', 'loc' => 'exterior'],
                ['nome' => 'Escapamento solto ou raspando', 'loc' => 'exterior'],
                ['nome' => 'Fundo do tanque de combustivel amassado', 'loc' => 'exterior'],
                ['nome' => 'Homocinetica estalando (coifa rasgada)', 'loc' => 'exterior'],
                ['nome' => 'Amortecedor/Suspensao com vazamento de oleo', 'loc' => 'exterior'],
                ['nome' => 'Barra de direcao ou componentes inferiores tortos', 'loc' => 'exterior'],
                ['nome' => 'Protetor plastico das molduras de caixa de roda arrancado', 'loc' => 'exterior'],
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
