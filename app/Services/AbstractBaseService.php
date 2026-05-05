<?php

namespace App\Services;

use App\Services\Interfaces\BaseServiceInterface;
use App\Support\EmpresaContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class AbstractBaseService implements BaseServiceInterface
{
    protected Model $model;

    public function getAll($where = [])
    {
        $where = $this->sanitizeEmpresaFilter($where);
        $where = $this->sanitizeEstacaoFilter($where);
        $where = $this->sanitizeInvalidColumnFilters($where);
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()));

        return $query->where($where)->get();
    }

    public function getAllForSelect($key = 'id', $value = 'nome')
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()));

        return $this->formatArraySelect($query->get(), $key, $value);
    }

    public function getAllWithFiltersAndPagination($filters = [], $perPage = 15, $with = [], $orderBy = 'id', $order = 'asc')
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))->with($with);
        $filters = $this->sanitizeEmpresaFilter($filters);
        $filters = $this->sanitizeEstacaoFilter($filters);
        $filters = $this->sanitizeInvalidColumnFilters($filters);
 
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                if (!is_array($value) || !array_key_exists('valor', $value)) {
                    continue;
                }

                $typeWhere = $value['typeWhere'] ?? 'where';
                $val = $value['valor'];

                if ($typeWhere === 'between') {
                    $from = $val['from'] ?? null;
                    $to = $val['to'] ?? null;
                    if ($from && $to) {
                        $query->whereBetween($field, [$from, $to]);
                    } elseif ($from) {
                        $query->where($field, '>=', $from);
                    } elseif ($to) {
                        $query->where($field, '<=', $to);
                    }
                } elseif ($typeWhere === 'where') {
                    if (is_array($val)) {
                        $query->whereIn($field, $val);
                    } else {
                        $query->where($field, $val);
                    }
                } else { // like
                    if (is_array($val)) {
                        // fallback: apply like for first element
                        $query->where($field, 'like', '%' . (string) ($val[0] ?? '') . '%');
                    } else {
                        $query->where($field, 'like', '%' . $val . '%');
                    }
                }
            }
        }

        $query->orderBy($orderBy, $order);

        return $query->paginate($perPage);
    }

    public function getById($id, $with = [])
    {
        return $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))->with($with)->find($id);
    }

    public function create(array $data)
    {
        $this->ensureEmpresaSelectedForWrite();
        $this->injectEmpresaId($data);
        $this->guardEstacaoAssignments($data);

        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $this->ensureEmpresaSelectedForWrite();
        $item = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))->find($id);
        if ($item) {
            $this->injectEmpresaId($data, true);
            $this->guardEstacaoAssignments($data);
            $item->update($data);
            return $item;
        }
        return null;
    }

    public function delete($id)
    {
        $this->ensureEmpresaSelectedForWrite();
        $item = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))->find($id);
        if ($item) {
            return $item->delete();
        }
        return false;
    }

    // Método abstrato para definir o modelo nas classes filhas
    abstract public function setModel(Model $model): void;

    public function formatArraySelect($data, string $idField = 'id', string $nameField = 'name'): array
    {
        $arrayData = $data->toArray();
        return array_column($arrayData, $nameField, $idField);
    }

    public function getAllAtivosForSelectSubText($id = 'id', $nome = 'nome', $subtitle = '', $orderBy = null, $orderDirection = 'asc')
    {
        if($subtitle == ''){
            throw new \Exception('Subtitle não pode ser vazio');
        }

        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->select($id, $nome, $subtitle)
            ->distinct();

        if (SchemaCache::hasColumn($this->model->getTable(), 'ativo')) {
            $query->where('ativo', 'S');
        }
        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        if (!$this->shouldApplyEmpresaScope()) {
            return $query->get();
        }

        $empresaId = $this->currentEmpresaId();

        if ($empresaId === null) {
            return [];
        }

        return $query->where('empresa_id', $empresaId)->get();
    }
    public function formatArraySelectSubText($data, string $idField = 'id', string $nameField = 'name'): array
    {
        $arrayData = $data->toArray();
        return array_column($arrayData, $nameField, $idField);
    }
    public function getAllAtivos($id = 'id', $nome = 'nome', $orderBy = null, $orderDirection = 'asc',$attrExtra = [])
    {
        $attrExtra = is_array($attrExtra) ? array_values(array_filter($attrExtra, fn ($item) => is_string($item) && trim($item) !== '')) : [];

        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->select(array_merge([$id, $nome], $attrExtra))
            ->distinct();

        if (SchemaCache::hasColumn($this->model->getTable(), 'ativo')) {
            $query->where('ativo', 'S');
        }
        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }
        if (!$this->shouldApplyEmpresaScope()) {
            $data = $query->get();

            return $this->formatArraySelect($data, $id, $nome);
        }

        $empresaId = $this->currentEmpresaId();
        if ($empresaId === null) {
            return [];
        }

        $data = $query->where('empresa_id', $empresaId)->get();

        return $this->formatArraySelect($data, $id, $nome);
    }
    public function getAllFormatSelect($id = 'id', $nome = 'nome', $orderBy = null, $orderDirection = 'asc')
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->select($id, $nome)
            ->distinct();

        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        if (!$this->shouldApplyEmpresaScope()) {
            $data = $query->get();

            return $this->formatArraySelect($data, $id, $nome);
        }

        $empresaId = $this->currentEmpresaId();
        if ($empresaId === null) {
            return [];
        }

        $data = $query->where('empresa_id', $empresaId)->get();
        return $this->formatArraySelect($data, $id, $nome);
    }

    protected function applyEmpresaScope($query)
    {
        if (!$this->shouldApplyEmpresaScope()) {
            return $query;
        }

        // Tabela 'empresas' nunca deve ser filtrada por empresa_id — mostra todas sempre.
        if ($this->model->getTable() === 'empresas') {
            return $query;
        }

        $empresaId = $this->currentEmpresaId();
        if ($empresaId === null) {
            $user = Auth::user();
            if ($user?->isSuperAdmin()) {
                return $query;
            }

            if ($user?->isAdministrador()) {
                throw new AccessDeniedHttpException('Selecione uma empresa para visualizar estes registros.');
            }

            throw new AccessDeniedHttpException('Empresa nao identificada para a operacao.');
        }

        return $query->where($this->model->getTable() . '.empresa_id', $empresaId);
    }

    protected function injectEmpresaId(array &$data, bool $override = true): void
    {
        if (!$this->shouldApplyEmpresaScope()) {
            return;
        }

        $empresaId = $this->currentEmpresaId();
        if ($empresaId === null) {
            if (Auth::user()?->isSuperAdmin()) {
                // Super admin: exige empresa_id explícita quando não há contexto selecionado.
                if (!isset($data['empresa_id']) || $data['empresa_id'] === null || $data['empresa_id'] === '') {
                    throw new AccessDeniedHttpException('Informe o empresa_id para esta operacao (super admin).');
                }
                return;
            }

            throw new AccessDeniedHttpException('Empresa nao identificada para a operacao.');
        }

        if ($override || !isset($data['empresa_id'])) {
            $data['empresa_id'] = $empresaId;
        }
    }

    protected function shouldApplyEmpresaScope(): bool
    {
        return SchemaCache::hasColumn($this->model->getTable(), 'empresa_id');
    }

    protected function currentEmpresaId(): ?int
    {
        if (Auth::user()?->isAdministrador()) {
            /** @var EmpresaContext $empresaContext */
            $empresaContext = app(EmpresaContext::class);

            return $empresaContext->empresaId();
        }

        $userEmpresaId = Auth::user()?->empresa_id;
        if ($userEmpresaId !== null) {
            return (int) $userEmpresaId;
        }

        /** @var EmpresaContext $empresaContext */
        $empresaContext = app(EmpresaContext::class);

        return $empresaContext->empresaId();
    }

    protected function sanitizeEmpresaFilter(array $filters): array
    {
        if (!array_key_exists('empresa_id', $filters)) {
            return $filters;
        }

        $empresaId = $this->currentEmpresaId();
        $incoming = $filters['empresa_id'];
        $incomingValue = is_array($incoming) ? ($incoming['valor'] ?? null) : $incoming;

        if ($empresaId !== null && $incomingValue !== null && (int) $incomingValue !== (int) $empresaId) {
            throw new AccessDeniedHttpException('Filtro de empresa invalido para o usuario autenticado.');
        }

        unset($filters['empresa_id']);

        return $filters;
    }

    protected function applyEstacaoScope($query)
    {
        $user = Auth::user();
        if (!$user || $user->isAdministrador() || $user->hasAcessoTodasEstacoes()) {
            return $query;
        }

        $table = $this->model->getTable();
        $estacaoColumns = $this->estacaoScopeColumns($table);

        // Se a tabela NíO é 'estacoes' e NÃO possui nenhuma coluna de vinculo com estacao,
        // esse scope não deve aplicar nenhum filtro restritivo.
        if ($table !== 'estacoes' && empty($estacaoColumns)) {
            return $query;
        }

        $allowedIds = $this->allowedEstacaoIdsForUser();
        if (empty($allowedIds)) {
            return $query->whereRaw('1 = 0');
        }

        if ($table === 'estacoes') {
            return $query->whereIn($table . '.id', $allowedIds);
        }

        if (count($estacaoColumns) === 1) {
            return $query->whereIn($table . '.' . $estacaoColumns[0], $allowedIds);
        }

        return $query->where(function ($subQuery) use ($estacaoColumns, $allowedIds, $table) {
            foreach ($estacaoColumns as $column) {
                $subQuery->orWhereIn($table . '.' . $column, $allowedIds);
            }
        });
    }

    protected function sanitizeEstacaoFilter(array $filters): array
    {
        $allowedIds = $this->allowedEstacaoIdsForUser();
        if ($allowedIds === null) {
            return $filters;
        }

        $estacaoFields = array_merge(['id'], $this->estacaoScopeColumns($this->model->getTable()));
        if ($this->model->getTable() !== 'estacoes') {
            $estacaoFields = array_diff($estacaoFields, ['id']);
        }

        foreach ($estacaoFields as $field) {
            if (!array_key_exists($field, $filters)) {
                continue;
            }


            $incoming = $filters[$field];
            $incomingValue = is_array($incoming) ? ($incoming['valor'] ?? null) : $incoming;

            if ($incomingValue !== null) {
                if (is_array($incomingValue)) {
                    foreach ($incomingValue as $val) {
                        if (!in_array((int) $val, $allowedIds, true)) {
                            throw new AccessDeniedHttpException('Filtro de estacao invalido para o usuario autenticado.');
                        }
                    }
                } else {
                    if (!in_array((int) $incomingValue, $allowedIds, true)) {
                        throw new AccessDeniedHttpException('Filtro de estacao invalido para o usuario autenticado.');
                    }
                }
            }

            unset($filters[$field]);
        }

        return $filters;
    }

    protected function guardEstacaoAssignments(array $data): void
    {
        $allowedIds = $this->allowedEstacaoIdsForUser();
        if ($allowedIds === null || empty($data)) {
            return;
        }

        $fields = array_merge(
            $this->estacaoScopeColumns($this->model->getTable()),
            $this->model->getTable() === 'estacoes' ? ['id'] : []
        );

        foreach ($fields as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                continue;
            }

            if (!in_array((int) $data[$field], $allowedIds, true)) {
                throw new AccessDeniedHttpException('Estacao informada nao permitida para o usuario autenticado.');
            }
        }
    }

    protected function allowedEstacaoIdsForUser(): ?array
    {
        $user = Auth::user();
        if (!$user || $user->isAdministrador() || $user->hasAcessoTodasEstacoes()) {
            return null;
        }

        return $user->estacoesPermitidasIds();
    }

    protected function estacaoScopeColumns(string $table): array
    {
        $candidates = [
            'estacao_id',
            'local_disponibilidade',
            'estacao_retirada_id',
            'estacao_devolucao_id',
            'estacao_origem_id',
            'estacao_destino_id',
            'local_origem',
            'local_destino',
            'local_origem_id',
            'local_destino_id',
            'local',
        ];

        return array_values(array_filter(
            $candidates,
            fn (string $column): bool => SchemaCache::hasColumn($table, $column)
        ));
    }

    protected function sanitizeInvalidColumnFilters(array $filters): array
    {
        if (empty($filters)) {
            return $filters;
        }

        $table = $this->model->getTable();

        foreach ($filters as $field => $value) {
            // Campos complexos/relacionais ficam por conta de quem monta query customizada.
            if (!is_string($field) || !preg_match('/^[A-Za-z0-9_\.]+$/', $field)) {
                unset($filters[$field]);
                continue;
            }

            $column = str_contains($field, '.') ? (string) str($field)->afterLast('.') : $field;
            if (!SchemaCache::hasColumn($table, $column)) {
                unset($filters[$field]);
            }
        }

        return $filters;
    }

    protected function ensureEmpresaSelectedForWrite(): void
    {
        if (!$this->shouldApplyEmpresaScope()) {
            return;
        }

        $empresaId = $this->currentEmpresaId();
        if ($empresaId !== null) {
            return;
        }

        if (Auth::user()?->isAdministrador()) {
            throw new AccessDeniedHttpException('Selecione uma empresa para criar, editar ou excluir registros.');
        }
    }
}
