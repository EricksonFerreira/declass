@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
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
        <div class="card card-termo-consentimento shadow-sm border-0">
            <div class="card-header card-header-termo-consentimento border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure nome, status e texto do termo de consentimento.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                    <div class="col-12">
                        <div class="termo-consentimento-section-title">Informacoes Basicas</div>
                    </div>

                    <div class="col-lg-8 col-md-12">
                        <div class="form-group form-group-termo-consentimento">
                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                label="Nome" value="{{ old('nome', $termo_consentimento->nome ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-12">
                        <div class="form-group form-group-termo-consentimento">
                            <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                :selected="old(
                                    'ativo',
                                    isset($termo_consentimento->ativo) && $termo_consentimento->ativo
                                        ? $termo_consentimento->ativo
                                        : null,
                                )"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="termo-consentimento-section-title">Conteudo do Termo</div>
                    </div>

                    <div class="col-12">
                        <div class="form-group form-group-termo-consentimento">
                            <label for="texto">Texto</label>
                            <textarea name="texto" id="texto" class="form-control form-control-sm termo-textarea" rows="12" required>{{ old('texto', isset($termo_consentimento->texto) && $termo_consentimento->texto ? $termo_consentimento->texto : null) }}</textarea>
                            <small class="text-muted d-block mt-1">
                                Escreva o texto completo que sera apresentado para o aceite.
                            </small>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-save-termo-consentimento col-12 mt-2">
                            Salvar
                        </button>
                    </div>
                </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-termo-consentimento {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-termo-consentimento {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-termo-consentimento h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .termo-consentimento-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-termo-consentimento {
        margin-bottom: 1rem;
    }

    .card-termo-consentimento .form-control,
    .card-termo-consentimento .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-termo-consentimento .form-control:focus,
    .card-termo-consentimento .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .termo-textarea {
        min-height: 260px;
        resize: vertical;
        line-height: 1.45;
    }

    .btn-save-termo-consentimento {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-termo-consentimento {
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

        .card-header-termo-consentimento h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-termo-consentimento p {
            font-size: .85rem;
        }

        .termo-consentimento-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-termo-consentimento {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-termo-consentimento {
            margin-bottom: .8rem;
        }

        .card-termo-consentimento .form-control,
        .card-termo-consentimento .form-select {
            min-height: 36px;
            font-size: 13px;
        }

        .termo-textarea {
            min-height: 220px;
        }
    }
</style>
@stop

@section('js')
    <script></script>
@stop
