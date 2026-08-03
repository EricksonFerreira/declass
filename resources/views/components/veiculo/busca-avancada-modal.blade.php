@props([
    'prefix' => 'veiculo',
    'title' => 'Buscar Veículo',
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

<style>
    .veiculo-busca-modal__modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);
    }

    .veiculo-busca-modal__header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #fff;
        border-bottom: 0;
        position: relative;
    }

    .veiculo-busca-modal__header::after {
        content: '';
        position: absolute;
        inset: auto 0 0 0;
        height: 3px;
        background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 55%, #22c55e 100%);
    }

    .veiculo-busca-modal__eyebrow {
        display: block;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(255,255,255,.72);
        margin-bottom: .15rem;
    }

    .veiculo-busca-modal__title {
        margin: 0;
        font-weight: 800;
        line-height: 1.1;
        color: #fff;
    }

    .veiculo-busca-modal__subtitle {
        color: rgba(255,255,255,.78);
        font-size: .88rem;
    }

    .veiculo-busca-modal__body {
        background: #f8fafc;
        padding: 1rem;
    }

    .veiculo-busca-modal__panel {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .veiculo-busca-modal__panel-header {
        padding: .9rem 1rem;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        border-bottom: 1px solid #e2e8f0;
    }

    .veiculo-busca-modal__panel-title {
        font-size: .84rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #475569;
        margin: 0;
    }

    .veiculo-busca-modal__table-wrap {
        max-height: min(62vh, 540px);
        overflow: auto;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
    }

    .veiculo-busca-modal__table-wrap table {
        margin-bottom: 0;
    }

    .veiculo-busca-modal__table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #f8fafc !important;
        box-shadow: 0 1px 0 rgba(0,0,0,.04);
    }

    .veiculo-busca-modal__toolbar .btn {
        border-radius: 10px;
        font-weight: 700;
    }
</style>

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content veiculo-busca-modal__modal-content">
            <div class="modal-header veiculo-busca-modal__header py-3">
                <div class="d-flex align-items-start">
                    <div class="vehicle-modal__icon mr-3" style="background: rgba(255,255,255,.12); color:#fff;">
                        <i class="fas fa-search"></i>
                    </div>
                    <div>
                        <span class="veiculo-busca-modal__eyebrow">Consulta de frota</span>
                        <h5 class="modal-title veiculo-busca-modal__title" id="{{ $modalId }}Label">{{ $title }}</h5>
                        <small class="veiculo-busca-modal__subtitle">Use os filtros para localizar o veículo e selecione com um clique.</small>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body veiculo-busca-modal__body">
                <div class="veiculo-busca-modal__panel mb-3">
                    <div class="veiculo-busca-modal__panel-header d-flex align-items-center justify-content-between">
                        <h6 class="veiculo-busca-modal__panel-title mb-0"><i class="fas fa-filter mr-1"></i> Pesquisa</h6>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body py-3">
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
                            <div class="col-md-4 d-flex align-items-end form-group mb-0 veiculo-busca-modal__toolbar" style="gap:.5rem;">
                                <button type="button" class="btn btn-primary btn-sm" id="{{ $prefix }}_btnAplicarFiltroVeiculo">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="{{ $prefix }}_btnLimparFiltroVeiculo">
                                    <i class="fas fa-eraser mr-1"></i> Limpar
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="toggleAvancado{{ $prefix }}()">
                                    <i class="fas fa-sliders-h mr-1"></i> Filtros avançados
                                </button>
                            </div>
                        </div>

                        <div id="{{ $prefix }}_filtrosAvancados" class="row" style="display:none;margin-top:1rem;padding-top:1rem;border-top:1px solid #e2e8f0;">
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
                <div id="{{ $prefix }}_modalVeiculoResultados" class="veiculo-busca-modal__table-wrap"></div>
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
