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
    private ?array $empresaColumnsCache = null;

    public function __construct(Empresa $empresa, ReplicacaoBaseService $replicacaoBaseService)
    {
        $this->model = $empresa;
        $this->replicacaoBaseService = $replicacaoBaseService;
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
