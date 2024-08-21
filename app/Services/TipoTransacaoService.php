<?php

namespace App\Services;

use App\DTO\BreadcrumbItem;
use App\Helpers\BreadcrumbHelper;
use App\Models\TipoTransacao;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class TipoTransacaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(TipoTransacao $tipotransacao)
    {
        $this->model = $tipotransacao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof TipoTransacao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\TipoTransacao");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function prepararBreadcrumbIndex()
    {
        $items = [
            new BreadcrumbItem('Tipo Transação', route('tipo_transacao.index')),
        ];
        return BreadcrumbHelper::generate($items);
    }

    public function prepararBreadcrumbCreate()
    {
        $items = [
            new BreadcrumbItem('Tipo Transação', route('tipo_transacao.index')),
            new BreadcrumbItem('Criar', route('tipo_transacao.create')),
        ];
        return BreadcrumbHelper::generate($items);
    }

    public function prepararBreadcrumbEdit($id)
    {
        $items = [
            new BreadcrumbItem('Tipo Transação', route('tipo_transacao.index')),
            new BreadcrumbItem('Editar', route('tipo_transacao.edit', ['id' => $id])),
        ];
        return BreadcrumbHelper::generate($items);
    }
}
