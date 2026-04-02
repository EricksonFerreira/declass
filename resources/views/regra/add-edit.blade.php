@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $oldPermissions = old('permissions');
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
        <div class="card card-regra shadow-sm border-0">
            <div class="card-header card-header-regra border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure nome, politica de seguranca e permissoes da regra.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                    <div class="col-12">
                        <div class="regra-section-title">Informacoes Basicas</div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="form-group form-group-regra">
                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="name"
                                label="Nome" value="{{ old('name', $regra->name ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="form-group form-group-regra">
                            <x-form-input type="text" class="form-control form-control-sm" id="guard_name" name="guard_name"
                                label="Nome da Politica de Seguranca" value="{{ old('guard_name', $regra->guard_name ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="regra-section-title">Permissoes</div>
                    </div>

                    <div class="col-12">
                        <div class="permissoes-wrap">
                            <div class="permissoes-head">
                                <span><i class="fa fa-shield-alt mr-1"></i> Selecione as permissoes da regra</span>
                                <small class="text-muted">
                                    <span id="permissoes-selecionadas">0</span> selecionada(s)
                                </small>
                            </div>

                            <div class="permissoes-grid">
                                @foreach ($permissoes as $permissao)
                                    @php
                                        $checked = is_array($oldPermissions)
                                            ? in_array($permissao->id, $oldPermissions)
                                            : !empty($permissao->checked);
                                    @endphp
                                    <label class="permissao-item" for="permission-{{ $permissao->id }}">
                                        <input class="permissao-item__input" type="checkbox" name="permissions[]"
                                            value="{{ $permissao->id }}" id="permission-{{ $permissao->id }}"
                                            {{ $checked ? 'checked' : '' }}>
                                        <span class="permissao-item__label">{{ $permissao->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-form-errors name="permissions" />
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-save-regra col-12 mt-2">
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
    .card-regra {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-regra {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-regra h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .regra-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-regra {
        margin-bottom: 1rem;
    }

    .card-regra .form-control,
    .card-regra .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-regra .form-control:focus,
    .card-regra .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .permissoes-wrap {
        border: 1px solid #dbe6f3;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .permissoes-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding: 10px 12px;
        border-bottom: 1px solid #e5edf7;
        background: #f9fbfe;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
    }

    .permissoes-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        padding: 12px;
        max-height: 380px;
        overflow-y: auto;
    }

    .permissao-item {
        margin: 0;
        border: 1px solid #d8e1ec;
        border-radius: 10px;
        padding: 8px 10px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        cursor: pointer;
        background: #fff;
        transition: all .15s ease;
    }

    .permissao-item:hover {
        border-color: #b9c9e0;
        background: #f8fbff;
    }

    .permissao-item__input {
        margin-top: 2px;
        flex: 0 0 auto;
    }

    .permissao-item__label {
        font-size: 12px;
        font-weight: 600;
        color: #1f2937;
        line-height: 1.3;
        word-break: break-word;
    }

    .btn-save-regra {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 1199.98px) {
        .permissoes-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .card-header-regra {
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

        .card-header-regra h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-regra p {
            font-size: .85rem;
        }

        .regra-section-title {
            font-size: .9rem;
        }

        .permissoes-grid {
            grid-template-columns: 1fr;
            max-height: 320px;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-regra {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-regra {
            margin-bottom: .8rem;
        }

        .card-regra .form-control,
        .card-regra .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script>
        $(function () {
            const $checkboxes = $('input[name="permissions[]"]');
            const $counter = $('#permissoes-selecionadas');

            function atualizarQuantidade() {
                $counter.text($checkboxes.filter(':checked').length);
            }

            $checkboxes.on('change', atualizarQuantidade);
            atualizarQuantidade();
        });
    </script>
@stop
