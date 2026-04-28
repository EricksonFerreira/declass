@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $corSelecionada = old('cor', isset($status_movimentacao->cor) && $status_movimentacao->cor ? $status_movimentacao->cor : 'primary');
        $opcoesCor = [
            'warning' => 'Amarelo',
            'primary' => 'Azul',
            'info' => 'Azul Claro',
            'cyan' => 'Ciano',
            'secondary' => 'Cinza',
            'indigo' => 'Indigo',
            'orange' => 'Laranja',
            'black' => 'Preto',
            'purple' => 'Roxo',
            'pink' => 'Rosa',
            'success' => 'Verde',
            'teal' => 'Verde Azulado',
            'lime' => 'Verde Limao',
            'danger' => 'Vermelho',
        ];
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
        <div class="card card-status-movimentacao shadow-sm border-0">
            <div class="card-header card-header-status-movimentacao border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure nome, cor visual e status da movimentacao.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-status-movimentacao" method="POST" action="{{ $rota }}">
                    @if (strtoupper($metodo ?? '') === 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="status-movimentacao-section-title">Informacoes Basicas</div>
                    <div class="form-row">
                        <div class="col-lg-5">
                            <div class="form-group form-group-status-movimentacao">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome" value="{{ old('nome', $status_movimentacao->nome ?? '') }}" required />
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-7">
                            <div class="form-group form-group-status-movimentacao">
                                <label for="cor" class="form-label-status-movimentacao">Cor</label>
                                <select name="cor" id="cor" class="form-control form-control-sm" required>
                                    @foreach ($opcoesCor as $valor => $texto)
                                        <option value="{{ $valor }}" {{ $corSelecionada === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                    @endforeach
                                </select>
                                <x-form-errors name="cor" />
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-5">
                            <div class="form-group form-group-status-movimentacao">
                                <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                    :selected="old('ativo', isset($status_movimentacao->ativo) && $status_movimentacao->ativo ? $status_movimentacao->ativo : null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-status-movimentacao col-12 mt-2">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-status-movimentacao {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-status-movimentacao {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-status-movimentacao h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .status-movimentacao-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-status-movimentacao {
        margin-bottom: 1rem;
    }

    .form-label-status-movimentacao {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .card-status-movimentacao .form-control,
    .card-status-movimentacao .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-status-movimentacao .form-control:focus,
    .card-status-movimentacao .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-status-movimentacao {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-status-movimentacao {
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

        .card-header-status-movimentacao h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-status-movimentacao p {
            font-size: .85rem;
        }

        .status-movimentacao-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-status-movimentacao {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-status-movimentacao {
            margin-bottom: .8rem;
        }

        .card-status-movimentacao .form-control,
        .card-status-movimentacao .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script></script>
    <x-page-tour tour-id="status-movimentacao-add-edit" :steps="[
        [
            'element' => '.card-header-status-movimentacao',
            'popover' => [
                'title' => 'Cadastro de status',
                'description' => 'Nesta tela você configura como um status de movimentação será exibido e utilizado no sistema.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => '#formulario-status-movimentacao',
            'popover' => [
                'title' => 'Formulário principal',
                'description' => 'Preencha os campos obrigatórios para criar ou atualizar um status de movimentação.',
                'side' => 'top',
            ],
        ],
        [
            'element' => '#nome',
            'popover' => [
                'title' => 'Nome do status',
                'description' => 'Defina um nome claro para identificar a etapa da movimentação, como Pendente, Em trânsito ou Concluído.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => '#cor',
            'popover' => [
                'title' => 'Cor visual',
                'description' => 'Escolha a cor usada para destacar esse status nas listas, badges e cartões de movimentação.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'select[name=\"ativo\"]',
            'popover' => [
                'title' => 'Status do cadastro',
                'description' => 'Controle se este status ficará disponível para uso nas movimentações futuras.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => '.btn-save-status-movimentacao',
            'popover' => [
                'title' => 'Salvar',
                'description' => 'Depois de preencher os campos, clique aqui para gravar o status da movimentação.',
                'side' => 'top',
            ],
        ],
    ]" />
@stop
