@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $preRequisitosGrupoFrota = $preRequisitosGrupoFrota ?? [
            'qtd_tipos_frota' => is_countable($tipoFrotas ?? null) ? count($tipoFrotas) : 0,
            'qtd_modelos' => is_countable($modelos ?? null) ? count($modelos) : 0,
            'qtd_caracteristicas' => is_countable($caracteristicas ?? null) ? count($caracteristicas) : 0,
            'qtd_tipos_opcionais' => is_countable($tiposOpcionais ?? null) ? count($tiposOpcionais) : 0,
            'qtd_opcionais' => 0,
            'faltas' => [],
            'pode_criar_grupo_frota' => true,
        ];

        $qtdTiposFrota = (int) ($preRequisitosGrupoFrota['qtd_tipos_frota'] ?? 0);
        $qtdModelos = (int) ($preRequisitosGrupoFrota['qtd_modelos'] ?? 0);
        $qtdCaracteristicas = (int) ($preRequisitosGrupoFrota['qtd_caracteristicas'] ?? 0);
        $qtdTiposOpcionais = (int) ($preRequisitosGrupoFrota['qtd_tipos_opcionais'] ?? 0);
        $qtdOpcionais = (int) ($preRequisitosGrupoFrota['qtd_opcionais'] ?? 0);
        $mensagensPendenciaGrupoFrota = $preRequisitosGrupoFrota['faltas'] ?? [];

        if ($qtdTiposOpcionais === 0) {
            $mensagensPendenciaGrupoFrota[] = 'Cadastre pelo menos um tipo de opcional para organizar os opcionais do grupo.';
        }
        if ($qtdOpcionais === 0) {
            $mensagensPendenciaGrupoFrota[] = 'Cadastre pelo menos um opcional para vincular no grupo de frota.';
        }

        $acoesPreRequisitosGrupoFrota = [];
        if ($qtdTiposFrota === 0 && \Illuminate\Support\Facades\Route::has('tipo_frota.create')) {
            $acoesPreRequisitosGrupoFrota[] = ['label' => 'Cadastrar tipo de frota', 'url' => route('tipo_frota.create')];
        }
        if ($qtdModelos === 0 && \Illuminate\Support\Facades\Route::has('modelo.create')) {
            $acoesPreRequisitosGrupoFrota[] = ['label' => 'Cadastrar modelo', 'url' => route('modelo.create')];
        }
        if ($qtdCaracteristicas === 0 && \Illuminate\Support\Facades\Route::has('caracteristica_frota.create')) {
            $acoesPreRequisitosGrupoFrota[] = ['label' => 'Cadastrar caracteristica de frota', 'url' => route('caracteristica_frota.create')];
        }
        if ($qtdTiposOpcionais === 0 && \Illuminate\Support\Facades\Route::has('tipo_opcional.create')) {
            $acoesPreRequisitosGrupoFrota[] = ['label' => 'Cadastrar tipo de opcional', 'url' => route('tipo_opcional.create')];
        }
        if ($qtdOpcionais === 0 && \Illuminate\Support\Facades\Route::has('opcional.create')) {
            $acoesPreRequisitosGrupoFrota[] = ['label' => 'Cadastrar opcional', 'url' => route('opcional.create')];
        }

        $bloquearSalvarGrupoFrota = $metodo === 'POST' && !((bool) ($preRequisitosGrupoFrota['pode_criar_grupo_frota'] ?? true));
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
            title="Pre-requisitos pendentes para criar grupo de frota"
            :chips="[
                ['label' => 'Tipos de Frota', 'value' => $qtdTiposFrota],
                ['label' => 'Modelos', 'value' => $qtdModelos],
                ['label' => 'Caracteristicas', 'value' => $qtdCaracteristicas],
                ['label' => 'Tipos de Opcionais', 'value' => $qtdTiposOpcionais],
                ['label' => 'Opcionais', 'value' => $qtdOpcionais],
            ]"
            :missing-items="$mensagensPendenciaGrupoFrota"
            :action-links="$acoesPreRequisitosGrupoFrota"
        />

        <div class="card card-grupo-frota shadow-sm border-0">
            <div class="card-header card-header-grupo-frota border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure dados gerais, caracteristicas, opcionais e modelos do grupo.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario" method="POST" action="{{ $rota }}">
                    @if ($metodo == 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <ul class="nav nav-pills nav-grupo-frota mb-3" id="grupo-frota-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="geral-tab" data-toggle="pill" href="#geral-content" role="tab" aria-controls="geral-content" aria-selected="true">
                                <i class="fa fa-info-circle mr-1"></i> Geral
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="caracteristicas-tab" data-toggle="pill" href="#caracteristicas-content" role="tab" aria-controls="caracteristicas-content" aria-selected="false">
                                <i class="fa fa-list-ul mr-1"></i> Caracteristicas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="opcionais-tab" data-toggle="pill" href="#opcionais-content" role="tab" aria-controls="opcionais-content" aria-selected="false">
                                <i class="fa fa-plus-circle mr-1"></i> Opcionais
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="modelos-tab" data-toggle="pill" href="#modelos-content" role="tab" aria-controls="modelos-content" aria-selected="false">
                                <i class="fa fa-tags mr-1"></i> Modelos
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="grupo-frota-tab-content">
                        <div id="geral-content" class="tab-pane fade show active" role="tabpanel" aria-labelledby="geral-tab">
                            <div class="grupo-frota-section-title">Informacoes Basicas</div>
                            <div class="form-row">
                                <div class="col-lg-6">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome" label="Nome"
                                            value="{{ old('nome', $grupoFrota->nome ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-input type="text" class="form-control form-control-sm" id="codigo" name="codigo" label="Codigo"
                                            value="{{ old('codigo', $grupoFrota->codigo ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-input type="text" class="form-control form-control-sm" id="codigo_sipp" name="codigo_sipp" label="Codigo SIPP"
                                            value="{{ old('codigo_sipp', $grupoFrota->codigo_sipp ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione..."
                                            :selected="old('ativo', isset($grupoFrota->ativo) && $grupoFrota->ativo ? $grupoFrota->ativo : null)" class="form-control form-control-sm" required />
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-lg-3">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-select name="tipo_frota_id" label="Tipo de Frota" :options="$tipoFrotas" placeholder="Selecione..."
                                            :selected="old('tipo_frota_id', isset($grupoFrota->tipo_frota_id) && $grupoFrota->tipo_frota_id ? $grupoFrota->tipo_frota_id : null)" class="form-control form-control-sm" required />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-input type="text" class="form-control form-control-sm" id="valor_diario" name="valor_diario" label="Valor Diario"
                                            value="{{ old('valor_diario', $grupoFrota->valor_diario ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group form-group-grupo-frota">
                                        <label for="fileInput">Imagem</label>
                                        <x-input-file id="fileInput" name="file" placeholder="Selecione a imagem..." legend="Click" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="caracteristicas-content" class="tab-pane fade" role="tabpanel" aria-labelledby="caracteristicas-tab">
                            <div class="grupo-frota-section-title d-flex align-items-center justify-content-between">
                                <span>Caracteristicas do Grupo</span>
                                <small class="text-muted">Defina o valor de cada caracteristica</small>
                            </div>
                            <div class="table-responsive card-interno">
                                <table class="table table-sm table-bordered table-hover mb-0 table-grupo-frota">
                                    <thead>
                                        <tr>
                                            <th>Caracteristica</th>
                                            <th>Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($caracteristicas as $k => $caracteristica)
                                            <tr>
                                                <td>{{ $caracteristica }}</td>
                                                <td>
                                                    <input type="hidden" name="caracteristicas[{{ $k }}]" value="{{ $k }}">
                                                    <input type="text" name="caracteristicaValor[{{ $k }}]" class="form-control form-control-sm"
                                                        value="{{ isset($grupoFrota) && collect($grupoFrota->caracteristicas)->contains('id', $k) ? collect($grupoFrota->caracteristicas)->firstWhere('id', $k)['valor'] : '' }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="opcionais-content" class="tab-pane fade" role="tabpanel" aria-labelledby="opcionais-tab">
                            <div class="grupo-frota-section-title">Opcionais por Tipo</div>
                            @foreach ($tiposOpcionais as $tipoOpcional)
                                @if (count($tipoOpcional->opcionais) > 0)
                                    <div class="card card-opcional-grupo mb-2">
                                        <div class="card-header py-2 px-3 border-0">
                                            <strong>{{ $tipoOpcional->nome }}</strong>
                                        </div>
                                        <div class="card-body py-2 px-3">
                                            <div class="row">
                                                @foreach ($tipoOpcional->opcionais as $opcional)
                                                    <div class="col-lg-4 col-md-6 mb-1">
                                                        <label class="opcional-check mb-0">
                                                            <input type="checkbox" id="opcional_{{ $opcional->id }}" name="opcionais[]"
                                                                value="{{ $opcional->id }}"
                                                                {{ isset($grupoFrota) && in_array($opcional->id, $grupoFrota->opcionais) ? 'checked' : '' }}>
                                                            <span>{{ $opcional->nome }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div id="modelos-content" class="tab-pane fade" role="tabpanel" aria-labelledby="modelos-tab">
                            <div class="grupo-frota-section-title">Modelos Vinculados</div>
                            <div class="card card-modelos-grupo">
                                <div class="card-body py-2 px-3">
                                    <div class="row">
                                        @foreach ($modelos as $id_modelo => $modelo)
                                            <div class="col-lg-4 col-md-6 mb-1">
                                                <label class="opcional-check mb-0">
                                                    <input type="checkbox" id="modelo_{{ $id_modelo }}" name="modelos[]" value="{{ $id_modelo }}"
                                                        {{ isset($grupoFrota) && in_array($id_modelo, $grupoFrota->modelos) ? 'checked' : '' }}>
                                                    <span>{{ $modelo }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary col-12 mt-3 btn-save-grupo-frota" {{ $bloquearSalvarGrupoFrota ? 'disabled' : '' }}>
                        Salvar
                    </button>
                    @if ($bloquearSalvarGrupoFrota)
                        <small class="text-danger d-block mt-2 text-center font-weight-semibold">
                            Cadastre os itens obrigatorios acima para liberar a criacao do grupo de frota.
                        </small>
                    @endif
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-grupo-frota {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-grupo-frota {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-grupo-frota h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .nav-grupo-frota .nav-link {
        border-radius: 8px;
        font-weight: 600;
        color: #334155;
        border: 1px solid #dbe3ef;
        margin-right: .45rem;
        background: #fff;
    }

    .nav-grupo-frota .nav-link.active {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }

    .grupo-frota-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-grupo-frota {
        margin-bottom: 1rem;
    }

    .card-grupo-frota .form-control,
    .card-grupo-frota .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-grupo-frota .form-control:focus,
    .card-grupo-frota .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .card-interno,
    .card-opcional-grupo,
    .card-modelos-grupo {
        border: 1px solid #dbe6f3;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .table-grupo-frota thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
        color: #334155;
        font-weight: 700;
        font-size: .78rem;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid #d5e0ee;
    }

    .table-grupo-frota tbody tr:nth-child(even) td {
        background: #fbfdff;
    }

    .table-grupo-frota tbody tr:hover td {
        background: #f2f8ff;
    }

    .opcional-check {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #d8e1ec;
        border-radius: 10px;
        padding: 8px 10px;
        cursor: pointer;
        background: #fff;
        min-height: 40px;
    }

    .opcional-check input {
        margin: 0;
    }

    .opcional-check span {
        font-size: 12px;
        font-weight: 600;
        color: #334155;
    }

    .btn-save-grupo-frota {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-grupo-frota {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .nav-grupo-frota {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .nav-grupo-frota .nav-item {
            flex: 0 0 auto;
        }

        .nav-grupo-frota .nav-link {
            white-space: nowrap;
        }

        .table-grupo-frota {
            min-width: 640px;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-grupo-frota h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-grupo-frota p {
            font-size: .85rem;
        }
    }
</style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(function () {
            $('#valor_diario').mask('0000000000,00', {
                reverse: true
            });
        });
    </script>
@stop
