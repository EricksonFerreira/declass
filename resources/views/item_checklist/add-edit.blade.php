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
        <div class="card card-item-checklist shadow-sm border-0">
            <div class="card-header card-header-item-checklist border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dados principais do item de checklist.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-item-checklist" method="POST" action="{{ $rota }}">
                    @if (strtoupper($metodo ?? '') === 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="item-checklist-section-title">Informacoes Basicas</div>
                    <div class="form-row">
                        <div class="col-lg-8 col-md-7">
                            <div class="form-group form-group-item-checklist">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome" value="{{ old('nome', $item_checklist->nome ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-5">
                            <div class="form-group form-group-item-checklist">
                                <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                    :selected="old('ativo', isset($item_checklist->ativo) && $item_checklist->ativo ? $item_checklist->ativo : null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-item-checklist col-12 mt-2">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-item-checklist {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-item-checklist {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-item-checklist h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .item-checklist-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-item-checklist {
        margin-bottom: 1rem;
    }

    .card-item-checklist .form-control,
    .card-item-checklist .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-item-checklist .form-control:focus,
    .card-item-checklist .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-item-checklist {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-item-checklist {
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

        .card-header-item-checklist h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-item-checklist p {
            font-size: .85rem;
        }

        .item-checklist-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-item-checklist {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-item-checklist {
            margin-bottom: .8rem;
        }

        .card-item-checklist .form-control,
        .card-item-checklist .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script></script>
@stop
