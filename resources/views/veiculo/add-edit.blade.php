@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $mostrarAbaDisponibilidade =
        !isset($veiculo) ||
        empty($veiculo->local_disponibilidade) ||
        empty($veiculo->data_hora_disponibilidade);
    $chavesErro = array_keys($errors->messages());

    $qtdTiposFrota    = is_countable($tipoFrotas ?? null)    ? count($tipoFrotas)    : 0;
    $qtdGruposFrota   = is_countable($grupoFrotas ?? null)   ? count($grupoFrotas)   : 0;
    $qtdMarcas        = is_countable($marcas ?? null)        ? count($marcas)        : 0;
    $qtdModelos       = is_countable($modelos ?? null)       ? count($modelos)       : 0;
    $qtdStatusVeiculo = is_countable($statusVeiculos ?? null)? count($statusVeiculos): 0;
    $qtdEstacoes      = is_countable($estacoes ?? null)      ? count($estacoes)      : 0;

    $faltasPreRequisitosVeiculo = [];
    if ($qtdTiposFrota === 0)    $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um tipo de frota ativo.';
    if ($qtdGruposFrota === 0)   $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um grupo de frota ativo.';
    if ($qtdMarcas === 0)        $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos uma marca ativa.';
    if ($qtdModelos === 0)       $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um modelo ativo.';
    if ($qtdStatusVeiculo === 0) $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um status de veículo ativo.';
    if ($mostrarAbaDisponibilidade && $qtdEstacoes === 0)
        $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos uma estação ativa para definir a disponibilidade inicial.';

    $acoesPreRequisitosVeiculo = [];
    if ($qtdTiposFrota === 0 && Route::has('tipo_frota.create'))
        $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar tipo de frota',   'url' => route('tipo_frota.create')];
    if ($qtdGruposFrota === 0 && Route::has('grupo_frota.create'))
        $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar grupo de frota',  'url' => route('grupo_frota.create')];
    if ($qtdMarcas === 0 && Route::has('marca.create'))
        $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar marca',           'url' => route('marca.create')];
    if ($qtdModelos === 0 && Route::has('modelo.create'))
        $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar modelo',          'url' => route('modelo.create')];
    if ($qtdStatusVeiculo === 0 && Route::has('status_veiculo.create'))
        $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar status de veículo','url' => route('status_veiculo.create')];
    if ($mostrarAbaDisponibilidade && $qtdEstacoes === 0 && Route::has('estacao.create'))
        $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar estação',          'url' => route('estacao.create')];

    $podeCriarVeiculo     = empty($faltasPreRequisitosVeiculo);
    $bloquearSalvarVeiculo= $metodo === 'POST' && !$podeCriarVeiculo;

    // Determine start step from validation errors
    $errosIdent  = ['ano_fabricacao','ano_modelo','placa_licenca','chassi','cor','numero_motor','renavam'];
    $errosDatas  = ['data_placa_licenca','data_compra','data_validade','data_proxima_manutencao','data_proxima_inspecao','kms','limite_kms','proxima_revisao_kms','proxima_manutencao_kms'];
    $errosDisp   = ['local_disponibilidade','data_hora_disponibilidade'];
    $startStep   = 1;
    if (count(array_intersect($chavesErro, $errosIdent)))  $startStep = 2;
    elseif (count(array_intersect($chavesErro, $errosDatas)))  $startStep = 3;
    elseif (count(array_intersect($chavesErro, $errosDisp)))   $startStep = 4;
@endphp

