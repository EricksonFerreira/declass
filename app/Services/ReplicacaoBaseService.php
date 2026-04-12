<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
                } elseif ($mode === 'bundle') {
                    $resultadoModulo = ['inseridos' => 0, 'ignorados' => 0];

                    foreach ((array) ($moduleData['tables'] ?? []) as $bundleTable) {
                        $bundleResultado = $this->replicarModulo((string) $bundleTable, $empresaOrigemId, $destinos);
                        $resultadoModulo['inseridos'] += (int) $bundleResultado['inseridos'];
                        $resultadoModulo['ignorados'] += (int) $bundleResultado['ignorados'];
                    }
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
