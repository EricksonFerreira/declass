@props(['additionalFilters' => [], 'filters' => [], 'itensFiltered' => [], 'nomeFiltro' => null, 'statusList' => []])

@php
    $filtroUrl      = $nomeFiltro && \Illuminate\Support\Facades\Route::has($nomeFiltro . '.filtro')
        ? route($nomeFiltro . '.filtro')
        : (url()->current() . '/filtro');
    $removerTudoUrl = $nomeFiltro && \Illuminate\Support\Facades\Route::has($nomeFiltro . '.removerFiltro')
        ? route($nomeFiltro . '.removerFiltro')
        : (url()->current() . '/removerfiltro');
    $usesSelect2 = false;
    $filters = is_iterable($filters) ? $filters : [];
    $additionalFilters = is_iterable($additionalFilters) ? $additionalFilters : [];
    $itensFiltered = is_iterable($itensFiltered) ? $itensFiltered : [];
    $statusList = is_iterable($statusList) ? $statusList : [];
    $statusLabels = collect($statusList ?? [])->mapWithKeys(function ($status) {
        return [(string) data_get($status, 'id') => (string) data_get($status, 'nome')];
    })->all();

    $normalizeFilterOptions = function ($options): array {
        if (!is_iterable($options)) {
            return [];
        }

        $optionsArray = is_array($options) ? $options : iterator_to_array($options);
        $isList = array_is_list($optionsArray);
        $normalized = [];

        foreach ($optionsArray as $key => $option) {
            if (is_array($option)) {
                $normalized[] = [
                    'value' => $option['value'] ?? $option['id'] ?? $key,
                    'label' => $option['label'] ?? $option['nome'] ?? $option['name'] ?? (string) ($option['value'] ?? $option['id'] ?? $key),
                    'selected' => (bool) ($option['selected'] ?? false),
                ];
                continue;
            }

            $normalized[] = [
                'value' => $isList ? $option : $key,
                'label' => (string) $option,
                'selected' => false,
            ];
        }

        return $normalized;
    };

    foreach (array_merge($filters, $additionalFilters) as $f) {
        if (isset($f['options'])) {
            $usesSelect2 = true;
            break;
        }
    }

    $appliedFiltersCount = count(array_filter($itensFiltered, function ($f) {
        return !empty($f['valor']);
    }));
@endphp