@if ($errors->any())
    <div class="alert alert-danger col-xl-11 col-12">
        <ul class="mb-0 pl-3">
            @foreach (array_unique($errors->all()) as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="col-xl-11 col-12 px-0">
    <x-pre-requisitos-alerta
        title="Pré-requisitos pendentes para adicionar veículo"
        :chips="[
            ['label' => 'Tipos de Frota',   'value' => $qtdTiposFrota],
            ['label' => 'Grupos de Frota',  'value' => $qtdGruposFrota],
            ['label' => 'Marcas',           'value' => $qtdMarcas],
            ['label' => 'Modelos',          'value' => $qtdModelos],
            ['label' => 'Status Veículo',   'value' => $qtdStatusVeiculo],
            ['label' => 'Estações',         'value' => $qtdEstacoes],
        ]"
        :missing-items="$faltasPreRequisitosVeiculo"
        :action-links="$acoesPreRequisitosVeiculo"
    />

    <div class="card card-veiculo shadow-sm border-0">
        <div class="card-header card-header-veiculo border-0">
            <div>
                <h4 class="mb-1">{{ $titulo }}</h4>
                <p class="mb-0 text-muted">Configure os dados gerais, identificação, datas/km e disponibilidade inicial do veículo.</p>
            </div>
        </div>

        <div class="card-body p-4">

            {{-- ── WIZARD STEPS ── --}}
            <div class="vc-wizard-steps mb-4">
                @php
                    $steps = [
                        1 => ['icon' => 'fa-car',         'label' => 'Geral'],
                        2 => ['icon' => 'fa-id-card',     'label' => 'Identificação'],
                        3 => ['icon' => 'fa-calendar-alt','label' => 'Datas e Km'],
                        4 => ['icon' => 'fa-map-marker-alt','label' => 'Disponibilidade'],
                    ];
                    if (!$mostrarAbaDisponibilidade) unset($steps[4]);
                @endphp
                @foreach($steps as $n => $s)
                    <button type="button" class="vc-step-btn" data-step="{{ $n }}">
                        <span class="vc-step-circle"><i class="fas {{ $s['icon'] }}"></i></span>
                        <span class="vc-step-label">{{ $s['label'] }}</span>
                    </button>
                    @if(!$loop->last)
                        <div class="vc-step-line"></div>
                    @endif
                @endforeach
            </div>

            <form id="formulario" method="POST" action="{{ $rota }}">
                @if ($metodo == 'PUT') @method('PUT') @endif
                @csrf

                {{-- ── STEP 1: GERAL ── --}}
                <div class="vc-step-pane" data-pane="1">
                    <div class="veiculo-section-title"><i class="fas fa-car mr-1"></i>Informações Gerais</div>
                    <div class="form-row">
                        @isset($veiculo)
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome" value="{{ old('nome', $veiculo->nome ?? '') }}" disabled readonly />
                            </div>
                        </div>
                        @endisset
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-select name="tipo_frota_id" label="Tipo de Frota" :options="$tipoFrotas"
                                    placeholder="Selecione um valor"
                                    :selected="old('tipo_frota_id', $veiculo->tipo_frota_id ?? null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-12">
                            <div class="form-group form-group-veiculo">
                                <x-form-select name="grupo_frota_id" label="Grupo de Frota" :options="$grupoFrotas"
                                    placeholder="Selecione um valor"
                                    :selected="old('grupo_frota_id', $veiculo->grupo_frota_id ?? null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-select name="marca_id" label="Marca" :options="$marcas"
                                    placeholder="Selecione um valor"
                                    :selected="old('marca_id', $veiculo->marca_id ?? null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-select name="modelo_id" label="Modelo" :options="$modelos"
                                    placeholder="Selecione um valor"
                                    :selected="old('modelo_id', $veiculo->modelo_id ?? null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-select id="status_veiculo_id" name="status_veiculo_id" label="Status"
                                    :options="$statusVeiculos" placeholder="Selecione um valor"
                                    :selected="old('status_veiculo_id', isset($veiculo->status_veiculo_id) && $veiculo->status_veiculo_id ? $veiculo->status_veiculo_id : ($defaultStatusId ?? null))"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 d-none" id="div_data_vendido">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="date" class="form-control form-control-sm" id="data_vendido"
                                    name="data_vendido" label="Data de Venda"
                                    value="{{ old('data_vendido', $veiculo->data_vendido ?? '') }}" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 2: IDENTIFICAÇÃO ── --}}
                <div class="vc-step-pane d-none" data-pane="2">
                    <div class="veiculo-section-title"><i class="fas fa-id-card mr-1"></i>Identificação do Veículo</div>
                    <div class="form-row">
                        <div class="col-lg-3 col-md-4">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="number" class="form-control form-control-sm" id="ano_fabricacao"
                                    name="ano_fabricacao" label="Ano de Fabricação" min="1900"
                                    value="{{ old('ano_fabricacao', $veiculo->ano_fabricacao ?? date('Y')) }}" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="number" class="form-control form-control-sm" id="ano_modelo"
                                    name="ano_modelo" min="1900" label="Ano do Modelo"
                                    value="{{ old('ano_modelo', $veiculo->ano_modelo ?? date('Y')) }}" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="text" class="form-control form-control-sm" id="cor" name="cor"
                                    label="Cor" value="{{ old('cor', $veiculo->cor ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="text" class="form-control form-control-sm" id="placa_licenca"
                                    name="placa_licenca" label="Placa"
                                    value="{{ old('placa_licenca', $veiculo->placa_licenca ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="text" class="form-control form-control-sm" id="renavam"
                                    name="renavam" label="RENAVAM"
                                    value="{{ old('renavam', $veiculo->renavam ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="text" class="form-control form-control-sm" id="chassi" name="chassi"
                                    label="Chassi" value="{{ old('chassi', $veiculo->chassi ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="text" class="form-control form-control-sm" id="numero_motor"
                                    name="numero_motor" label="Número do Motor"
                                    value="{{ old('numero_motor', $veiculo->numero_motor ?? '') }}" required />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 3: DATAS E KM ── --}}
                <div class="vc-step-pane d-none" data-pane="3">
                    <div class="veiculo-section-title"><i class="fas fa-calendar-alt mr-1"></i>Datas Importantes</div>
                    <div class="form-row">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="date" class="form-control form-control-sm" id="data_placa_licenca"
                                    name="data_placa_licenca" label="Emissão da Placa/Licença"
                                    value="{{ old('data_placa_licenca', $veiculo->data_placa_licenca ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="date" class="form-control form-control-sm" id="data_compra"
                                    name="data_compra" label="Data de Compra"
                                    value="{{ old('data_compra', $veiculo->data_compra ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="date" class="form-control form-control-sm" id="data_validade"
                                    name="data_validade" label="Validade da Documentação/Licença"
                                    value="{{ old('data_validade', $veiculo->data_validade ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="date" class="form-control form-control-sm" id="data_proxima_manutencao"
                                    name="data_proxima_manutencao" label="Próxima Manutenção"
                                    value="{{ old('data_proxima_manutencao', $veiculo->data_proxima_manutencao ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="date" class="form-control form-control-sm" id="data_proxima_inspecao"
                                    name="data_proxima_inspecao" label="Próxima Inspeção"
                                    value="{{ old('data_proxima_inspecao', $veiculo->data_proxima_inspecao ?? '') }}" required />
                            </div>
                        </div>
                    </div>

                    <div class="veiculo-section-title"><i class="fas fa-tachometer-alt mr-1"></i>Métricas e Limites de Km</div>
                    <div class="form-row">
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="number" class="form-control form-control-sm" id="kms" name="kms"
                                    label="Km Atual" value="{{ old('kms', $veiculo->kms ?? '') }}" min="0" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="number" class="form-control form-control-sm" id="limite_kms"
                                    name="limite_kms" label="Limite de Km por Aluguel"
                                    value="{{ old('limite_kms', $veiculo->limite_kms ?? '') }}" min="0" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="number" class="form-control form-control-sm" id="proxima_revisao_kms"
                                    name="proxima_revisao_kms" label="Km da Próxima Revisão"
                                    value="{{ old('proxima_revisao_kms', $veiculo->proxima_revisao_kms ?? '') }}" min="0" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="number" class="form-control form-control-sm" id="proxima_manutencao_kms"
                                    name="proxima_manutencao_kms" label="Km da Próxima Manutenção"
                                    value="{{ old('proxima_manutencao_kms', $veiculo->proxima_manutencao_kms ?? '') }}" min="0" required />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 4: DISPONIBILIDADE ── --}}
                @if ($mostrarAbaDisponibilidade)
                <div class="vc-step-pane d-none" data-pane="4">
                    <div class="veiculo-section-title"><i class="fas fa-map-marker-alt mr-1"></i>Disponibilidade Inicial</div>
                    <div class="veiculo-info-tip">
                        <i class="fas fa-info-circle mr-1"></i>
                        Defina em qual estação o veículo inicia e a data/hora inicial de disponibilidade.
                    </div>
                    <div class="form-row mt-2">
                        <div class="col-lg-5 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-select name="local_disponibilidade" label="Estação de origem"
                                    :options="$estacoes" placeholder="Selecione um valor"
                                    :selected="old('local_disponibilidade', $veiculo->local_disponibilidade ?? null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-veiculo">
                                <x-form-input type="datetime-local" class="form-control form-control-sm"
                                    id="data_hora_disponibilidade" name="data_hora_disponibilidade"
                                    label="Data e Hora de Disponibilidade"
                                    value="{{ old('data_hora_disponibilidade', $veiculo->data_hora_disponibilidade ?? '') }}"
                                    required />
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── NAV BUTTONS ── --}}
                <div class="vc-wizard-nav mt-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" id="btnPrev" style="display:none!important;">
                        <i class="fas fa-chevron-left mr-1"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-4" id="btnNext">
                        Próximo <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                    <button type="submit" class="btn btn-success btn-sm px-4 d-none" id="btnSalvar" {{ $bloquearSalvarVeiculo ? 'disabled' : '' }}>
                        <i class="fas fa-save mr-1"></i> Salvar Veículo
                    </button>
                </div>
                @if ($bloquearSalvarVeiculo)
                    <small class="text-danger d-block mt-2 text-center font-weight-semibold">
                        Cadastre os itens obrigatórios acima para liberar a criação do veículo.
                    </small>
                @endif
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card-veiculo { border-radius: 14px; overflow: hidden; }
.card-header-veiculo {
    background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
    padding: 1.1rem 1.5rem;
}
.card-header-veiculo h4 { color: #0f2740; font-weight: 700; }

/* ── Wizard ── */
.vc-wizard-steps {
    display: flex; align-items: center; gap: 0;
    overflow-x: auto; padding-bottom: 4px;
}
.vc-step-btn {
    display: flex; flex-direction: column; align-items: center;
    gap: 4px; background: none; border: none; padding: 0 8px;
    cursor: pointer; flex-shrink: 0;
}
.vc-step-circle {
    width: 38px; height: 38px; border-radius: 50%;
    border: 2px solid #d0daea; background: #f8fafc;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; color: #94a3b8; transition: all .2s;
}
.vc-step-btn.active .vc-step-circle { background: #2563eb; border-color: #2563eb; color: #fff; }
.vc-step-btn.done   .vc-step-circle { background: #ecfdf5; border-color: #10b981; color: #10b981; }
.vc-step-label { font-size: .72rem; font-weight: 600; color: #94a3b8; white-space: nowrap; }
.vc-step-btn.active .vc-step-label { color: #2563eb; }
.vc-step-btn.done   .vc-step-label { color: #10b981; }
.vc-step-line { flex: 1; height: 2px; background: #e5eaf2; min-width: 16px; margin-bottom: 18px; }

/* ── Form ── */
.veiculo-section-title {
    font-size: .96rem; font-weight: 700; color: #21364f;
    letter-spacing: .02em; padding-bottom: 8px;
    border-bottom: 1px solid #e5eaf2; margin-bottom: 14px;
}
.form-group-veiculo { margin-bottom: 1rem; }
.veiculo-info-tip {
    border: 1px solid #dbe6f3; border-radius: 10px;
    background: #f8fbff; color: #334155;
    font-size: 12px; font-weight: 600; padding: 8px 10px; margin-bottom: 10px;
}
.card-veiculo .form-control,
.card-veiculo .form-select {
    border-radius: 10px; border-color: #d8e1ec; min-height: 38px;
}
.card-veiculo .form-control:focus,
.card-veiculo .form-select:focus {
    border-color: #4b80ff;
    box-shadow: 0 0 0 .15rem rgba(75,128,255,.18);
}

@media (max-width:575px) {
    .card-header-veiculo { padding: .85rem; }
    .card-body { padding: .85rem !important; }
    .form-group-veiculo { margin-bottom: .8rem; }
}
</style>
@stop

@section('js')
<script>
$(function () {
    // ── Wizard ──
    const TOTAL_STEPS = {{ $mostrarAbaDisponibilidade ? 4 : 3 }};
    let currentStep = {{ $startStep }};

    function goToStep(n) {
        n = Math.max(1, Math.min(TOTAL_STEPS, n));
        currentStep = n;
        $('.vc-step-pane').addClass('d-none');
        $(`.vc-step-pane[data-pane="${n}"]`).removeClass('d-none');
        $('.vc-step-btn').each(function () {
            const s = parseInt($(this).data('step'));
            $(this).removeClass('active done');
            if (s === n) $(this).addClass('active');
            if (s < n)  $(this).addClass('done');
        });
        $('#btnPrev').css('display', n > 1 ? 'inline-flex' : 'none');
        if (n < TOTAL_STEPS) {
            $('#btnNext').removeClass('d-none');
            $('#btnSalvar').addClass('d-none');
        } else {
            $('#btnNext').addClass('d-none');
            $('#btnSalvar').removeClass('d-none');
        }
    }

    goToStep(currentStep);
    $('#btnNext').on('click', function () { goToStep(currentStep + 1); });
    $('#btnPrev').on('click', function () { goToStep(currentStep - 1); });
    $('.vc-step-btn').on('click', function () { goToStep(parseInt($(this).data('step'))); });

    // ── Grupos por Tipo de Frota ──
    const $tipoFrota  = $('[name="tipo_frota_id"]');
    const $grupoFrota = $('[name="grupo_frota_id"]');
    const selectedGrupo = {{ old('grupo_frota_id', isset($veiculo->grupo_frota_id) ? $veiculo->grupo_frota_id : 'null') }};

    function carregarGrupos(tipoId, selectValor) {
        $grupoFrota.prop('disabled', true).html('<option value="">Carregando...</option>');
        $.get('{{ route('veiculo.gruposPorTipo') }}', { tipo_frota_id: tipoId }, function(grupos) {
            let html = '<option value="">Selecione um valor</option>';
            grupos.forEach(function(g) {
                const sel = selectValor && parseInt(selectValor) === g.id ? ' selected' : '';
                html += `<option value="${g.id}"${sel}>${g.nome}</option>`;
            });
            $grupoFrota.html(html).prop('disabled', false);
        }).fail(function() {
            $grupoFrota.html('<option value="">Selecione um valor</option>').prop('disabled', false);
        });
    }

    $tipoFrota.on('change', function () { carregarGrupos($(this).val(), null); });
    const tipoInicial = $tipoFrota.val();
    if (tipoInicial) carregarGrupos(tipoInicial, selectedGrupo);

    // ── Modelos por Marca ──
    const $marca  = $('[name="marca_id"]');
    const $modelo = $('[name="modelo_id"]');
    const selectedModelo = {{ old('modelo_id', isset($veiculo->modelo_id) ? $veiculo->modelo_id : 'null') }};

    function carregarModelos(marcaId, selectValor) {
        $modelo.prop('disabled', true).html('<option value="">Carregando...</option>');
        $.get('{{ route('veiculo.modelosPorMarca') }}', { marca_id: marcaId }, function(modelos) {
            let html = '<option value="">Selecione um valor</option>';
            modelos.forEach(function(m) {
                const sel = selectValor && parseInt(selectValor) === m.id ? ' selected' : '';
                html += `<option value="${m.id}"${sel}>${m.nome}</option>`;
            });
            $modelo.html(html).prop('disabled', false);
        }).fail(function() {
            $modelo.html('<option value="">Selecione um valor</option>').prop('disabled', false);
        });
    }

    $marca.on('change', function () { carregarModelos($(this).val(), null); });
    const marcaInicial = $marca.val();
    if (marcaInicial) carregarModelos(marcaInicial, selectedModelo);

    // ── Validações de datas e km ──
    (function () {
        const today = new Date().toISOString().slice(0, 10);

        const dataEmissaoPlaca = document.getElementById('data_placa_licenca');   // Emissão da Placa/Licença
        const dataValidade     = document.getElementById('data_validade');         // Validade da Documentação/Licença
        const dataManutencao   = document.getElementById('data_proxima_manutencao');
        const dataInspecao     = document.getElementById('data_proxima_inspecao');
        const kmsAtual         = document.getElementById('kms');
        const proxRevisaoKms   = document.getElementById('proxima_revisao_kms');
        const proxManutKms     = document.getElementById('proxima_manutencao_kms');

        // Validade da Documentação/Licença > Emissão da Placa
        function validarValidade() {
            if (!dataValidade || !dataEmissaoPlaca) return;
            if (dataValidade.value && dataEmissaoPlaca.value && dataValidade.value <= dataEmissaoPlaca.value) {
                dataValidade.setCustomValidity(
                    'A Validade da Documentação/Licença deve ser posterior à Emissão da Placa (' +
                    formatarData(dataEmissaoPlaca.value) + ').'
                );
            } else {
                dataValidade.setCustomValidity('');
            }
            dataValidade.reportValidity && dataValidade.value ? dataValidade.checkValidity() : null;
        }
        dataValidade?.addEventListener('change', validarValidade);
        dataEmissaoPlaca?.addEventListener('change', validarValidade);

        // Próxima Manutenção ≥ hoje
        dataManutencao?.addEventListener('change', function () {
            if (this.value && this.value < today) {
                this.setCustomValidity('A Próxima Manutenção deve ser hoje ou uma data futura (hoje = ' + formatarData(today) + ').');
            } else {
                this.setCustomValidity('');
            }
        });

        // Próxima Inspeção ≥ hoje
        dataInspecao?.addEventListener('change', function () {
            if (this.value && this.value < today) {
                this.setCustomValidity('A Próxima Inspeção deve ser hoje ou uma data futura (hoje = ' + formatarData(today) + ').');
            } else {
                this.setCustomValidity('');
            }
        });

        // Km da Próxima Revisão ≥ Km Atual
        function validarKmRevisao() {
            if (!proxRevisaoKms || !kmsAtual) return;
            const km = parseFloat(kmsAtual.value) || 0;
            const pr = parseFloat(proxRevisaoKms.value);
            if (proxRevisaoKms.value !== '' && pr < km) {
                proxRevisaoKms.setCustomValidity(
                    'O Km da Próxima Revisão (' + pr.toLocaleString('pt-BR') + ' km) deve ser maior ou igual ao Km Atual (' + km.toLocaleString('pt-BR') + ' km).'
                );
            } else {
                proxRevisaoKms.setCustomValidity('');
            }
        }
        proxRevisaoKms?.addEventListener('change', validarKmRevisao);
        kmsAtual?.addEventListener('change', validarKmRevisao);

        // Km da Próxima Manutenção ≥ Km Atual
        function validarKmManut() {
            if (!proxManutKms || !kmsAtual) return;
            const km = parseFloat(kmsAtual.value) || 0;
            const pm = parseFloat(proxManutKms.value);
            if (proxManutKms.value !== '' && pm < km) {
                proxManutKms.setCustomValidity(
                    'O Km da Próxima Manutenção (' + pm.toLocaleString('pt-BR') + ' km) deve ser maior ou igual ao Km Atual (' + km.toLocaleString('pt-BR') + ' km).'
                );
            } else {
                proxManutKms.setCustomValidity('');
            }
        }
        proxManutKms?.addEventListener('change', validarKmManut);
        kmsAtual?.addEventListener('change', validarKmManut);

        // Rodar validações no load para pegar valores já preenchidos (edição)
        validarValidade();
        validarKmRevisao();
        validarKmManut();

        function formatarData(iso) {
            if (!iso) return '';
            const [y, m, d] = iso.split('-');
            return d + '/' + m + '/' + y;
        }
    })();

    // ── Status Vendido ──
    const $status = $('[name="status_veiculo_id"]');
    const $divDataVendido = $('#div_data_vendido');
    const $inputDataVendido = $('#data_vendido');

    function isVendido(v) { return String(v || '') === '3'; }
    function configDataVendido(show) {
        if (show) { $divDataVendido.removeClass('d-none'); $inputDataVendido.attr('required','required'); }
        else       { $divDataVendido.addClass('d-none');   $inputDataVendido.removeAttr('required'); }
    }

    $status.on('change', function () { configDataVendido(isVendido($(this).val())); });
    configDataVendido(isVendido($status.val()) || ($inputDataVendido.val() || '').trim() !== '');
});
</script>
@stop
