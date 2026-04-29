<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmpresaService extends AbstractBaseService
{
    protected Model $model;
    private ReplicacaoBaseService $replicacaoBaseService;
    private DefaultFrotaTemplateService $defaultFrotaTemplateService;
    private ?array $empresaColumnsCache = null;

    public function __construct(
        Empresa $empresa,
        ReplicacaoBaseService $replicacaoBaseService,
        DefaultFrotaTemplateService $defaultFrotaTemplateService,
    ) {
        $this->model = $empresa;
        $this->replicacaoBaseService = $replicacaoBaseService;
        $this->defaultFrotaTemplateService = $defaultFrotaTemplateService;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Empresa) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Empresa');
        }

        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        $data = $this->sanitizeDataForExistingColumns($data);
        $empresa = $this->model->newQuery()->create($data);

        if (Schema::hasColumn($this->model->getTable(), 'empresa_id') && empty($empresa->empresa_id)) {
            $empresa->empresa_id = $empresa->id;
            $empresa->save();
        }

        return $empresa;
    }

    public function update($id, array $data)
    {
        $data = $this->sanitizeDataForExistingColumns($data);
        $item = $this->model->newQuery()->find($id);

        if (!$item) {
            return null;
        }

        $item->update($data);
        return $item;
    }

    public function createWithDefaultTemplate(array $data): Empresa
    {
        return DB::transaction(function () use ($data): Empresa {
            /** @var Empresa $empresa */
            $empresa = $this->create($data);

            $templateEmpresaId = $this->resolveDefaultTemplateEmpresaId();
            if ($templateEmpresaId === null || $templateEmpresaId === (int) $empresa->id) {
                return $empresa;
            }

            $this->seedDefaultTemplateCompanyData((int) $templateEmpresaId);

            $modulos = array_keys($this->replicacaoBaseService->modulosDisponiveis());
            if (!empty($modulos)) {
                $this->replicacaoBaseService->replicar($templateEmpresaId, [(int) $empresa->id], $modulos);
            }

            return $empresa;
        });
    }

    protected function shouldApplyEmpresaScope(): bool
    {
        // Cadastro de empresas e multi-tenant de administracao global.
        return false;
    }

    private function resolveDefaultTemplateEmpresaId(): ?int
    {
        if (!Schema::hasTable('empresas')) {
            return null;
        }

        $table = $this->model->getTable();
        $defaultId = DB::table($table)
            ->where('slug', Empresa::DEFAULT_TEMPLATE_SLUG)
            ->value('id');

        if ($defaultId) {
            return (int) $defaultId;
        }

        $newId = DB::table($table)->insertGetId([
            'nome' => 'Empresa Padrao',
            'slug' => Empresa::DEFAULT_TEMPLATE_SLUG,
            'ativo' => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (Schema::hasColumn($table, 'empresa_id')) {
            DB::table($table)->where('id', $newId)->update(['empresa_id' => $newId]);
        }

        return (int) $newId;
    }

    /**
     * Garante que a empresa base tenha os cadastros globais de horarios e dias uteis
     * para que novas empresas recebam esses registros ao serem replicadas.
     */
    private function seedDefaultTemplateCompanyData(int $empresaId): void
    {
        $this->replicateGlobalRowsToEmpresa('dias_uteis', $empresaId, ['dia']);
        $this->replicateGlobalRowsToEmpresa('horarios_diarios', $empresaId, ['nome']);
        $this->defaultFrotaTemplateService->seed($empresaId);
    }

    /**
     * Copia linhas globais (empresa_id nulo) para a empresa informada, respeitando
     * uma chave natural simples para evitar duplicidade.
     *
     * @param array<int, string> $uniqueColumns
     */
    private function replicateGlobalRowsToEmpresa(string $table, int $empresaId, array $uniqueColumns): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'empresa_id')) {
            return;
        }

        $columns = Schema::getColumnListing($table);
        $rows = DB::table($table)->whereNull('empresa_id')->get();
        if ($rows->isEmpty()) {
            return;
        }

        foreach ($rows as $row) {
            $payload = [];
            foreach ($columns as $column) {
                if (in_array($column, ['id', 'empresa_id', 'created_at', 'updated_at'], true)) {
                    continue;
                }

                $payload[$column] = $row->{$column} ?? null;
            }

            $where = [];
            foreach ($uniqueColumns as $column) {
                if (array_key_exists($column, $payload)) {
                    $where[$column] = $payload[$column];
                }
            }

            if (empty($where)) {
                continue;
            }

            $payload['empresa_id'] = $empresaId;
            if (in_array('created_at', $columns, true)) {
                $payload['created_at'] = now();
            }
            if (in_array('updated_at', $columns, true)) {
                $payload['updated_at'] = now();
            }

            DB::table($table)->updateOrInsert(
                array_merge($where, ['empresa_id' => $empresaId]),
                $payload
            );
        }
    }

    private function sanitizeDataForExistingColumns(array $data): array
    {
        $columns = $this->getEmpresaColumns();

        return array_filter(
            $data,
            fn (string $key): bool => in_array($key, $columns, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function getEmpresaColumns(): array
    {
        if ($this->empresaColumnsCache !== null) {
            return $this->empresaColumnsCache;
        }

        if (!Schema::hasTable($this->model->getTable())) {
            $this->empresaColumnsCache = [];
            return $this->empresaColumnsCache;
        }

        $this->empresaColumnsCache = Schema::getColumnListing($this->model->getTable());

        return $this->empresaColumnsCache;
    }
}
