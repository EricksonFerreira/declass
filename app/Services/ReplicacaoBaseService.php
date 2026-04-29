<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ReplicacaoBaseService
{
    /**
     * @return array<string, array{label: string, area: string, table: string, mode: string, tables?: array<int, string>}>
     */
    public function modulosDisponiveis(): array
    {
        $modules = [
            'paises' => ['label' => 'Paises', 'area' => 'Cadastros Basicos', 'tables' => ['paises']],
            'segmentos' => ['label' => 'Segmentos', 'area' => 'Cadastros Basicos', 'tables' => ['segmentos']],
            'marcas' => ['label' => 'Marcas', 'area' => 'Cadastros Basicos', 'tables' => ['marcas']],
            'modelos' => ['label' => 'Modelos', 'area' => 'Cadastros Basicos', 'tables' => ['modelos']],
            'catalogo_fipe' => [
                'label' => 'Marca + Modelo + Segmentos',
                'area' => 'Cadastros Basicos',
                'tables' => ['segmentos', 'marcas', 'modelos'],
                'mode' => 'bundle',
            ],
            'dias_uteis' => ['label' => 'Dias Uteis', 'area' => 'Cadastros Basicos', 'tables' => ['dias_uteis']],
            'horarios_diarios' => ['label' => 'Horarios Diarios', 'area' => 'Cadastros Basicos', 'tables' => ['horarios_diarios']],
            'tipo_valor_caracteristica_frota' => ['label' => 'Tipos de Valor da Caracteristica de Frota', 'area' => 'Frota', 'tables' => ['tipo_valor_caracteristica_frota']],
            'caracteristicas_frotas' => ['label' => 'Caracteristicas de Frota', 'area' => 'Frota', 'tables' => ['caracteristicas_frotas']],
            'tipo_frota' => ['label' => 'Tipos de Frota', 'area' => 'Frota', 'tables' => ['tipo_frota']],
            'grupos_frota' => ['label' => 'Grupos de Frota', 'area' => 'Frota', 'tables' => ['grupos_frota']],
            'tipo_opcional' => ['label' => 'Tipos de Opcional', 'area' => 'Frota', 'tables' => ['tipo_opcional', 'tipo_opcionals']],
            'opcionais' => ['label' => 'Itens de Opcional', 'area' => 'Frota', 'tables' => ['opcionais', 'opcionals']],
            'opcional_grupo_veiculo' => ['label' => 'Vinculos de Opcionais por Grupo', 'area' => 'Frota', 'tables' => ['opcional_grupo_veiculo', 'opcional_grupo_veiculos']],
            'categoria_danos' => ['label' => 'Categorias de Dano', 'area' => 'Checklist e Danos', 'tables' => ['categoria_danos']],
            'danos' => ['label' => 'Itens de Dano', 'area' => 'Checklist e Danos', 'tables' => ['danos']],
            'tipo_checklist' => ['label' => 'Tipos de Checklist', 'area' => 'Checklist e Danos', 'tables' => ['tipo_checklist']],
            'status_item_checklist' => ['label' => 'Status Item Checklist', 'area' => 'Checklist e Danos', 'tables' => ['status_item_checklist']],
            'item_checklist' => ['label' => 'Itens de Checklist', 'area' => 'Checklist e Danos', 'tables' => ['item_checklist']],
            'configuracao_checklist' => ['label' => 'Configuracao de Checklist', 'area' => 'Checklist e Danos', 'tables' => ['configuracao_checklist']],
            'checklists' => ['label' => 'Checklists', 'area' => 'Checklist e Danos', 'tables' => ['checklists']],
            'tipos_movimentacao' => ['label' => 'Tipos de Movimentacao', 'area' => 'Operacao e Reservas', 'tables' => ['tipos_movimentacao']],
            'status_movimentacao' => ['label' => 'Status de Movimentacao', 'area' => 'Operacao e Reservas', 'tables' => ['status_movimentacao']],
            'status_reserva' => ['label' => 'Status de Reserva', 'area' => 'Operacao e Reservas', 'tables' => ['status_reserva']],
            'status_veiculos' => ['label' => 'Status de Veiculo', 'area' => 'Operacao e Reservas', 'tables' => ['status_veiculos']],
            'termo_consentimento' => ['label' => 'Termo de Consentimento', 'area' => 'Configuracoes do Usuario', 'tables' => ['termo_consentimento']],
            'configuracoes_calcao' => ['label' => 'Configuracoes de Calcao', 'area' => 'Operacao e Reservas', 'tables' => ['configuracoes_calcao']],
            'regras' => ['label' => 'Regras de Usuario', 'area' => 'Configuracoes do Usuario', 'tables' => ['roles']],
            'permissoes' => ['label' => 'Permissoes', 'area' => 'Configuracoes do Usuario', 'tables' => ['permissions']],
            'regras_permissoes' => [
                'label' => 'Vinculos Regra x Permissao',
                'area' => 'Configuracoes do Usuario',
                'tables' => ['role_has_permissions'],
                'mode' => 'regra_permissao',
            ],
        ];

        $disponiveis = [];

        foreach ($modules as $chave => $module) {
            $mode = (string) ($module['mode'] ?? 'table');

            if ($mode === 'regra_permissao') {
                $tabelaVinculo = $this->resolverTabelaExistente($module['tables'] ?? []);
                if (
                    $tabelaVinculo === null
                    || !Schema::hasTable('roles')
                    || !Schema::hasTable('permissions')
                    || !Schema::hasColumn('roles', 'empresa_id')
                    || !Schema::hasColumn('permissions', 'empresa_id')
                ) {
                    continue;
                }

                $disponiveis[$chave] = [
                    'label' => (string) $module['label'],
                    'area' => (string) ($module['area'] ?? 'Geral'),
                    'table' => $tabelaVinculo,
                    'mode' => $mode,
                ];
                continue;
            }

            if ($mode === 'bundle') {
                $tables = array_values(array_filter(array_map('strval', $module['tables'] ?? [])));
                $tablesExistentes = $this->resolverTabelasComEmpresaId($tables);

                if (count($tables) === 0 || count($tablesExistentes) !== count($tables)) {
                    continue;
                }

                $disponiveis[$chave] = [
                    'label' => (string) $module['label'],
                    'area' => (string) ($module['area'] ?? 'Geral'),
                    'table' => implode(' + ', $tablesExistentes),
                    'mode' => $mode,
                    'tables' => $tablesExistentes,
                ];
                continue;
            }

            $table = $this->resolverTabelaComEmpresaId($module['tables'] ?? []);
            if ($table === null) {
                continue;
            }

            $disponiveis[$chave] = [
                'label' => (string) $module['label'],
                'area' => (string) ($module['area'] ?? 'Geral'),
                'table' => $table,
                'mode' => $mode,
            ];
        }

        return $disponiveis;
    }

    /**
     * @param array<int, int> $empresasDestinoIds
     * @param array<int, string> $modulos
     * @return array<int, array{modulo:string, area:string, tabela:string, inseridos:int, ignorados:int}>
     */
    public function replicar(int $empresaOrigemId, array $empresasDestinoIds, array $modulos): array
    {
        $modulesMap = $this->modulosDisponiveis();
        $modulos = $this->expandirDependenciasModulos($modulos);

        if (in_array('catalogo_fipe', $modulos, true)) {
            if (!Empresa::query()->where('id', 1)->exists()) {
                throw new InvalidArgumentException('A empresa base 1 não foi encontrada para replicar o catálogo FIPE.');
            }

            $empresaOrigemId = 1;
        }

        $destinos = array_values(array_unique(array_map('intval', $empresasDestinoIds)));
        $destinos = array_values(array_filter($destinos, fn (int $id) => $id !== $empresaOrigemId));
        $destinos = $this->apenasEmpresasExistentes($destinos);

        if (empty($destinos)) {
            throw new InvalidArgumentException('Nenhuma empresa de destino valida foi informada.');
        }

        if (empty($modulos)) {
            throw new InvalidArgumentException('Nenhum modulo foi informado para replicacao.');
        }

        $resultado = [];

        DB::transaction(function () use ($modulos, $modulesMap, $empresaOrigemId, $destinos, &$resultado): void {
            foreach ($modulos as $modulo) {
                if (!isset($modulesMap[$modulo])) {
                    continue;
                }

                $moduleData = $modulesMap[$modulo];
                $table = $moduleData['table'];
                $mode = (string) ($moduleData['mode'] ?? 'table');

                if ($mode === 'regra_permissao') {
                    $resultadoModulo = $this->replicarVinculosRegraPermissao($empresaOrigemId, $destinos);
                } elseif ($mode === 'bundle' && $modulo === 'catalogo_fipe') {
                    $resultadoModulo = $this->replicarCatalogoFipe($empresaOrigemId, $destinos);
                } elseif ($mode === 'bundle') {
                    $resultadoModulo = ['inseridos' => 0, 'ignorados' => 0];

                    foreach ((array) ($moduleData['tables'] ?? []) as $bundleTable) {
                        $bundleResultado = $this->replicarModulo((string) $bundleTable, $empresaOrigemId, $destinos);
                        $resultadoModulo['inseridos'] += (int) $bundleResultado['inseridos'];
                        $resultadoModulo['ignorados'] += (int) $bundleResultado['ignorados'];
                    }
                } elseif ($modulo === 'grupos_frota') {
                    $resultadoModulo = $this->replicarGruposFrota($empresaOrigemId, $destinos);
                } else {
                    $resultadoModulo = $this->replicarModulo($table, $empresaOrigemId, $destinos);
                }

                $resultado[] = [
                    'modulo' => $moduleData['label'],
                    'area' => $moduleData['area'] ?? 'Geral',
                    'tabela' => $table,
                    'inseridos' => $resultadoModulo['inseridos'],
                    'ignorados' => $resultadoModulo['ignorados'],
                ];
            }
        });

        return $resultado;
    }

    /**
     * @param array<int, int> $destinos
     * @return array{inseridos:int, ignorados:int}
     */
    private function replicarModulo(string $table, int $origem, array $destinos): array
    {
        $columns = Schema::getColumnListing($table);
        $hasCreatedAt = in_array('created_at', $columns, true);
        $hasUpdatedAt = in_array('updated_at', $columns, true);

        $copyColumns = array_values(array_diff($columns, ['id', 'empresa_id', 'created_at', 'updated_at']));
        if (empty($copyColumns) || empty($destinos)) {
            return ['inseridos' => 0, 'ignorados' => 0];
        }

        $origemRows = DB::table($table)->where('empresa_id', $origem)->get();
        $inseridos = 0;
        $ignorados = 0;

        foreach ($destinos as $empresaDestinoId) {
            foreach ($origemRows as $row) {
                $payload = [];
                foreach ($copyColumns as $column) {
                    $payload[$column] = $row->{$column} ?? null;
                }

                $existsQuery = DB::table($table)->where('empresa_id', $empresaDestinoId);
                foreach ($payload as $column => $value) {
                    if ($value === null) {
                        $existsQuery->whereNull($column);
                    } else {
                        $existsQuery->where($column, $value);
                    }
                }

                if ($existsQuery->exists()) {
                    $ignorados++;
                    continue;
                }

                $insertData = $payload;
                $insertData['empresa_id'] = $empresaDestinoId;
                if ($hasCreatedAt) {
                    $insertData['created_at'] = now();
                }
                if ($hasUpdatedAt) {
                    $insertData['updated_at'] = now();
                }

                DB::table($table)->insert($insertData);
                $inseridos++;
            }
        }

        return ['inseridos' => $inseridos, 'ignorados' => $ignorados];
    }

    /**
     * Replica o catalogo FIPE preservando os relacionamentos entre segmentos, marcas e modelos.
     *
     * @param array<int, int> $destinos
     * @return array{inseridos:int, ignorados:int}
     */
    private function replicarCatalogoFipe(int $origem, array $destinos): array
    {
        if (
            empty($destinos)
            || !Schema::hasTable('segmentos')
            || !Schema::hasTable('marcas')
            || !Schema::hasTable('modelos')
        ) {
            return ['inseridos' => 0, 'ignorados' => 0];
        }

        $segmentosOrigem = DB::table('segmentos')
            ->where('empresa_id', $origem)
            ->orderBy('id')
            ->get();

        $marcasOrigem = DB::table('marcas')
            ->where('empresa_id', $origem)
            ->orderBy('id')
            ->get();

        $modelosOrigem = DB::table('modelos')
            ->where('empresa_id', $origem)
            ->orderBy('id')
            ->get();

        $inseridos = 0;
        $ignorados = 0;

        foreach ($destinos as $empresaDestinoId) {
            $mapaSegmentos = [];
            $mapaMarcas = [];

            $resultadoSegmentos = $this->replicarSegmentosCatalogo($segmentosOrigem, $empresaDestinoId, $mapaSegmentos);
            $resultadoMarcas = $this->replicarMarcasCatalogo($marcasOrigem, $empresaDestinoId, $mapaMarcas);
            $resultadoModelos = $this->replicarModelosCatalogo($modelosOrigem, $empresaDestinoId, $mapaMarcas, $mapaSegmentos);

            $inseridos += $resultadoSegmentos['inseridos'] + $resultadoMarcas['inseridos'] + $resultadoModelos['inseridos'];
            $ignorados += $resultadoSegmentos['ignorados'] + $resultadoMarcas['ignorados'] + $resultadoModelos['ignorados'];
        }

        return ['inseridos' => $inseridos, 'ignorados' => $ignorados];
    }

    /**
     * @param iterable<int, object> $segmentosOrigem
     * @param array<int, int> $mapaSegmentos
     * @return array{inseridos:int, ignorados:int}
     */
    private function replicarSegmentosCatalogo(iterable $segmentosOrigem, int $empresaDestinoId, array &$mapaSegmentos): array
    {
        $destinoPorChave = DB::table('segmentos')
            ->where('empresa_id', $empresaDestinoId)
            ->get()
            ->keyBy(fn ($segmento) => $this->chaveSegmentoCatalogo($segmento));

        $inseridos = 0;
        $ignorados = 0;

        foreach ($segmentosOrigem as $segmentoOrigem) {
            $chave = $this->chaveSegmentoCatalogo($segmentoOrigem);
            if ($chave === '') {
                $ignorados++;
                continue;
            }

            $payload = [
                'nome' => $segmentoOrigem->nome ?? null,
                'ativo' => $segmentoOrigem->ativo ?? 'S',
                'empresa_id' => $empresaDestinoId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $existente = $destinoPorChave->get($chave);
            if ($existente) {
                $mapaSegmentos[(int) $segmentoOrigem->id] = (int) $existente->id;
                $ignorados++;
                continue;
            }

            $novoId = (int) DB::table('segmentos')->insertGetId($payload);
            $mapaSegmentos[(int) $segmentoOrigem->id] = $novoId;
            $destinoPorChave->put($chave, (object) ['id' => $novoId]);
            $inseridos++;
        }

        return ['inseridos' => $inseridos, 'ignorados' => $ignorados];
    }

    /**
     * @param iterable<int, object> $marcasOrigem
     * @param array<int, int> $mapaMarcas
     * @return array{inseridos:int, ignorados:int}
     */
    private function replicarMarcasCatalogo(iterable $marcasOrigem, int $empresaDestinoId, array &$mapaMarcas): array
    {
        $destinoPorChave = DB::table('marcas')
            ->where('empresa_id', $empresaDestinoId)
            ->get()
            ->keyBy(fn ($marca) => $this->chaveMarcaCatalogo($marca));

        $inseridos = 0;
        $ignorados = 0;

        foreach ($marcasOrigem as $marcaOrigem) {
            $chave = $this->chaveMarcaCatalogo($marcaOrigem);
            if ($chave === '') {
                $ignorados++;
                continue;
            }

            $payload = [
                'nome' => $marcaOrigem->nome ?? null,
                'nome_fipe' => $marcaOrigem->nome_fipe ?? null,
                'codigo_fipe' => $marcaOrigem->codigo_fipe ?? null,
                'ativo' => $marcaOrigem->ativo ?? 'S',
                'empresa_id' => $empresaDestinoId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $existente = $destinoPorChave->get($chave);
            if ($existente) {
                $mapaMarcas[(int) $marcaOrigem->id] = (int) $existente->id;
                $ignorados++;
                continue;
            }

            $novoId = (int) DB::table('marcas')->insertGetId($payload);
            $mapaMarcas[(int) $marcaOrigem->id] = $novoId;
            $destinoPorChave->put($chave, (object) ['id' => $novoId]);
            $inseridos++;
        }

        return ['inseridos' => $inseridos, 'ignorados' => $ignorados];
    }

    /**
     * @param iterable<int, object> $modelosOrigem
     * @param array<int, int> $mapaMarcas
     * @param array<int, int> $mapaSegmentos
     * @return array{inseridos:int, ignorados:int}
     */
    private function replicarModelosCatalogo(iterable $modelosOrigem, int $empresaDestinoId, array $mapaMarcas, array $mapaSegmentos): array
    {
        $destinoPorChave = DB::table('modelos')
            ->where('empresa_id', $empresaDestinoId)
            ->get()
            ->keyBy(fn ($modelo) => $this->chaveModeloCatalogo($modelo));

        $inseridos = 0;
        $ignorados = 0;
        $temSegmento = Schema::hasColumn('modelos', 'segmento_id');

        foreach ($modelosOrigem as $modeloOrigem) {
            $marcaOrigemId = isset($modeloOrigem->marca_id) ? (int) $modeloOrigem->marca_id : null;
            $segmentoOrigemId = $temSegmento && isset($modeloOrigem->segmento_id) ? (int) $modeloOrigem->segmento_id : null;

            $marcaDestinoId = $marcaOrigemId !== null ? ($mapaMarcas[$marcaOrigemId] ?? null) : null;
            $segmentoDestinoId = $segmentoOrigemId !== null ? ($mapaSegmentos[$segmentoOrigemId] ?? null) : null;

            if ($marcaOrigemId !== null && $marcaDestinoId === null) {
                $ignorados++;
                continue;
            }

            if ($temSegmento && $segmentoOrigemId !== null && $segmentoDestinoId === null) {
                $ignorados++;
                continue;
            }

            $payload = [
                'nome' => $modeloOrigem->nome ?? null,
                'nome_fipe' => $modeloOrigem->nome_fipe ?? null,
                'codigo_fipe' => $modeloOrigem->codigo_fipe ?? null,
                'imagem' => $modeloOrigem->imagem ?? null,
                'ativo' => $modeloOrigem->ativo ?? 'S',
                'marca_id' => $marcaDestinoId,
                'empresa_id' => $empresaDestinoId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($temSegmento) {
                $payload['segmento_id'] = $segmentoDestinoId;
            }

            $chave = $this->chaveModeloCatalogo($payload);
            if ($chave === '') {
                $ignorados++;
                continue;
            }

            $existente = $destinoPorChave->get($chave);
            if ($existente) {
                $ignorados++;
                continue;
            }

            $novoId = (int) DB::table('modelos')->insertGetId($payload);
            $destinoPorChave->put($chave, (object) ['id' => $novoId]);
            $inseridos++;
        }

        return ['inseridos' => $inseridos, 'ignorados' => $ignorados];
    }

    private function normalizarChaveCatalogo(mixed $valor): string
    {
        $texto = trim((string) $valor);
        if ($texto === '') {
            return '';
        }

        $texto = Str::ascii(mb_strtolower($texto));
        $texto = preg_replace('/\s+/u', ' ', $texto);

        return trim((string) $texto);
    }

    private function chaveSegmentoCatalogo(mixed $row): string
    {
        $nome = $this->normalizarChaveCatalogo(data_get($row, 'nome'));

        return $nome === '' ? '' : 'nome:' . $nome;
    }

    private function chaveMarcaCatalogo(mixed $row): string
    {
        $codigoFipe = $this->normalizarChaveCatalogo(data_get($row, 'codigo_fipe'));
        if ($codigoFipe !== '') {
            return 'codigo:' . $codigoFipe;
        }

        $nomeFipe = $this->normalizarChaveCatalogo(data_get($row, 'nome_fipe'));
        if ($nomeFipe !== '') {
            return 'nome_fipe:' . $nomeFipe;
        }

        $nome = $this->normalizarChaveCatalogo(data_get($row, 'nome'));

        return $nome === '' ? '' : 'nome:' . $nome;
    }

    private function chaveModeloCatalogo(mixed $row): string
    {
        $codigoFipe = $this->normalizarChaveCatalogo(data_get($row, 'codigo_fipe'));
        if ($codigoFipe !== '') {
            return 'codigo:' . $codigoFipe;
        }

        $nomeFipe = $this->normalizarChaveCatalogo(data_get($row, 'nome_fipe'));
        if ($nomeFipe !== '') {
            return 'nome_fipe:' . $nomeFipe
                . '|marca:' . (data_get($row, 'marca_id') ?? 'null')
                . '|segmento:' . (data_get($row, 'segmento_id') ?? 'null');
        }

        $nome = $this->normalizarChaveCatalogo(data_get($row, 'nome'));

        return $nome === '' ? '' : 'nome:' . $nome
            . '|marca:' . (data_get($row, 'marca_id') ?? 'null')
            . '|segmento:' . (data_get($row, 'segmento_id') ?? 'null');
    }

    /**
     * Replica grupos_frota remapeando tipo_frota_id pelo nome do tipo para
     * que os IDs da empresa destino sejam consistentes.
     * Também replica os vínculos grupo↔modelo (grupo_frota_modelo) quando a tabela existe.
     *
     * @param array<int, int> $destinos
     * @return array{inseridos:int, ignorados:int}
     */
    private function replicarGruposFrota(int $origem, array $destinos): array
    {
        if (
            empty($destinos)
            || !Schema::hasTable('grupos_frota')
            || !Schema::hasColumn('grupos_frota', 'empresa_id')
        ) {
            return ['inseridos' => 0, 'ignorados' => 0];
        }

        $columns     = Schema::getColumnListing('grupos_frota');
        $hasCreatedAt = in_array('created_at', $columns, true);
        $hasUpdatedAt = in_array('updated_at', $columns, true);
        $hasCodigo    = in_array('codigo', $columns, true);
        $hasTipoId    = in_array('tipo_frota_id', $columns, true);

        $copyColumns = array_values(array_diff($columns, ['id', 'empresa_id', 'created_at', 'updated_at', 'tipo_frota_id']));

        $gruposOrigem = DB::table('grupos_frota')->where('empresa_id', $origem)->get();

        // Mapa tipo_frota_id origem → nome (para re-resolver no destino)
        $tipoNomePorId = [];
        if ($hasTipoId && Schema::hasTable('tipo_frota')) {
            $tipoNomePorId = DB::table('tipo_frota')
                ->where('empresa_id', $origem)
                ->pluck('nome', 'id')
                ->all();
        }

        $inseridos = 0;
        $ignorados = 0;

        foreach ($destinos as $destinoId) {
            // Mapa nome → id dos tipos na empresa destino
            $tipoIdPorNomeDestino = [];
            if ($hasTipoId && Schema::hasTable('tipo_frota')) {
                $tipoIdPorNomeDestino = DB::table('tipo_frota')
                    ->where('empresa_id', $destinoId)
                    ->pluck('id', 'nome')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }

            // Mapa de grupo origem_id → destino_id para replicar vínculos
            $mapaGrupos = [];

            foreach ($gruposOrigem as $grupo) {
                $payload = [];
                foreach ($copyColumns as $col) {
                    $payload[$col] = $grupo->{$col} ?? null;
                }

                // Resolve tipo_frota_id pelo nome
                if ($hasTipoId) {
                    $tipoOrigemId = isset($grupo->tipo_frota_id) ? (int) $grupo->tipo_frota_id : null;
                    $tipoNome     = $tipoOrigemId !== null ? ($tipoNomePorId[$tipoOrigemId] ?? null) : null;
                    $tipoDestinoId = $tipoNome !== null ? ($tipoIdPorNomeDestino[$tipoNome] ?? null) : null;
                    $payload['tipo_frota_id'] = $tipoDestinoId;
                }

                // Chave de unicidade: empresa + codigo (ou nome)
                $existsQuery = DB::table('grupos_frota')->where('empresa_id', $destinoId);
                if ($hasCodigo && !empty($payload['codigo'])) {
                    $existsQuery->where('codigo', $payload['codigo']);
                } else {
                    $existsQuery->where('nome', $payload['nome']);
                }

                $existente = $existsQuery->first();
                if ($existente) {
                    $mapaGrupos[(int) $grupo->id] = (int) $existente->id;
                    $ignorados++;
                    continue;
                }

                $insertData = $payload;
                $insertData['empresa_id'] = $destinoId;
                if ($hasCreatedAt) { $insertData['created_at'] = now(); }
                if ($hasUpdatedAt) { $insertData['updated_at'] = now(); }

                $novoId = (int) DB::table('grupos_frota')->insertGetId($insertData);
                $mapaGrupos[(int) $grupo->id] = $novoId;
                $inseridos++;
            }

            // Replica vínculos grupo_frota_modelo
            $resultadoVinculos = $this->replicarGrupoFrotaModeloVinculos($origem, $destinoId, $mapaGrupos);
            $inseridos += $resultadoVinculos['inseridos'];
            $ignorados += $resultadoVinculos['ignorados'];
        }

        return ['inseridos' => $inseridos, 'ignorados' => $ignorados];
    }

    /**
     * Replica vínculos grupo_frota_modelo remapeando IDs de grupos e modelos.
     *
     * @param array<int, int> $mapaGrupos  grupo_id origem => grupo_id destino
     * @return array{inseridos:int, ignorados:int}
     */
    private function replicarGrupoFrotaModeloVinculos(int $origem, int $destino, array $mapaGrupos): array
    {
        if (
            empty($mapaGrupos)
            || !Schema::hasTable('grupo_frota_modelo')
            || !Schema::hasTable('modelos')
            || !Schema::hasColumn('modelos', 'empresa_id')
        ) {
            return ['inseridos' => 0, 'ignorados' => 0];
        }

        $grupoIdsOrigem = array_keys($mapaGrupos);

        $vinculos = DB::table('grupo_frota_modelo')
            ->whereIn('grupo_frota_id', $grupoIdsOrigem)
            ->get();

        if ($vinculos->isEmpty()) {
            return ['inseridos' => 0, 'ignorados' => 0];
        }

        // Mapa modelo nome+marca_nome → modelo_id destino
        $modelosOrigem = DB::table('modelos as m')
            ->join('marcas as ma', 'ma.id', '=', 'm.marca_id')
            ->where('m.empresa_id', $origem)
            ->select(['m.id as modelo_id', 'm.nome as modelo_nome', 'ma.nome as marca_nome'])
            ->get();

        $modelosDestino = DB::table('modelos as m')
            ->join('marcas as ma', 'ma.id', '=', 'm.marca_id')
            ->where('m.empresa_id', $destino)
            ->select(['m.id as modelo_id', 'm.nome as modelo_nome', 'ma.nome as marca_nome'])
            ->get()
            ->keyBy(fn ($r) => $r->modelo_nome . '|' . $r->marca_nome);

        $mapaModelos = [];
        foreach ($modelosOrigem as $mo) {
            $chave = $mo->modelo_nome . '|' . $mo->marca_nome;
            $md    = $modelosDestino->get($chave);
            if ($md) {
                $mapaModelos[(int) $mo->modelo_id] = (int) $md->modelo_id;
            }
        }

        $inseridos = 0;
        $ignorados = 0;

        foreach ($vinculos as $v) {
            $grupoDestinoId  = $mapaGrupos[(int) $v->grupo_frota_id] ?? null;
            $modeloDestinoId = $mapaModelos[(int) $v->modelo_id] ?? null;

            if ($grupoDestinoId === null || $modeloDestinoId === null) {
                $ignorados++;
                continue;
            }

            $existe = DB::table('grupo_frota_modelo')
                ->where('grupo_frota_id', $grupoDestinoId)
                ->where('modelo_id', $modeloDestinoId)
                ->exists();

            if ($existe) {
                $ignorados++;
                continue;
            }

            DB::table('grupo_frota_modelo')->insert([
                'grupo_frota_id' => $grupoDestinoId,
                'modelo_id'      => $modeloDestinoId,
            ]);
            $inseridos++;
        }

        return ['inseridos' => $inseridos, 'ignorados' => $ignorados];
    }

    /**
     * Replica vinculos da tabela role_has_permissions mapeando role/permissao por
     * (name + guard_name) da empresa origem para cada empresa destino.
     *
     * @param array<int, int> $destinos
     * @return array{inseridos:int, ignorados:int}
     */
    private function replicarVinculosRegraPermissao(int $origem, array $destinos): array
    {
        if (
            empty($destinos)
            || !Schema::hasTable('role_has_permissions')
            || !Schema::hasTable('roles')
            || !Schema::hasTable('permissions')
        ) {
            return ['inseridos' => 0, 'ignorados' => 0];
        }

        $vinculosOrigem = DB::table('role_has_permissions as rp')
            ->join('roles as r', 'r.id', '=', 'rp.role_id')
            ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('r.empresa_id', $origem)
            ->where('p.empresa_id', $origem)
            ->select([
                'r.name as role_name',
                'r.guard_name as role_guard',
                'p.name as permissao_name',
                'p.guard_name as permissao_guard',
            ])
            ->distinct()
            ->get();

        if ($vinculosOrigem->isEmpty()) {
            return ['inseridos' => 0, 'ignorados' => 0];
        }

        $inseridos = 0;
        $ignorados = 0;

        foreach ($destinos as $empresaDestinoId) {
            $rolesDestino = DB::table('roles')
                ->where('empresa_id', $empresaDestinoId)
                ->select(['id', 'name', 'guard_name'])
                ->get()
                ->keyBy(fn ($role) => $role->name . '|' . $role->guard_name);

            $permissoesDestino = DB::table('permissions')
                ->where('empresa_id', $empresaDestinoId)
                ->select(['id', 'name', 'guard_name'])
                ->get()
                ->keyBy(fn ($permissao) => $permissao->name . '|' . $permissao->guard_name);

            foreach ($vinculosOrigem as $vinculo) {
                $roleKey = $vinculo->role_name . '|' . $vinculo->role_guard;
                $permissaoKey = $vinculo->permissao_name . '|' . $vinculo->permissao_guard;

                $roleId = $rolesDestino->get($roleKey)->id ?? null;
                $permissaoId = $permissoesDestino->get($permissaoKey)->id ?? null;

                if (!$roleId || !$permissaoId) {
                    $ignorados++;
                    continue;
                }

                $existe = DB::table('role_has_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissaoId)
                    ->exists();

                if ($existe) {
                    $ignorados++;
                    continue;
                }

                DB::table('role_has_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissaoId,
                ]);
                $inseridos++;
            }
        }

        return ['inseridos' => $inseridos, 'ignorados' => $ignorados];
    }

    /**
     * Garante replicacao de tabelas dependentes quando modulo principal e selecionado.
     *
     * @param array<int, string> $modulos
     * @return array<int, string>
     */
    private function expandirDependenciasModulos(array $modulos): array
    {
        $result = array_values(array_unique(array_filter(array_map('strval', $modulos))));

        // Tipo de frota depende da estrutura de caracteristicas e seus tipos de valor.
        if (in_array('tipo_frota', $result, true)) {
            $result[] = 'tipo_valor_caracteristica_frota';
            $result[] = 'caracteristicas_frotas';
        }

        if (in_array('modelos', $result, true)) {
            $result[] = 'marcas';
        }

        // Grupo de frota depende de tipo de frota (e consequentemente das caracteristicas).
        if (in_array('grupos_frota', $result, true)) {
            $result[] = 'tipo_frota';
        }

        // Caracteristicas dependem dos tipos de valor.
        if (in_array('caracteristicas_frotas', $result, true)) {
            $result[] = 'tipo_valor_caracteristica_frota';
        }

        // Opcional completo (tipo + itens + vinculos).
        if (in_array('tipo_opcional', $result, true) || in_array('opcionais', $result, true) || in_array('opcional_grupo_veiculo', $result, true)) {
            $result[] = 'tipo_opcional';
            $result[] = 'opcionais';
            $result[] = 'opcional_grupo_veiculo';
        }

        // Danos completo (categoria + itens).
        if (in_array('categoria_danos', $result, true) || in_array('danos', $result, true)) {
            $result[] = 'categoria_danos';
            $result[] = 'danos';
        }

        // Checklist completo (tipos + status + itens + configuracoes + checklists).
        if (
            in_array('tipo_checklist', $result, true)
            || in_array('status_item_checklist', $result, true)
            || in_array('item_checklist', $result, true)
            || in_array('configuracao_checklist', $result, true)
            || in_array('checklists', $result, true)
        ) {
            $result[] = 'tipo_checklist';
            $result[] = 'status_item_checklist';
            $result[] = 'item_checklist';
            $result[] = 'configuracao_checklist';
            $result[] = 'checklists';
        }

        // Seguranca: vinculos so fazem sentido com regras + permissoes.
        if (in_array('regras_permissoes', $result, true)) {
            $result[] = 'regras';
            $result[] = 'permissoes';
        }

        if (in_array('regras', $result, true) && in_array('permissoes', $result, true)) {
            $result[] = 'regras_permissoes';
        }

        if (in_array('catalogo_fipe', $result, true)) {
            $result = array_values(array_diff($result, ['segmentos', 'marcas', 'modelos']));
        }

        return array_values(array_unique($result));
    }

    /**
     * @param array<int, int> $empresaIds
     * @return array<int, int>
     */
    private function apenasEmpresasExistentes(array $empresaIds): array
    {
        if (empty($empresaIds) || !Schema::hasTable('empresas')) {
            return [];
        }

        return DB::table('empresas')
            ->whereIn('id', $empresaIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @param array<int, string> $tables
     */
    private function resolverTabelaComEmpresaId(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'empresa_id')) {
                return $table;
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $tables
     */
    private function resolverTabelaExistente(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $tables
     * @return array<int, string>
     */
    private function resolverTabelasComEmpresaId(array $tables): array
    {
        $tablesExistentes = [];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'empresa_id')) {
                $tablesExistentes[] = $table;
            }
        }

        return array_values(array_unique($tablesExistentes));
    }

    /**
     * Retorna os horários diários configurados para execução automática da replicação.
     * Formato esperado: array de strings "HH:MM".
     *
     * @return array<int, string>
     */
    public function horariosDiarios(): array
    {
        $raw = config('replicacao.horarios_diarios', []);
        if (!is_array($raw)) {
            return [];
        }

        $valid = [];
        foreach ($raw as $item) {
            $item = trim((string) $item);
            if ($item === '') {
                continue;
            }
            // Valida formato HH:MM
            if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $item)) {
                $valid[] = $item;
            }
        }

        return array_values(array_unique($valid));
    }
}
