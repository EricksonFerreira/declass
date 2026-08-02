<?php

namespace App\View\Components;

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
        $statusList = []
    ) {
        $this->filters = $filters;
        $this->additionalFilters = $additionalFilters;
        $this->itensFiltered = $itensFiltered;
        $this->statusList = $statusList;
    }

    public function render()
    {
        return view('components.filter');
    }
}
