@props(['additionalFilters' => [], 'filters' => [], 'itensFiltered' => [], 'nomeFiltro' => null])

@php
    $filtroUrl      = $nomeFiltro ? route($nomeFiltro . '.filtro')         : (url()->current() . '/filtro');
    $removerTudoUrl = $nomeFiltro ? route($nomeFiltro . '.removerFiltro')  : (url()->current() . '/removerfiltro');
    $usesSelect2 = false;
    foreach (array_merge($filters, $additionalFilters) as $f) {
        if (isset($f['options']) && (isset($f['multiple']) && $f['multiple'])) {
            $usesSelect2 = true;
            break;
        }
    }
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
                            <select 
                                id="{{ $filter['name'] }}"
                                @if(isset($filter['multiple']) && $filter['multiple'])
                                   class="filter-select2" data-placeholder="{{ $filter['placeholder'] ?? $filter['label'] }}" name="{{ $filter['name'] }}_{{ $filter['typeWhere'] ?? 'where' }}[]" multiple
                                @else
                                 class="form-control form-control-sm" name="{{ $filter['name'] }}_{{ $filter['typeWhere'] ?? 'where' }}"
                                @endif
                                >
                                @foreach ($filter['options'] as $option)
                                    <option value="{{ $option['value'] }}"
                                        {{ ($option['selected'] ?? false) ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            @php($inputType = in_array($filter['type'] ?? 'text', ['text', 'date', 'number']) ? ($filter['type'] ?? 'text') : 'text')
                            <input type="{{ $inputType }}"
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
                                <select id="{{ $filter['name'] }}"
                                    @if(isset($filter['multiple']) && $filter['multiple'])
                                        class="filter-select2" data-placeholder="{{ $filter['placeholder'] ?? $filter['label'] }}" name="{{ $filter['name'] }}_{{ $filter['typeWhere'] ?? 'where' }}[]" multiple
                                    @else
                                        class="form-control form-control-sm"  name="{{ $filter['name'] }}_{{ $filter['typeWhere'] ?? 'where' }}"
                                    @endif
                                    >
                                    @foreach ($filter['options'] as $option)
                                        <option value="{{ $option['value'] }}"
                                            {{ ($option['selected'] ?? false) ? 'selected' : '' }}>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                @php($inputType = in_array($filter['type'] ?? 'text', ['text', 'date', 'number']) ? ($filter['type'] ?? 'text') : 'text')
                                <input type="{{ $inputType }}"
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
                            <input type="hidden" name="atributo" value="{{ isset($filter['name']) ? $filter['name'] : '' }}">
                            <button type="submit" class="badge badge-secondary border-0 font-weight-normal"
                                style="cursor:pointer;">
                                <i class="fa fa-times mr-1"></i>{{  isset($filter['nomeExibir']) ? $filter['nomeExibir'] : '' }}:
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

@if($usesSelect2)
    @once('css')
        @push('css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.5.2/select2-bootstrap4.min.css" rel="stylesheet" />
        <style>
            .select2-container { min-width: 120px; }
            .select2-container .select2-selection--multiple { min-height: 31px; }
            .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
                font-size: .78rem;
            }
        </style>
        @endpush
    @endonce

    @once('js')
        @push('js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
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
    @endonce
@endif
