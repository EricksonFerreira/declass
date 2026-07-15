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
        <div class="card card-dia-util shadow-sm border-0">
            <div class="card-header card-header-dia-util border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dias uteis e o status de disponibilidade.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                    <div class="col-12">
                        <div class="dia-util-section-title">Informações Básicas</div>
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <div class="form-group form-group-dia-util">
                            <x-form-input type="number" class="form-control form-control-sm" min="0" max="7" id="dia" name="dia"
                                label="Dia" value="{{ old('dia', $dia_util->dia ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-8">
                        <div class="form-group form-group-dia-util">
                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                label="Nome" value="{{ old('nome', $dia_util->nome ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-12">
                        <div class="form-group form-group-dia-util">
                            <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                :selected="old('ativo', isset($dia_util->ativo) && $dia_util->ativo ? $dia_util->ativo : null)"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-save-dia-util col-12 mt-2">
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
    .card-dia-util {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-dia-util {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-dia-util h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .dia-util-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-dia-util {
        margin-bottom: 1rem;
    }

    .card-dia-util .form-control,
    .card-dia-util .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-dia-util .form-control:focus,
    .card-dia-util .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-dia-util {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-dia-util {
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

        .card-header-dia-util h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-dia-util p {
            font-size: .85rem;
        }

        .dia-util-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-dia-util {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-dia-util {
            margin-bottom: .8rem;
        }

        .card-dia-util .form-control,
        .card-dia-util .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script>
        $(function () {
            $('#dia').on('input', function () {
                let valor = parseInt($(this).val(), 10);

                if (isNaN(valor)) {
                    return;
                }

                if (valor < 0) {
                    valor = 0;
                }

                if (valor > 7) {
                    valor = 7;
                }

                $(this).val(valor);
            });
        });
    </script>
@stop
