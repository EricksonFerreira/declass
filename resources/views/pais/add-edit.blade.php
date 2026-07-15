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
        <div class="card card-pais shadow-sm border-0">
            <div class="card-header card-header-pais border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dados principais do pais para uso no sistema.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                    <div class="col-12">
                        <div class="pais-section-title">Informações Básicas</div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="form-group form-group-pais">
                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                label="Nome" value="{{ old('nome', $pais->nome ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <div class="form-group form-group-pais">
                            <x-form-input type="text" class="form-control form-control-sm text-uppercase" id="sigla" name="sigla"
                                label="Sigla" maxlength="2" value="{{ old('sigla', $pais->sigla ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-8">
                        <div class="form-group form-group-pais">
                            <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                :selected="old('ativo', isset($pais->ativo) && $pais->ativo ? $pais->ativo : null)"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-save-pais col-12 mt-2">
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
    .card-pais {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-pais {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-pais h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .pais-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-pais {
        margin-bottom: 1rem;
    }

    .card-pais .form-control,
    .card-pais .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-pais .form-control:focus,
    .card-pais .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-pais {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-pais {
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

        .card-header-pais h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-pais p {
            font-size: .85rem;
        }

        .pais-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-pais {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-pais {
            margin-bottom: .8rem;
        }

        .card-pais .form-control,
        .card-pais .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script>
        $(function () {
            $('#sigla').on('input', function () {
                const valor = ($(this).val() || '')
                    .toUpperCase()
                    .replace(/[^A-Z]/g, '')
                    .slice(0, 2);

                $(this).val(valor);
            });
        });
    </script>
@stop
