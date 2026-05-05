@props([
    'prefix' => 'veiculo',
    'title' => 'Buscar veiculo',
    'buscarUrl' => null,
    'triggerId' => null,
    'targetSelectId' => null,
    'hiddenInputId' => null,
    'displayInputId' => null,
    'clearBtnId' => null,
    'autoFillPlacaSelector' => null,
    'autoFillKmsSelector' => null,
    'autoFillNivelSelector' => null,
    'filtroMarcas' => [],
    'filtroGrupos' => [],
    'filtroTipos' => [],
    'filtroStatus' => [],
])

@php
    $buscarUrl = $buscarUrl ?: route('veiculo.buscar');
    $modalId = $prefix . '_modalBuscarVeiculo';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="fas fa-search mr-1"></i>{{ $title }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card mb-2">
                    <div class="card-body py-2">
                        <div class="row align-items-end">
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Placa</label>
                                <input type="text" id="{{ $prefix }}_fv_placa" class="form-control form-control-sm" placeholder="ABC-1234">
                            </div>
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Nome</label>
                                <input type="text" id="{{ $prefix }}_fv_nome" class="form-control form-control-sm" placeholder="Ex: Corolla">
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Marca</label>
                                <select id="{{ $prefix }}_fv_marca_id" class="form-control form-control-sm">
                                    <option value="">Todas as marcas</option>
                                    @foreach($filtroMarcas as $id => $nome)
                                        <option value="{{ $id }}">{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Grupo de Frota</label>
                                <select id="{{ $prefix }}_fv_grupo_frota_id" class="form-control form-control-sm">
                                    <option value="">Todos os grupos</option>
                                    @foreach($filtroGrupos as $id => $nome)
                                        <option value="{{ $id }}">{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Status</label>
                                <select id="{{ $prefix }}_fv_status_veiculo_id" class="form-control form-control-sm">
                                    <option value="">Todos os status</option>
                                    @foreach($filtroStatus as $id => $nome)
                                        <option value="{{ $id }}">{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end form-group mb-0 gap-1">
                                <button type="button" class="btn btn-primary btn-sm" id="{{ $prefix }}_btnAplicarFiltroVeiculo">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="{{ $prefix }}_btnLimparFiltroVeiculo">
                                    <i class="fas fa-eraser mr-1"></i> Limpar
                                </button>
<button type="button" class="btn btn-outline-info btn-sm" onclick="toggleAvancado{{ $prefix }}()">
    <i class="fas fa-sliders-h"></i> <span>Filtros Avançados</span>
</button>
                            </div>
                        </div>

                        <div id="{{ $prefix }}_filtrosAvancados" class="row" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid #e2e8f0;">
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Chassi</label>
                                <input type="text" id="{{ $prefix }}_fv_chassi" class="form-control form-control-sm" placeholder="Chassi">
                            </div>
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Cor</label>
                                <input type="text" id="{{ $prefix }}_fv_cor" class="form-control form-control-sm" placeholder="Preto">
                            </div>
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Modelo (texto)</label>
                                <input type="text" id="{{ $prefix }}_fv_modelo" class="form-control form-control-sm" placeholder="Modelo">
                            </div>
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Tipo de Frota</label>
                                <select id="{{ $prefix }}_fv_tipo_frota_id" class="form-control form-control-sm">
                                    <option value="">Todos os tipos</option>
                                    @foreach($filtroTipos as $id => $nome)
                                        <option value="{{ $id }}">{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Ano modelo de</label>
                                <input type="number" id="{{ $prefix }}_fv_ano_de" class="form-control form-control-sm" placeholder="2015" min="1990" max="{{ date('Y')+1 }}">
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Ano modelo ate</label>
                                <input type="number" id="{{ $prefix }}_fv_ano_ate" class="form-control form-control-sm" placeholder="{{ date('Y') }}" min="1990" max="{{ date('Y')+1 }}">
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">KMs ate</label>
                                <input type="number" id="{{ $prefix }}_fv_kms_ate" class="form-control form-control-sm" placeholder="100000" min="0">
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Ativo</label>
                                <select id="{{ $prefix }}_fv_ativo" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="1">Sim</option>
                                    <option value="0">Nao</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                @push('js')
                <script>
                    window['toggleAvancado{{ $prefix }}'] = function() {
                        var el = document.getElementById('{{ $prefix }}_filtrosAvancados');
                        if (el) el.style.display = el.style.display === 'none' ? '' : 'none';
                    };
                </script>
                @endpush

                <div id="{{ $prefix }}_modalVeiculoLoading" class="text-center d-none py-3">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Buscando...
                </div>
                <div id="{{ $prefix }}_modalVeiculoResultados"></div>
            </div>
        </div>
    </div>
</div>

@once
    <script src="{{ asset('js/veiculo/busca-modal.js') }}"></script>
@endonce

@if($triggerId)
    @push('js')
        <script>
            (function () {
                if (!window.VeiculoBuscaModal) return;
                    window.VeiculoBuscaModal.init({
                        prefix: @json($prefix),
                        buscarUrl: @json($buscarUrl),
                        modalId: @json($modalId),
                        triggerId: @json($triggerId),
                        targetSelectId: @json($targetSelectId),
                        hiddenInputId: @json($hiddenInputId),
                        displayInputId: @json($displayInputId),
                        clearBtnId: @json($clearBtnId),
                        autoFillPlacaSelector: @json($autoFillPlacaSelector),
                        autoFillKmsSelector: @json($autoFillKmsSelector ?? null),
                        autoFillNivelSelector: @json($autoFillNivelSelector ?? null),
                    });
            }());
        </script>
    @endpush
@endif

