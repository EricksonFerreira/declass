@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $qtdCategoriasDano = is_countable($categorias ?? null) ? count($categorias) : 0;
        $faltasPreRequisitosDano = [];
        if ($qtdCategoriasDano === 0) {
            $faltasPreRequisitosDano[] = 'Cadastre pelo menos uma categoria de dano para criar danos.';
        }

        $acoesPreRequisitosDano = [];
        if ($qtdCategoriasDano === 0 && \Illuminate\Support\Facades\Route::has('categoria_dano.create')) {
            $acoesPreRequisitosDano[] = ['label' => 'Cadastrar categoria de dano', 'url' => route('categoria_dano.create')];
        }

        $podeCriarDano = empty($faltasPreRequisitosDano);
        $bloquearSalvarDano = strtoupper($metodo ?? 'POST') === 'POST' && !$podeCriarDano;
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
            title="Pre-requisitos pendentes para criar dano"
            :chips="[
                ['label' => 'Categorias de Dano', 'value' => $qtdCategoriasDano],
            ]"
            :missing-items="$faltasPreRequisitosDano"
            :action-links="$acoesPreRequisitosDano"
        />

        <div class="card card-dano shadow-sm border-0">
            <div class="card-header card-header-dano border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dados principais do dano.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-dano" method="POST" action="{{ $rota }}">
                    @if (strtoupper($metodo ?? '') === 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="dano-section-title">Informacoes Basicas</div>
                    <div class="form-row">
                        <div class="col-lg-6">
                            <div class="form-group form-group-dano">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome" value="{{ old('nome', $dano->nome ?? '') }}" required />
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-dano">
                                <x-form-select name="categoria_id" label="Categoria" id="categoria" :options="$categorias"
                                    placeholder="Selecione uma categoria"
                                    :selected="old('categoria_id', isset($dano->categoria_id) && $dano->categoria_id ? $dano->categoria_id : null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-dano">
                                <x-form-select name="localizacao" label="Localizacao" id="localizacao" :options="['interior' => 'Interior', 'exterior' => 'Exterior']"
                                    placeholder="Selecione uma localizacao"
                                    :selected="old('localizacao', isset($dano->localizacao) && $dano->localizacao ? $dano->localizacao : null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-lg-3 col-md-5">
                            <div class="form-group form-group-dano mb-0">
                                <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                    :selected="old('ativo', isset($dano->ativo) && $dano->ativo ? $dano->ativo : 'S')"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-dano col-12 mt-3" {{ $bloquearSalvarDano ? 'disabled' : '' }}>
                        Salvar
                    </button>
                    @if ($bloquearSalvarDano)
                        <small class="text-danger d-block mt-2 text-center font-weight-semibold">
                            Cadastre categoria de dano para liberar a criacao de dano.
                        </small>
                    @endif
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-dano {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-dano {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-dano h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .dano-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-dano {
        margin-bottom: 1rem;
    }

    .card-dano .form-control,
    .card-dano .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-dano .form-control:focus,
    .card-dano .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-dano {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-dano {
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

        .card-header-dano h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-dano p {
            font-size: .85rem;
        }

        .dano-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-dano {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-dano {
            margin-bottom: .8rem;
        }

        .card-dano .form-control,
        .card-dano .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script></script>
@stop
