@props(['additionalFilters' => [], 'filters' => [], 'itensFiltered' => [], 'nomeFiltro' => null])

@php
    $filtroUrl      = $nomeFiltro ? route($nomeFiltro . '.filtro')         : (url()->current() . '/filtro');
    $removerTudoUrl = $nomeFiltro ? route($nomeFiltro . '.removerFiltro')  : (url()->current() . '/removerfiltro');
@endphp

<div class="card mb-2">
    <div class="card-body p-2">
        <h6 class="text-secondary text-xs mb-1"><i class="fa fa-filter mr-1"></i> Filtros</h6>
        <hr class="m-0 mb-2">

        <form method="GET" action="{{ $filtroUrl }}">
            <div class="row align-items-end">
                @foreach ($filters as $filter)
                    <div class="col-auto flex-grow-1">
                        <label class="text-xs mb-0" for="{{ $filter['name'] }}">{{ $filter['label'] }}</label>
                        @if (isset($filter['options']))
                            <select class="form-control form-control-sm"
                                id="{{ $filter['name'] }}"
                                name="{{ $filter['name'] }}_{{ $filter['typeWhere'] ?? 'where' }}">
                                @foreach ($filter['options'] as $option)
                                    <option value="{{ $option['value'] }}"
                                        {{ ($option['selected'] ?? false) ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="text"
                                class="form-control form-control-sm"
                                id="{{ $filter['name'] }}"
                                name="{{ $filter['name'] }}_{{ $filter['typeWhere'] ?? 'where' }}"
                                placeholder="{{ $filter['label'] }}"
                                value="{{ $filter['valor'] ?? '' }}">
                        @endif
                    </div>
                @endforeach

                <div class="col-auto d-flex align-items-end gap-1" style="gap:6px;">
                    @if (count($additionalFilters) > 0)
                        <button type="button" id="toggle-filters"
                            class="btn btn-sm btn-outline-secondary"
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
                        <div class="col-auto flex-grow-1">
                            <label class="text-xs mb-0" for="{{ $filter['name'] }}">{{ $filter['label'] }}</label>
                            @if (isset($filter['options']))
                                <select class="form-control form-control-sm"
                                    id="{{ $filter['name'] }}"
                                    name="{{ $filter['name'] }}_{{ $filter['typeWhere'] ?? 'where' }}">
                                    @foreach ($filter['options'] as $option)
                                        <option value="{{ $option['value'] }}"
                                            {{ ($option['selected'] ?? false) ? 'selected' : '' }}>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text"
                                    class="form-control form-control-sm"
                                    id="{{ $filter['name'] }}"
                                    name="{{ $filter['name'] }}_{{ $filter['typeWhere'] ?? 'where' }}"
                                    placeholder="{{ $filter['label'] }}"
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
                        <form action="{{ $removerTudoUrl }}" method="GET" class="d-inline">
                            <input type="hidden" name="atributo" value="{{ $filter['name'] }}">
                            <button type="submit" class="badge badge-secondary border-0 font-weight-normal"
                                style="cursor:pointer;">
                                <i class="fa fa-times mr-1"></i>{{ $filter['nomeExibir'] }}:
                                <b>{{ $filter['valor'] }}</b>
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
    $(document).ready(function () {
        $('#toggle-filters').tooltip();
        $('#toggle-filters').on('click', function () {
            var $af = $('#additional-filters');
            $af.slideToggle('fast');
            var open = $af.is(':visible');
            $(this).html(open ? '<i class="fa fa-angle-double-up"></i>' : '<i class="fa fa-angle-double-down"></i>');
            $(this).attr('title', open ? 'Ocultar filtros adicionais' : 'Exibir filtros adicionais')
                   .tooltip('dispose').tooltip();
        });
    });
</script>
