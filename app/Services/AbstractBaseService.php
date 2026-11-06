<?php

namespace App\Services;

use App\Services\Interfaces\BaseServiceInterface;
use App\Support\EmpresaContext;
use App\Support\EscolaContext;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class AbstractBaseService implements BaseServiceInterface
{
    protected Model $model;

    public function getAll($where = [])
    {
        $where = $this->sanitizeEmpresaFilter($where);
        $where = $this->sanitizeInvalidColumnFilters($where);
        $query = $this->applyEmpresaScope($this->model->newQuery());

        return $query->where($where)->get();
    }

    public function getAllForSelect($key = 'id', $value = 'nome')
    {
        $query = $this->applyEmpresaScope($this->model->newQuery());

        return $this->formatArraySelect($query->get(), $key, $value);
    }

    public function getAllWithFiltersAndPagination($filters = [], $perPage = 15, $with = [], $orderBy = 'id', $order = 'asc', ?\Closure $scope = null)
    {
        $query = $this->applyEmpresaScope($this->model->newQuery())->with($with);
        if ($scope) {
            $scope($query);
        }
        $filters = $this->sanitizeEmpresaFilter($filters);
        $filters = $this->sanitizeEscolaContextFilter($filters);
        $filters = $this->sanitizeInvalidColumnFilters($filters);

        if (! empty($filters)) {
            foreach ($filters as $field => $value) {
                if (! is_array($value) || ! array_key_exists('valor', $value)) {
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
                        $query->where($field, 'like', '%'.(string) ($val[0] ?? '').'%');
                    } else {
                        $query->where($field, 'like', '%'.$val.'%');
                    }
                }
            }
        }

        $query->orderBy($orderBy, $order);

        return $query->paginate($perPage);
    }

    public function getById($id, $with = [])
    {
        return $this->applyEmpresaScope($this->model->newQuery())->with($with)->find($id);
    }

    public function create(array $data)
    {
        $this->ensureEmpresaSelectedForWrite();
        $this->injectEmpresaId($data);

        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $this->ensureEmpresaSelectedForWrite();
        $item = $this->applyEmpresaScope($this->model->newQuery())->find($id);
        if ($item) {
            $this->injectEmpresaId($data, true);
            $item->update($data);

            return $item;
        }

        return null;
    }

    public function delete($id)
    {
        $this->ensureEmpresaSelectedForWrite();
        $item = $this->applyEmpresaScope($this->model->newQuery())->find($id);
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
        if ($subtitle == '') {
            throw new \Exception('Subtitle não pode ser vazio');
        }

        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->select($id, $nome, $subtitle)
            ->distinct();

        if (SchemaCache::hasColumn($this->model->getTable(), 'ativo')) {
            $query->where('ativo', 'S');
        }
        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        if (! $this->shouldApplyEmpresaScope()) {
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

    public function getAllAtivos($id = 'id', $nome = 'nome', $orderBy = null, $orderDirection = 'asc', $attrExtra = [])
    {
        $attrExtra = is_array($attrExtra) ? array_values(array_filter($attrExtra, fn ($item) => is_string($item) && trim($item) !== '')) : [];

        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->select(array_merge([$id, $nome], $attrExtra))
            ->distinct();

        if (SchemaCache::hasColumn($this->model->getTable(), 'ativo')) {
            $query->where('ativo', 'S');
        }
        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }
        if (! $this->shouldApplyEmpresaScope()) {
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
        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->select($id, $nome)
            ->distinct();

        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        if (! $this->shouldApplyEmpresaScope()) {
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
        if (! $this->shouldApplyEmpresaScope()) {
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

        return $query->where($this->model->getTable().'.empresa_id', $empresaId);
    }

    protected function injectEmpresaId(array &$data, bool $override = true): void
    {
        if (! $this->shouldApplyEmpresaScope()) {
            return;
        }

        $empresaId = $this->currentEmpresaId();
        if ($empresaId === null) {
            if (Auth::user()?->isSuperAdmin()) {
                // Super admin: exige empresa_id explícita quando não há contexto selecionado.
                if (! isset($data['empresa_id']) || $data['empresa_id'] === null || $data['empresa_id'] === '') {
                    throw new AccessDeniedHttpException('Informe o empresa_id para esta operacao (super admin).');
                }

                return;
            }

            throw new AccessDeniedHttpException('Empresa nao identificada para a operacao.');
        }

        if ($override || ! isset($data['empresa_id'])) {
            $data['empresa_id'] = $empresaId;
        }
    }

    protected function shouldApplyEmpresaScope(): bool
    {
        return SchemaCache::hasColumn($this->model->getTable(), 'empresa_id');
    }

    protected function currentEmpresaId(): ?int
    {
        // Apenas o superadmin da plataforma pode operar com contexto
        // de tenant selecionavel (inclusive "todas" = null).
        if (Auth::user()?->isSuperAdmin()) {
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
        if (! array_key_exists('empresa_id', $filters)) {
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

    /**
     * Com uma escola selecionada no contexto da sessao, o escopo global ja
     * filtra escola_id — um filtro de escola divergente e ignorado para
     * nao esvaziar o resultado nem contornar o contexto.
     */
    protected function sanitizeEscolaContextFilter(array $filters): array
    {
        if (app(EscolaContext::class)->filtrar()) {
            unset($filters['escola_id']);
        }

        return $filters;
    }

    protected function sanitizeInvalidColumnFilters(array $filters): array
    {
        if (empty($filters)) {
            return $filters;
        }

        $table = $this->model->getTable();

        foreach ($filters as $field => $value) {
            // Campos complexos/relacionais ficam por conta de quem monta query customizada.
            if (! is_string($field) || ! preg_match('/^[A-Za-z0-9_\.]+$/', $field)) {
                unset($filters[$field]);

                continue;
            }

            $column = str_contains($field, '.') ? (string) str($field)->afterLast('.') : $field;
            if (! SchemaCache::hasColumn($table, $column)) {
                unset($filters[$field]);
            }
        }

        return $filters;
    }

    protected function ensureEmpresaSelectedForWrite(): void
    {
        if (! $this->shouldApplyEmpresaScope()) {
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
