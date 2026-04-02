@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $qtdMarcas = is_countable($marcas ?? null) ? count($marcas) : 0;
        $qtdSegmentos = is_countable($segmentos ?? null) ? count($segmentos) : 0;

        $faltasPreRequisitosModelo = [];
        if ($qtdMarcas === 0) {
            $faltasPreRequisitosModelo[] = 'Cadastre pelo menos uma marca para criar modelos.';
        }
        if ($qtdSegmentos === 0) {
            $faltasPreRequisitosModelo[] = 'Cadastre pelo menos um segmento para criar modelos.';
        }

        $acoesPreRequisitosModelo = [];
        if ($qtdMarcas === 0 && \Illuminate\Support\Facades\Route::has('marca.create')) {
            $acoesPreRequisitosModelo[] = ['label' => 'Cadastrar marca', 'url' => route('marca.create')];
        }
        if ($qtdSegmentos === 0 && \Illuminate\Support\Facades\Route::has('segmento.create')) {
            $acoesPreRequisitosModelo[] = ['label' => 'Cadastrar segmento', 'url' => route('segmento.create')];
        }

        $podeCriarModelo = empty($faltasPreRequisitosModelo);
        $bloquearSalvarModelo = strtoupper($metodo ?? 'POST') === 'POST' && !$podeCriarModelo;
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
            title="Pre-requisitos pendentes para criar modelo"
            :chips="[
                ['label' => 'Marcas', 'value' => $qtdMarcas],
                ['label' => 'Segmentos', 'value' => $qtdSegmentos],
            ]"
            :missing-items="$faltasPreRequisitosModelo"
            :action-links="$acoesPreRequisitosModelo"
        />

        <div class="card card-modelo shadow-sm border-0">
            <div class="card-header card-header-modelo border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dados principais do modelo.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                    <div class="col-12">
                        <div class="modelo-section-title">Informacoes Basicas</div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="form-group form-group-modelo">
                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                label="Nome" value="{{ old('nome', $modelo->nome ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="form-group form-group-modelo">
                            <x-form-select name="marca_id" label="Marca" :options="$marcas" placeholder="Selecione..."
                                :selected="old('marca_id', isset($modelo->marca_id) && $modelo->marca_id ? $modelo->marca_id : null)"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="form-group form-group-modelo">
                            <x-form-select name="segmento_id" label="Segmento" :options="$segmentos" placeholder="Selecione..."
                                :selected="old('segmento_id', isset($modelo->segmento_id) && $modelo->segmento_id ? $modelo->segmento_id : null)"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <div class="form-group form-group-modelo">
                            <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione..."
                                :selected="old('ativo', isset($modelo->ativo) && $modelo->ativo ? $modelo->ativo : null)"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-save-modelo col-12 mt-2" {{ $bloquearSalvarModelo ? 'disabled' : '' }}>
                            Salvar
                        </button>
                        @if ($bloquearSalvarModelo)
                            <small class="text-danger d-block mt-2 text-center font-weight-semibold">
                                Cadastre os itens obrigatorios acima para liberar a criacao do modelo.
                            </small>
                        @endif
                    </div>
                </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-modelo {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-modelo {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-modelo h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .modelo-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-modelo {
        margin-bottom: 1rem;
    }

    .card-modelo .form-control,
    .card-modelo .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-modelo .form-control:focus,
    .card-modelo .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-modelo {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-modelo {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-modelo h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-modelo p {
            font-size: .85rem;
        }

        .modelo-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-modelo {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-modelo {
            margin-bottom: .8rem;
        }

        .card-modelo .form-control,
        .card-modelo .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script></script>
@stop
