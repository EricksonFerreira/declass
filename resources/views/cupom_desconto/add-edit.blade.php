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
        <div class="card card-cupom-desconto shadow-sm border-0">
            <div class="card-header card-header-cupom-desconto border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure o nome, codigo e percentual do cupom de desconto.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-cupom" method="POST" action="{{ $rota }}">
                    @if (strtoupper($metodo ?? '') === 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="cupom-desconto-section-title">Informacoes Basicas</div>
                    <div class="form-row">
                        <div class="col-lg-5">
                            <div class="form-group form-group-cupom-desconto">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome" value="{{ old('nome', $cupom_desconto->nome ?? '') }}" required />
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <div class="form-group form-group-cupom-desconto">
                                <x-form-input type="text" class="form-control form-control-sm" id="codigo" name="codigo"
                                    maxlength="7" label="Codigo" value="{{ old('codigo', $cupom_desconto->codigo ?? '') }}" required />
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <div class="form-group form-group-cupom-desconto">
                                <x-form-input type="text" class="form-control form-control-sm" id="valor" name="valor"
                                    label="Valor" value="{{ old('valor', $cupom_desconto->valor ?? '') }}" required />
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <div class="form-group form-group-cupom-desconto">
                                <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                    :selected="old('ativo', isset($cupom_desconto->ativo) && $cupom_desconto->ativo ? $cupom_desconto->ativo : null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-cupom-desconto col-12 mt-2">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-cupom-desconto {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-cupom-desconto {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-cupom-desconto h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .cupom-desconto-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-cupom-desconto {
        margin-bottom: 1rem;
    }

    .card-cupom-desconto .form-control,
    .card-cupom-desconto .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-cupom-desconto .form-control:focus,
    .card-cupom-desconto .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-cupom-desconto {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-cupom-desconto {
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

        .card-header-cupom-desconto h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-cupom-desconto p {
            font-size: .85rem;
        }

        .cupom-desconto-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-cupom-desconto {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-cupom-desconto {
            margin-bottom: .8rem;
        }

        .card-cupom-desconto .form-control,
        .card-cupom-desconto .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(function() {
        $('#valor').mask('##0,00%', { reverse: true });

        $('#formulario-cupom').on('submit', function() {
            const valorAtual = $('#valor').val() || '';
            $('#valor').val(valorAtual.replace('%', ''));
        });
    });
</script>
@stop