<div class="card mb-2 filter-card">
    <div class="card-body p-2">
        <h6 class="filter-heading text-secondary text-xs mb-1"><i class="fa fa-filter mr-1"></i> Filtros</h6>
        <hr class="filter-heading m-0 mb-2">

        <form method="GET" action="{{ $filtroUrl }}">
            <div class="row align-items-end">
                @foreach ($filters as $filter)
                    @php
                        $hasOptions = isset($filter['options']);
                        $isMultiple = !empty($filter['multiple']);
                        $filterName = $filter['name'] ?? '';
                        $filterLabel = $filter['label'] ?? '';
                        $filterWhere = $filter['typeWhere'] ?? 'where';
                        $filterType = in_array($filter['type'] ?? 'text', ['text', 'date', 'number']) ? ($filter['type'] ?? 'text') : 'text';
                    @endphp
                    <div class="col-auto flex-grow-1 filter-field">
                        <label class="text-xs mb-0" for="{{ $filterName }}">{{ $filterLabel }}</label>
                        @if ($hasOptions)
                            <select
                                id="{{ $filterName }}"
                                class="filter-select2 form-control form-control-sm"
                                @if ($isMultiple)
                                    data-placeholder="{{ $filter['placeholder'] ?? $filterLabel }}"
                                    name="{{ $filterName }}_{{ $filterWhere }}[]"
                                    multiple
                                @else
                                    name="{{ $filterName }}_{{ $filterWhere }}"
                                @endif
                            >
                                @foreach ($normalizeFilterOptions($filter['options'] ?? []) as $option)
                                    <option value="{{ $option['value'] }}"
                                        {{ $option['selected'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $filterType }}"
                                class="form-control form-control-sm"
                                id="{{ $filterName }}"
                                name="{{ $filterName }}_{{ $filterWhere }}"
                                placeholder="{{ $filterLabel }}"
                                value="{{ $filter['valor'] ?? '' }}">
                        @endif
                    </div>
                @endforeach

                <div class="col-auto d-flex align-items-end" style="gap:6px;">
                    <button type="button" id="toggle-filters-mobile"
                        class="btn btn-sm btn-outline-secondary d-md-none"
                        aria-expanded="false" aria-label="Abrir filtros">
                        <i class="fa fa-filter mr-1"></i> Filtros
                        @if ($appliedFiltersCount > 0)
                            <span class="badge badge-primary ml-1">{{ $appliedFiltersCount }}</span>
                        @endif
                    </button>
                    @if (count($additionalFilters) > 0)
                        <button type="button" id="toggle-filters"
                            class="btn btn-sm btn-outline-secondary filter-toggle-desktop"
                            title="Filtros adicionais" data-toggle="tooltip">
                            <i class="fa fa-angle-double-down"></i>
                        </button>
                    @endif
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-search mr-1"></i> Filtrar
                    </button>
                </div>
            </div>

            @if (count($additionalFilters) > 0)
                <div id="additional-filters" class="row mt-2 border rounded p-2" style="display:none;">
                    <p class="col-12 mb-1 text-xs text-secondary text-bold">Filtros adicionais:</p>
                    @foreach ($additionalFilters as $filter)
                        @php
                            $hasOptions = isset($filter['options']);
                            $isMultiple = !empty($filter['multiple']);
                            $filterName = $filter['name'] ?? '';
                            $filterLabel = $filter['label'] ?? '';
                            $filterWhere = $filter['typeWhere'] ?? 'where';
                            $filterType = in_array($filter['type'] ?? 'text', ['text', 'date', 'number']) ? ($filter['type'] ?? 'text') : 'text';
                        @endphp
                        <div class="col-auto flex-grow-1 filter-field">
                            <label class="text-xs mb-0" for="{{ $filterName }}">{{ $filterLabel }}</label>
                            @if ($hasOptions)
                                <select
                                    id="{{ $filterName }}"
                                    class="filter-select2 form-control form-control-sm"
                                    @if ($isMultiple)
                                        data-placeholder="{{ $filter['placeholder'] ?? $filterLabel }}"
                                        name="{{ $filterName }}_{{ $filterWhere }}[]"
                                        multiple
                                    @else
                                        name="{{ $filterName }}_{{ $filterWhere }}"
                                    @endif
                                >
                                    @foreach ($normalizeFilterOptions($filter['options'] ?? []) as $option)
                                        <option value="{{ $option['value'] }}"
                                            {{ $option['selected'] ? 'selected' : '' }}>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $filterType }}"
                                    class="form-control form-control-sm"
                                    id="{{ $filterName }}"
                                    name="{{ $filterName }}_{{ $filterWhere }}"
                                    placeholder="{{ $filterLabel }}"
                                    value="{{ $filter['valor'] ?? '' }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </form>

        {{-- Filtros aplicados --}}
        @if (count($itensFiltered) > 0)
            <div class="mt-2 d-flex flex-wrap align-items-center" style="gap:6px;">
                <span class="text-xs text-secondary text-bold">Aplicados:</span>
                @foreach ($itensFiltered as $filter)
                    @if ($filter['valor'])
                        @php
                            $valorExibido = $filter['valor'];
                            if (($filter['name'] ?? '') === 'status_movimentacao_id') {
                                $valorExibido = $statusLabels[(string) $filter['valor']] ?? $filter['valor'];
                            }
                        @endphp
                        <form action="{{ $removerTudoUrl }}" method="GET" class="d-inline">
                            <input type="hidden" name="atributo" value="{{ isset($filter['name']) ? $filter['name'] : '' }}">
                            <button type="submit" class="badge badge-secondary border-0 font-weight-normal"
                                style="cursor:pointer;">
                                <i class="fa fa-times mr-1"></i>{{  isset($filter['nomeExibir']) ? $filter['nomeExibir'] : '' }}:
                                <b>{{ $valorExibido }}</b>
                            </button>
                        </form>
                    @endif
                @endforeach
                <form action="{{ $removerTudoUrl }}" method="GET" class="d-inline">
                    <button type="submit" class="badge badge-danger border-0 font-weight-normal"
                        style="cursor:pointer;">
                        <i class="fa fa-times mr-1"></i> Limpar tudo
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    function initToggleFilters() {
        var btn = document.getElementById('toggle-filters');
        var af  = document.getElementById('additional-filters');
        if (!btn || !af) return;

        // Bootstrap tooltip via jQuery (carregado pelo AdminLTE depois do body)
        function jqReady(fn) {
            if (typeof window.$ !== 'undefined') { fn(); }
            else { setTimeout(function () { jqReady(fn); }, 50); }
        }
        jqReady(function () { $(btn).tooltip(); });

        btn.addEventListener('click', function () {
            var visible = af.style.display !== 'none' && af.style.display !== '';
            if (visible) {
                af.style.display = 'none';
            } else {
                af.style.display = '';
            }
            var nowVisible = af.style.display !== 'none' && af.style.display !== '';
            btn.innerHTML = nowVisible
                ? '<i class="fa fa-angle-double-up"></i>'
                : '<i class="fa fa-angle-double-down"></i>';
            btn.setAttribute('title', nowVisible ? 'Ocultar filtros adicionais' : 'Exibir filtros adicionais');
            jqReady(function () { $(btn).tooltip('dispose').tooltip(); });
        });
    }

    // Mobile: botão "Filtros" expande/colapsa todos os campos do form
    function initMobileFilterToggle() {
        var btn = document.getElementById('toggle-filters-mobile');
        if (!btn) return;
        var form = btn.closest('form');
        var af = document.getElementById('additional-filters');
        var arrow = document.getElementById('toggle-filters');
        if (!form) return;

        btn.addEventListener('click', function () {
            var open = form.classList.toggle('filters-open');
            if (af) af.style.display = open ? '' : 'none';
            if (arrow) {
                arrow.innerHTML = open
                    ? '<i class="fa fa-angle-double-up"></i>'
                    : '<i class="fa fa-angle-double-down"></i>';
                arrow.setAttribute('title', open ? 'Ocultar filtros adicionais' : 'Exibir filtros adicionais');
            }
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    function init() {
        initToggleFilters();
        initMobileFilterToggle();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

@if($usesSelect2)
    @push('css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
        <style>
            .select2-container { min-width: 120px; }
            .select2-container .select2-selection--multiple { min-height: 31px; }
            .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
                font-size: .78rem;
            }
        </style>
    @endpush
    @push('js')
        <script src="{{ asset('js/select2.min.js') }}"></script>
        <script>
        (function initFilterSelect2() {
            function applySelect2() {
                document.querySelectorAll('.filter-select2:not(.select2-hidden-accessible)').forEach(function(el) {
                    var $el = $(el);
                    var placeholder = $el.data('placeholder') || '';
                    $el.select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        placeholder: placeholder,
                        allowClear: true,
                        closeOnSelect: !el.multiple
                    });
                    // ao mudar, submete o form automaticamente
                    $el.on('change', function() {
                        $(this).closest('form').submit();
                    });
                });
            }
            $(document).ready(applySelect2);
        })();
        </script>
    @endpush
@endif
