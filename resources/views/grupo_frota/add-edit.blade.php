@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $isEdit = isset($grupoFrota);
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
            $mensagensPendenciaGrupoFrota[] =
                'Cadastre pelo menos um tipo de opcional para organizar os opcionais do grupo.';
        }
        if ($qtdOpcionais === 0) {
            $mensagensPendenciaGrupoFrota[] = 'Cadastre pelo menos um opcional para vincular no grupo de frota.';
        }

        $acoesPreRequisitosGrupoFrota = [];
        if ($qtdTiposFrota === 0 && \Illuminate\Support\Facades\Route::has('tipo_frota.create')) {
            $acoesPreRequisitosGrupoFrota[] = [
                'label' => 'Cadastrar tipo de frota',
                'url' => route('tipo_frota.create'),
            ];
        }
        if ($qtdModelos === 0 && \Illuminate\Support\Facades\Route::has('modelo.create')) {
            $acoesPreRequisitosGrupoFrota[] = ['label' => 'Cadastrar modelo', 'url' => route('modelo.create')];
        }
        if ($qtdCaracteristicas === 0 && \Illuminate\Support\Facades\Route::has('caracteristica_frota.create')) {
            $acoesPreRequisitosGrupoFrota[] = [
                'label' => 'Cadastrar caracteristica de frota',
                'url' => route('caracteristica_frota.create'),
            ];
        }
        if ($qtdTiposOpcionais === 0 && \Illuminate\Support\Facades\Route::has('tipo_opcional.create')) {
            $acoesPreRequisitosGrupoFrota[] = [
                'label' => 'Cadastrar tipo de opcional',
                'url' => route('tipo_opcional.create'),
            ];
        }
        if ($qtdOpcionais === 0 && \Illuminate\Support\Facades\Route::has('opcional.create')) {
            $acoesPreRequisitosGrupoFrota[] = ['label' => 'Cadastrar opcional', 'url' => route('opcional.create')];
        }

        $bloquearSalvarGrupoFrota =
            $metodo === 'POST' && !((bool) ($preRequisitosGrupoFrota['pode_criar_grupo_frota'] ?? true));
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
        <x-pre-requisitos-alerta title="Pre-requisitos pendentes para criar grupo de frota" :chips="[
            ['label' => 'Tipos de Frota', 'value' => $qtdTiposFrota],
            ['label' => 'Caracteristicas', 'value' => $qtdCaracteristicas],
            ['label' => 'Tipos de Opcionais', 'value' => $qtdTiposOpcionais],
            ['label' => 'Opcionais', 'value' => $qtdOpcionais],
            ['label' => 'Modelos', 'value' => $qtdModelos],
        ]"
            :missing-items="$mensagensPendenciaGrupoFrota" :action-links="$acoesPreRequisitosGrupoFrota" />

        <div class="card card-grupo-frota shadow-sm border-0">
            <div class="card-header card-header-grupo-frota border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure dados gerais, caracteristicas, opcionais e modelos do grupo.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario" method="POST" action="{{ $rota }}" enctype="multipart/form-data">
                    @if ($metodo == 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="ec-wizard-steps mb-4" id="grupoFrotaWizardSteps">
                        <button type="button" class="ec-step-btn active" data-step="1">
                            <span class="ec-step-circle"><i class="fas fa-info-circle"></i></span>
                            <span class="ec-step-label">Geral</span>
                        </button>
                        <div class="ec-step-line"></div>
                        <button type="button" class="ec-step-btn" data-step="2">
                            <span class="ec-step-circle"><i class="fas fa-list-ul"></i></span>
                            <span class="ec-step-label">Caracteristicas</span>
                        </button>
                        <div class="ec-step-line"></div>
                        <button type="button" class="ec-step-btn" data-step="3">
                            <span class="ec-step-circle"><i class="fas fa-plus-circle"></i></span>
                            <span class="ec-step-label">Opcionais</span>
                        </button>
                        <div class="ec-step-line"></div>
                        <button type="button" class="ec-step-btn" data-step="4">
                            <span class="ec-step-circle"><i class="fas fa-tags"></i></span>
                            <span class="ec-step-label">Modelos</span>
                        </button>
                    </div>

                    <ul class="nav nav-pills nav-grupo-frota mb-3" id="grupo-frota-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="geral-tab" data-toggle="pill" href="#geral-content"
                                role="tab" aria-controls="geral-content" aria-selected="true">
                                <i class="fa fa-info-circle mr-1"></i> Geral
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="caracteristicas-tab" data-toggle="pill" href="#caracteristicas-content"
                                role="tab" aria-controls="caracteristicas-content" aria-selected="false">
                                <i class="fa fa-list-ul mr-1"></i> Caracteristicas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="opcionais-tab" data-toggle="pill" href="#opcionais-content"
                                role="tab" aria-controls="opcionais-content" aria-selected="false">
                                <i class="fa fa-plus-circle mr-1"></i> Opcionais
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="modelos-tab" data-toggle="pill" href="#modelos-content" role="tab"
                                aria-controls="modelos-content" aria-selected="false">
                                <i class="fa fa-tags mr-1"></i> Modelos
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="grupo-frota-tab-content">
                        <div id="geral-content" class="tab-pane fade show active" role="tabpanel"
                            aria-labelledby="geral-tab">
                            <div class="grupo-frota-section-title">Informacoes Basicas</div>
                            <div class="form-row">
                                <div class="col-lg-6">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-input type="text" class="form-control form-control-sm" id="nome"
                                            name="nome" label="Nome" value="{{ old('nome', $grupoFrota->nome ?? '') }}"
                                            required />
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-input type="text" class="form-control form-control-sm" id="codigo"
                                            name="codigo" label="Codigo"
                                            value="{{ old('codigo', $grupoFrota->codigo ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-input type="text" class="form-control form-control-sm" id="codigo_sipp"
                                            name="codigo_sipp" label="Codigo SIPP"
                                            value="{{ old('codigo_sipp', $grupoFrota->codigo_sipp ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-select name="ativo" label="Status" :options="$status"
                                            placeholder="Selecione..." :selected="old('ativo', $grupoFrota->ativo ?? 'S')"
                                            class="form-control form-control-sm" required />
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-lg-3">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-select name="tipo_frota_id" label="Tipo de Frota" :options="$tipoFrotas"
                                            placeholder="Selecione..." :selected="old(
                                                'tipo_frota_id',
                                                isset($grupoFrota->tipo_frota_id) && $grupoFrota->tipo_frota_id
                                                    ? $grupoFrota->tipo_frota_id
                                                    : null,
                                            )"
                                            class="form-control form-control-sm" required />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group form-group-grupo-frota">
                                        <x-form-input type="text" class="form-control form-control-sm" id="valor_diario"
                                            name="valor_diario" label="Valor Diario"
                                            value="{{ old('valor_diario', $grupoFrota->valor_diario ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group form-group-grupo-frota">
                                        <label for="fileInput">Imagem de destaque</label>
                                        <x-input-file id="fileInput" name="imagem" placeholder="Selecione a imagem..."
                                            legend="Click" />
                                        <div class="grupo-frota-preview mt-2 js-imagem-principal-preview">
                                            @if(!empty($grupoFrota?->imagem_url))
                                                <img src="{{ $grupoFrota->imagem_url }}" alt="Imagem atual">
                                            @else
                                                <div class="grupo-frota-preview__empty">
                                                    <i class="fas fa-image"></i>
                                                    <span>Preview da imagem principal</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group form-group-grupo-frota">
                                        <label for="imagens">Imagens do grupo (multiplas)</label>
                                        <input type="file" id="imagens" name="imagens[]" class="form-control form-control-sm" multiple accept=".jpg,.jpeg,.png,.webp,.svg">
                                        <small class="text-muted d-block mt-1">Voce pode selecionar varias imagens para o grupo.</small>
                                        <div class="grupo-frota-gallery mt-2 js-imagens-preview">
                                            @if(!empty($grupoFrota?->imagens_urls))
                                                @foreach($grupoFrota->imagens_urls as $img)
                                                    <div class="grupo-frota-thumb">
                                                        <img src="{{ $img }}" alt="Imagem do grupo">
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="grupo-frota-gallery__empty">
                                                    <i class="fas fa-images"></i>
                                                    <span>Preview das imagens adicionais</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="caracteristicas-content" class="tab-pane fade" role="tabpanel"
                            aria-labelledby="caracteristicas-tab">
                            <div class="grupo-frota-section-title d-flex align-items-center justify-content-between">
                                <span>Caracteristicas do Grupo</span>
                                <small class="text-muted">Selecione apenas as caracteristicas que deseja usar neste
                                    grupo</small>
                            </div>

                            <div class="table-responsive card-interno">
                                <table class="table table-sm table-bordered table-hover mb-0 table-grupo-frota">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">Usar</th>
                                            <th>Caracteristica</th>
                                            <th>Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($caracteristicas as $caracteristica)
                                            @php
                                                $tipoValor = \Illuminate\Support\Str::of(
                                                    (string) ($caracteristica->tipoValorDescricao->nome ??
                                                        ($caracteristica->tipoValorDescricao->configuracao ?? '')),
                                                )
                                                    ->ascii()
                                                    ->lower()
                                                    ->toString();
                                                $ehQuantidade =
                                                    str_contains($tipoValor, 'inteiro') ||
                                                    str_contains($tipoValor, 'numero') ||
                                                    str_contains($tipoValor, 'quantidade') ||
                                                    str_contains($tipoValor, 'qtd');

                                                $caracteristicaId = (int) ($caracteristica->id ?? 0);

                                                $caracteristicaSelecionada =
                                                    isset($grupoFrota) &&
                                                    collect($grupoFrota->caracteristicas)->contains(
                                                        'id',
                                                        $caracteristicaId,
                                                    );

                                                $valorAtual = $caracteristicaSelecionada
                                                    ? collect($grupoFrota->caracteristicas)->firstWhere(
                                                        'id',
                                                        $caracteristicaId,
                                                    )['valor']
                                                    : '';
                                            @endphp

                                            <tr>
                                                <td class="text-center align-middle">
                                                    <input type="checkbox" class="caracteristica-check"
                                                        id="caracteristica_{{ $caracteristicaId }}"
                                                        name="caracteristicas[]" value="{{ $caracteristicaId }}"
                                                        {{ $caracteristicaSelecionada ? 'checked' : '' }}>
                                                </td>

                                                <td class="align-middle">
                                                    <label for="caracteristica_{{ $caracteristicaId }}" class="mb-0"
                                                        style="cursor:pointer;">
                                                        {{ $caracteristica->nome }}
                                                    </label>

                                                    @if (!empty($caracteristica->tipoValorDescricao?->nome))
                                                        <small class="text-muted d-block">
                                                            {{ $caracteristica->tipoValorDescricao->nome }}
                                                        </small>
                                                    @endif
                                                </td>

                                                <td>
                                                    <input type="{{ $ehQuantidade ? 'number' : 'text' }}"
                                                        name="caracteristicaValor[{{ $caracteristicaId }}]"
                                                        class="form-control form-control-sm caracteristica-valor"
                                                        value="{{ $valorAtual }}" data-id="{{ $caracteristicaId }}"
                                                        {{ $caracteristicaSelecionada ? '' : 'disabled' }}
                                                        @if ($ehQuantidade) min="0"
        step="1"
        inputmode="numeric"
        pattern="[0-9]*" @endif>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        <div id="opcionais-content" class="tab-pane fade" role="tabpanel"
                            aria-labelledby="opcionais-tab">
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
                                                            <input type="checkbox" id="opcional_{{ $opcional->id }}"
                                                                name="opcionais[]" value="{{ $opcional->id }}"
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
                                                    <input type="checkbox" id="modelo_{{ $id_modelo }}"
                                                        name="modelos[]" value="{{ $id_modelo }}"
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

                    <div class="ec-wizard-nav mt-4 d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-sm ec-nav-btn ec-nav-btn--back" id="btnPrev" style="display:none!important;">
                                <i class="fas fa-chevron-left mr-1"></i> Voltar
                            </button>
                        </div>
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <button type="button" class="btn btn-sm ec-nav-btn ec-nav-btn--next" id="btnNext">
                                Próximo <i class="fas fa-chevron-right ml-1"></i>
                            </button>
                            <button type="submit" class="btn btn-sm ec-nav-btn ec-nav-btn--save d-none" id="btnSalvar"
                                {{ $bloquearSalvarGrupoFrota ? 'disabled' : '' }}>
                                <i class="fas fa-save mr-1"></i> Salvar alterações
                            </button>
                        </div>
                    </div>

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
        .campo-desabilitado {
    background: #f1f5f9 !important;
    cursor: not-allowed;
    opacity: .75;
}
        .card-grupo-frota {
            border-radius: 14px;
            overflow: hidden;
        }

        .nav-grupo-frota {
            display: none;
        }

        .ec-wizard-steps {
            display: flex;
            align-items: center;
            gap: 0;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .ec-step-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            background: none;
            border: none;
            padding: 0 8px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .ec-step-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #d0daea;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: #94a3b8;
            transition: all .2s;
        }

        .ec-step-btn.active .ec-step-circle {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .ec-step-btn.done .ec-step-circle {
            background: #ecfdf5;
            border-color: #10b981;
            color: #10b981;
        }

        .ec-step-label {
            font-size: .72rem;
            font-weight: 600;
            color: #94a3b8;
            white-space: nowrap;
        }

        .ec-step-btn.active .ec-step-label { color: #2563eb; }
        .ec-step-btn.done .ec-step-label { color: #10b981; }
        .ec-step-line {
            flex: 1;
            height: 2px;
            background: #e5eaf2;
            min-width: 16px;
            margin-bottom: 18px;
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

        .grupo-frota-preview img {
            width: 100%;
            max-height: 160px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #dbe3ef;
        }

        .grupo-frota-preview {
            min-height: 160px;
            border: 1px dashed #d7e3f2;
            border-radius: 12px;
            background: #f8fbff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .grupo-frota-preview__empty,
        .grupo-frota-gallery__empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #94a3b8;
            font-size: .82rem;
            text-align: center;
            padding: 1rem;
        }

        .grupo-frota-preview__empty i,
        .grupo-frota-gallery__empty i {
            font-size: 2rem;
            opacity: .55;
        }

        .grupo-frota-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 8px;
            min-height: 92px;
            border: 1px dashed #d7e3f2;
            border-radius: 12px;
            background: #f8fbff;
            padding: 8px;
        }

        .grupo-frota-thumb {
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            overflow: hidden;
            background: #f8fafc;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .grupo-frota-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .ec-nav-btn {
            min-height: 38px;
            padding: .58rem 1.05rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            border-radius: 999px;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
            transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease, border-color .15s ease, color .15s ease;
        }

        .ec-nav-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(15, 23, 42, .10);
        }

        .ec-nav-btn--back {
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #64748b;
        }

        .ec-nav-btn--back:hover,
        .ec-nav-btn--back.ec-nav-btn--visible {
            background: #fff;
            border-color: #94a3b8;
            color: #475569;
        }

        .ec-nav-btn--next {
            background: #2563eb;
            border: 1px solid #2563eb;
            color: #fff;
        }

        .ec-nav-btn--next:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #fff;
        }

        .ec-nav-btn--save {
            background: #16a34a;
            border: 1px solid #16a34a;
            color: #fff;
        }

        .ec-nav-btn--save:hover {
            background: #15803d;
            border-color: #15803d;
            color: #fff;
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

            const isEdit = @json($isEdit);
            const TOTAL_STEPS = 4;
            let currentStep = 1;

            function goToStep(n) {
                n = Math.max(1, Math.min(TOTAL_STEPS, n));
                currentStep = n;

                $('#geral-content, #caracteristicas-content, #opcionais-content, #modelos-content')
                    .removeClass('show active')
                    .addClass('d-none');

                const $pane = $(`#${['', 'geral-content', 'caracteristicas-content', 'opcionais-content', 'modelos-content'][n]}`);
                $pane.removeClass('d-none').addClass('show active');

                $('.ec-step-btn').each(function () {
                    const step = parseInt($(this).data('step'));
                    $(this).removeClass('active done');
                    if (step === n) {
                        $(this).addClass('active');
                    } else if (step < n) {
                        $(this).addClass('done');
                    }
                });

                $('#btnPrev').css('display', n > 1 ? 'inline-flex' : 'none');
                $('#btnPrev').toggleClass('ec-nav-btn--visible', n > 1);
                $('#btnNext').toggleClass('d-none', n >= TOTAL_STEPS);

                if (isEdit) {
                    $('#btnSalvar').removeClass('d-none');
                } else {
                    $('#btnSalvar').toggleClass('d-none', n < TOTAL_STEPS);
                }
            }

            goToStep(currentStep);

            $('#btnNext').on('click', function () {
                goToStep(currentStep + 1);
            });
            $('#btnPrev').on('click', function () {
                goToStep(currentStep - 1);
            });
            $('.ec-step-btn').on('click', function () {
                goToStep(parseInt($(this).data('step')));
            });

            function toggleCampoCaracteristica($checkbox) {
                var id = $checkbox.val();
                var $input = $('.caracteristica-valor[data-id="' + id + '"]');

                if (!$input.length) {
                    return;
                }

                if ($checkbox.is(':checked')) {
                    $input.prop('disabled', false);
                    $input.removeClass('campo-desabilitado');
                } else {
                    $input.prop('disabled', true);
                    $input.val('');
                    $input.addClass('campo-desabilitado');
                }
            }

            $(document).on('change', '.caracteristica-check', function () {
                toggleCampoCaracteristica($(this));
            });

            $('.caracteristica-check').each(function () {
                toggleCampoCaracteristica($(this));
            });

            function renderSinglePreview(input, $preview) {
                const file = input.files && input.files[0] ? input.files[0] : null;
                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    $preview.html(
                        '<img src="' + e.target.result + '" alt="Preview da imagem">'
                    );
                };
                reader.readAsDataURL(file);
            }

            function renderMultiplePreview(input, $preview) {
                const files = Array.from(input.files || []);
                if (!files.length) {
                    return;
                }

                $preview.empty();
                files.forEach(function (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $preview.append(
                            '<div class="grupo-frota-thumb"><img src="' + e.target.result + '" alt="Preview da imagem"></div>'
                        );
                    };
                    reader.readAsDataURL(file);
                });
            }

            $('#fileInput').on('change', function () {
                renderSinglePreview(this, $('.js-imagem-principal-preview'));
            });

            $('#imagens').on('change', function () {
                renderMultiplePreview(this, $('.js-imagens-preview'));
            });
        });
    </script>
@stop
