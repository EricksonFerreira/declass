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

        $qtdTiposFrota = is_countable($tipoFrotas ?? null) ? count($tipoFrotas) : 0;
        $qtdGruposFrota = is_countable($grupoFrotas ?? null) ? count($grupoFrotas) : 0;
        $qtdMarcas = is_countable($marcas ?? null) ? count($marcas) : 0;
        $qtdModelos = is_countable($modelos ?? null) ? count($modelos) : 0;
        $qtdStatusVeiculo = is_countable($statusVeiculos ?? null) ? count($statusVeiculos) : 0;
        $qtdEstacoes = is_countable($estacoes ?? null) ? count($estacoes) : 0;

        $faltasPreRequisitosVeiculo = [];
        if ($qtdTiposFrota === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um tipo de frota ativo.';
        }
        if ($qtdGruposFrota === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um grupo de frota ativo.';
        }
        if ($qtdMarcas === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos uma marca ativa.';
        }
        if ($qtdModelos === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um modelo ativo.';
        }
        if ($qtdStatusVeiculo === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um status de veiculo ativo.';
        }
        if ($mostrarAbaDisponibilidade && $qtdEstacoes === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos uma estacao ativa para definir a disponibilidade inicial.';
        }

        $acoesPreRequisitosVeiculo = [];
        if ($qtdTiposFrota === 0 && \Illuminate\Support\Facades\Route::has('tipo_frota.create')) {
            $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar tipo de frota', 'url' => route('tipo_frota.create')];
        }
        if ($qtdGruposFrota === 0 && \Illuminate\Support\Facades\Route::has('grupo_frota.create')) {
            $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar grupo de frota', 'url' => route('grupo_frota.create')];
        }
        if ($qtdMarcas === 0 && \Illuminate\Support\Facades\Route::has('marca.create')) {
            $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar marca', 'url' => route('marca.create')];
        }
        if ($qtdModelos === 0 && \Illuminate\Support\Facades\Route::has('modelo.create')) {
            $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar modelo', 'url' => route('modelo.create')];
        }
        if ($qtdStatusVeiculo === 0 && \Illuminate\Support\Facades\Route::has('status_veiculo.create')) {
            $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar status de veiculo', 'url' => route('status_veiculo.create')];
        }
        if ($mostrarAbaDisponibilidade && $qtdEstacoes === 0 && \Illuminate\Support\Facades\Route::has('estacao.create')) {
            $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar estacao', 'url' => route('estacao.create')];
        }

        $podeCriarVeiculo = empty($faltasPreRequisitosVeiculo);
        $bloquearSalvarVeiculo = $metodo === 'POST' && !$podeCriarVeiculo;
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul class="mb-0 pl-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <x-pre-requisitos-alerta
            title="Pre-requisitos pendentes para adicionar veiculo"
            :chips="[
                ['label' => 'Tipos de Frota', 'value' => $qtdTiposFrota],
                ['label' => 'Grupos de Frota', 'value' => $qtdGruposFrota],
                ['label' => 'Marcas', 'value' => $qtdMarcas],
                ['label' => 'Modelos', 'value' => $qtdModelos],
                ['label' => 'Status Veiculo', 'value' => $qtdStatusVeiculo],
                ['label' => 'Estacoes', 'value' => $qtdEstacoes],
            ]"
            :missing-items="$faltasPreRequisitosVeiculo"
            :action-links="$acoesPreRequisitosVeiculo"
        />

        <div class="card card-veiculo shadow-sm border-0">
            <div class="card-header card-header-veiculo border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure dados gerais, identificacao, manutencao e disponibilidade inicial do veiculo.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario" method="POST" action="{{ $rota }}">
                    @if ($metodo == 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <ul class="nav nav-pills nav-veiculo mb-3" id="veiculo-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="geral-tab" data-toggle="pill" href="#geral" role="tab" aria-controls="geral" aria-selected="true">
                                <i class="fa fa-info-circle mr-1"></i> Geral
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="identificacao-tab" data-toggle="pill" href="#identificacao" role="tab" aria-controls="identificacao" aria-selected="false">
                                <i class="fa fa-id-card mr-1"></i> Identificacao
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="datas-metricas-tab" data-toggle="pill" href="#datas-metricas" role="tab" aria-controls="datas-metricas" aria-selected="false">
                                <i class="fa fa-calendar mr-1"></i> Datas e Km
                            </a>
                        </li>
                        @if ($mostrarAbaDisponibilidade)
                            <li class="nav-item">
                                <a class="nav-link" id="disponibilidade-tab" data-toggle="pill" href="#disponibilidade" role="tab" aria-controls="disponibilidade" aria-selected="false">
                                    <i class="fa fa-map-marker-alt mr-1"></i> Disponibilidade
                                </a>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content" id="veiculo-tab-content">
                        <div class="tab-pane fade show active" id="geral" role="tabpanel" aria-labelledby="geral-tab">
                            <div class="veiculo-section-title">Informacoes Basicas</div>
                            <div class="form-row">
                                @isset($veiculo)
                                    <div class="col-lg-3">
                                        <div class="form-group form-group-veiculo">
                                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                                label="Nome" value="{{ old('nome', $veiculo->nome ?? '') }}" disabled readonly />
                                        </div>
                                    </div>
                                @endisset

                                <div class="col-lg-3">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-select name="tipo_frota_id" label="Tipo de Frota" :options="$tipoFrotas"
                                            placeholder="Selecione um valor" :selected="old(
                                                'tipo_frota_id',
                                                isset($veiculo->tipo_frota_id) && $veiculo->tipo_frota_id ? $veiculo->tipo_frota_id : null,
                                            )" class="form-control form-control-sm"
                                            required />
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-select name="grupo_frota_id" label="Grupo de Frota" :options="$grupoFrotas"
                                            placeholder="Selecione um valor" :selected="old(
                                                'grupo_frota_id',
                                                isset($veiculo->grupo_frota_id) && $veiculo->grupo_frota_id ? $veiculo->grupo_frota_id : null,
                                            )" class="form-control form-control-sm"
                                            required />
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-select name="marca_id" label="Marca" :options="$marcas" placeholder="Selecione um valor"
                                            :selected="old('marca_id', isset($veiculo->marca_id) && $veiculo->marca_id ? $veiculo->marca_id : null)"
                                            class="form-control form-control-sm" required />
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-select name="modelo_id" label="Modelo" :options="$modelos" placeholder="Selecione um valor"
                                            :selected="old('modelo_id', isset($veiculo->modelo_id) && $veiculo->modelo_id ? $veiculo->modelo_id : null)"
                                            class="form-control form-control-sm" required />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-select id="status_veiculo_id" name="status_veiculo_id" label="Status"
                                            :options="$statusVeiculos" placeholder="Selecione um valor" :selected="old(
                                                'status_veiculo_id',
                                                isset($veiculo->status_veiculo_id) && $veiculo->status_veiculo_id ? $veiculo->status_veiculo_id : null,
                                            )" class="form-control form-control-sm"
                                            required />
                                    </div>
                                </div>
                                <div class="col-lg-3 d-none" id="div_data_vendido">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="date" class="form-control form-control-sm" id="data_vendido"
                                            name="data_vendido" label="Data de Venda"
                                            value="{{ old('data_vendido', $veiculo->data_vendido ?? '') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="identificacao" role="tabpanel" aria-labelledby="identificacao-tab">
                            <div class="veiculo-section-title">Dados de Identificacao</div>
                            <div class="form-row">
                                <div class="col-lg-3 col-md-4">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="number" class="form-control form-control-sm" id="ano_fabricacao"
                                            name="ano_fabricacao" label="Ano de Fabricacao" min="1900"
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
                                        <x-form-input type="text" class="form-control form-control-sm" id="placa_licenca"
                                            name="placa_licenca" label="Placa"
                                            value="{{ old('placa_licenca', $veiculo->placa_licenca ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="text" class="form-control form-control-sm" id="chassi" name="chassi"
                                            label="Chassi" value="{{ old('chassi', $veiculo->chassi ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="text" class="form-control form-control-sm" id="cor" name="cor"
                                            label="Cor" value="{{ old('cor', $veiculo->cor ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="text" class="form-control form-control-sm" id="numero_motor"
                                            name="numero_motor" label="Numero do Motor"
                                            value="{{ old('numero_motor', $veiculo->numero_motor ?? '') }}" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="datas-metricas" role="tabpanel" aria-labelledby="datas-metricas-tab">
                            <div class="veiculo-section-title">Datas Importantes</div>
                            <div class="form-row">
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="date" class="form-control form-control-sm" id="data_placa_licenca"
                                            name="data_placa_licenca" label="Data de Emissao da Placa/Licenca"
                                            value="{{ old('data_placa_licenca', $veiculo->data_placa_licenca ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="date" class="form-control form-control-sm" id="data_compra"
                                            name="data_compra" label="Data de Compra"
                                            value="{{ old('data_compra', $veiculo->data_compra ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="date" class="form-control form-control-sm" id="data_validade"
                                            name="data_validade" label="Data de Validade da Documentacao/Licenca"
                                            value="{{ old('data_validade', $veiculo->data_validade ?? '') }}"  required />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="date" class="form-control form-control-sm" id="data_proxima_manutencao"
                                            name="data_proxima_manutencao" label="Data da Proxima Manutencao"
                                            value="{{ old('data_proxima_manutencao', $veiculo->data_proxima_manutencao ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="date" class="form-control form-control-sm" id="data_proxima_inspecao"
                                            name="data_proxima_inspecao" label="Data da Proxima Inspecao"
                                            value="{{ old('data_proxima_inspecao', $veiculo->data_proxima_inspecao ?? '') }}" required />
                                    </div>
                                </div>
                            </div>

                            <div class="veiculo-section-title">Metricas e Limites</div>
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
                                            name="limite_kms" label="Limite de Km"
                                            value="{{ old('limite_kms', $veiculo->limite_kms ?? '') }}" min="0" required />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="number" class="form-control form-control-sm" id="proxima_revisao_kms"
                                            name="proxima_revisao_kms" label="Km da Proxima Revisao"
                                            value="{{ old('proxima_revisao_kms', $veiculo->proxima_revisao_kms ?? '') }}" min="0" required />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <x-form-input type="number" class="form-control form-control-sm" id="proxima_manutencao_kms"
                                            name="proxima_manutencao_kms" label="Km da Proxima Manutencao"
                                            value="{{ old('proxima_manutencao_kms', $veiculo->proxima_manutencao_kms ?? '') }}" min="0" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($mostrarAbaDisponibilidade)
                            <div class="tab-pane fade" id="disponibilidade" role="tabpanel" aria-labelledby="disponibilidade-tab">
                                <div class="veiculo-section-title">Disponibilidade Inicial</div>
                                <div class="veiculo-info-tip">
                                    Defina em qual estacao o veiculo inicia e a data/hora inicial de disponibilidade.
                                </div>
                                <div class="form-row">
                                    <div class="col-lg-4">
                                        <div class="form-group form-group-veiculo">
                                            <x-form-select name="local_disponibilidade" label="Estacao" :options="$estacoes"
                                                placeholder="Selecione um valor" :selected="old(
                                                    'local_disponibilidade',
                                                    isset($veiculo->local_disponibilidade) && $veiculo->local_disponibilidade ? $veiculo->local_disponibilidade : null,
                                                )" class="form-control form-control-sm"
                                                required />
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group form-group-veiculo">
                                            <x-form-input type="datetime-local" class="form-control form-control-sm"
                                                id="data_hora_disponibilidade" name="data_hora_disponibilidade" label="Data e Hora"
                                                value="{{ old('data_hora_disponibilidade', $veiculo->data_hora_disponibilidade ?? '') }}"
                                                required />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <button id="btn_salvar" type="submit" class="btn btn-primary btn-save-veiculo col-12 mt-3" {{ $bloquearSalvarVeiculo ? 'disabled' : '' }}>
                        Salvar
                    </button>
                    @if ($bloquearSalvarVeiculo)
                        <small class="text-danger d-block mt-2 text-center font-weight-semibold">
                            Cadastre os itens obrigatorios acima para liberar a criacao de veiculo.
                        </small>
                    @endif
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-veiculo {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-veiculo {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-veiculo h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .nav-veiculo .nav-link {
        border-radius: 8px;
        font-weight: 600;
        color: #334155;
        border: 1px solid #dbe3ef;
        margin-right: .45rem;
        background: #fff;
    }

    .nav-veiculo .nav-link.active {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }

    .veiculo-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-veiculo {
        margin-bottom: 1rem;
    }

    .veiculo-info-tip {
        border: 1px solid #dbe6f3;
        border-radius: 10px;
        background: #f8fbff;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 10px;
        margin-bottom: 10px;
    }

    .card-veiculo .form-control,
    .card-veiculo .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-veiculo .form-control:focus,
    .card-veiculo .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-veiculo {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-veiculo {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .nav-veiculo {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .nav-veiculo .nav-item {
            flex: 0 0 auto;
        }

        .nav-veiculo .nav-link {
            white-space: nowrap;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-veiculo h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-veiculo p {
            font-size: .85rem;
        }

        .veiculo-section-title {
            font-size: .9rem;
        }

        .btn-save-veiculo {
            min-height: 42px;
            font-size: .95rem;
        }
    }
</style>
@stop

@section('js')
    <script>
        $(function () {
            const $status = $('[name="status_veiculo_id"]');
            const $divDataVendido = $('#div_data_vendido');
            const $inputDataVendido = $('#data_vendido');
            const errorKeys = @json($chavesErro);

            function isStatusVendido(valor) {
                return String(valor || '') === '3';
            }

            function configureDataVendido(show) {
                if (show) {
                    $divDataVendido.removeClass('d-none');
                    $inputDataVendido.attr('required', 'required');
                    return;
                }

                $divDataVendido.addClass('d-none');
                $inputDataVendido.removeAttr('required');
            }

            $status.on('change', function () {
                configureDataVendido(isStatusVendido($(this).val()));
            });

            const valorStatusInicial = $status.val();
            const possuiDataVendido = ($inputDataVendido.val() || '').trim() !== '';
            configureDataVendido(isStatusVendido(valorStatusInicial) || possuiDataVendido);

            if (!Array.isArray(errorKeys) || errorKeys.length === 0) {
                return;
            }

            const errosIdentificacao = [
                'ano_fabricacao',
                'ano_modelo',
                'placa_licenca',
                'chassi',
                'cor',
                'numero_motor',
            ];
            const errosDatasMetricas = [
                'data_placa_licenca',
                'data_compra',
                'data_validade',
                'data_proxima_manutencao',
                'data_proxima_inspecao',
                'kms',
                'limite_kms',
                'proxima_revisao_kms',
                'proxima_manutencao_kms',
            ];
            const errosDisponibilidade = [
                'local_disponibilidade',
                'data_hora_disponibilidade',
            ];

            if (errorKeys.some((k) => errosIdentificacao.includes(k))) {
                $('#identificacao-tab').tab('show');
                return;
            }

            if (errorKeys.some((k) => errosDatasMetricas.includes(k))) {
                $('#datas-metricas-tab').tab('show');
                return;
            }

            if (errorKeys.some((k) => errosDisponibilidade.includes(k))) {
                $('#disponibilidade-tab').tab('show');
            }
        });
    </script>
@stop
