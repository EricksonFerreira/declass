<?php

namespace App\Services;

use App\Models\Empresa;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmpresaService extends AbstractBaseService
{
    protected Model $model;

    private ?array $empresaColumnsCache = null;

    public function __construct(Empresa $empresa)
    {
        $this->model = $empresa;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Empresa) {
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

        if (empty($data['slug']) && ! empty($data['nome'])) {
            $data['slug'] = $this->gerarSlugUnico((string) $data['nome']);
        }

        return DB::transaction(function () use ($data): Empresa {
            /** @var Empresa $empresa */
            $empresa = $this->model->newQuery()->create($data);

            // Perfis escolares padrao do tenant.
            app(PermissaoSyncService::class)
                ->garantirPapeisParaEmpresa((int) $empresa->id);

            return $empresa;
        });
    }

    public function update($id, array $data)
    {
        $data = $this->sanitizeDataForExistingColumns($data);
        $item = $this->model->newQuery()->find($id);

        if (! $item) {
            return null;
        }

        $item->update($data);

        return $item;
    }

    protected function shouldApplyEmpresaScope(): bool
    {
        // Cadastro de empresas e multi-tenant de administracao global.
        return false;
    }

    private function gerarSlugUnico(string $nome): string
    {
        $base = Str::slug($nome) ?: 'tenant';
        $slug = $base;
        $i = 1;

        while ($this->model->newQuery()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
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

        if (! SchemaCache::hasTable($this->model->getTable())) {
            $this->empresaColumnsCache = [];

            return $this->empresaColumnsCache;
        }

        $this->empresaColumnsCache = Schema::getColumnListing($this->model->getTable());

        return $this->empresaColumnsCache;
    }
}
