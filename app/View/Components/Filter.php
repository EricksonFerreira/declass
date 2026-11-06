<?php

namespace App\View\Components;

use App\Support\EscolaContext;
use Illuminate\View\Component;

class Filter extends Component
{
    public $filters;

    public $additionalFilters;

    public $itensFiltered;

    public $statusList;

    public $nomeFiltro;

    public $urlPadrao;

    public $routeRedirect;

    public function __construct(
        $filters,
        $additionalFilters = [],
        $itensFiltered = [],
        $statusList = [],
        $nomeFiltro = null
    ) {
        $this->filters = $filters;
        $this->additionalFilters = $additionalFilters;
        $this->itensFiltered = $itensFiltered;
        $this->statusList = $statusList;
        $this->nomeFiltro = $nomeFiltro;

        // Com uma escola selecionada no seletor do navbar, o escopo global
        // ja restringe os dados — o filtro de escola fica oculto para nao
        // permitir uma selecao conflitante.
        if (app(EscolaContext::class)->filtrar()) {
            $this->filters = $this->removerFiltroEscola($this->filters);
            $this->additionalFilters = $this->removerFiltroEscola($this->additionalFilters);
            $this->itensFiltered = $this->removerFiltroEscola($this->itensFiltered);
        }
    }

    private function removerFiltroEscola($items)
    {
        if (! is_iterable($items)) {
            return $items;
        }

        return collect($items)
            ->reject(fn ($item) => is_array($item) && ($item['name'] ?? null) === 'escola_id')
            ->all();
    }

    public function render()
    {
        return view('components.filter');
    }
}
