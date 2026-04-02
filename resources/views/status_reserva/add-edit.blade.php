@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $coresStatus = [
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
            'teal' => 'Verde-azulado',
            'lime' => 'Verde Limao',
            'danger' => 'Vermelho',
        ];
        $corSelecionada = old('cor', $status_reserva->cor ?? 'primary');
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
        <div class="card card-status-reserva shadow-sm border-0">
            <div class="card-header card-header-status-reserva border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure nome, status e cor visual para uso nas reservas.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                    <div class="col-12">
                        <div class="status-reserva-section-title">Informacoes Basicas</div>
                    </div>

                    <div class="col-lg-7 col-md-6">
                        <div class="form-group form-group-status-reserva">
                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                label="Nome" value="{{ old('nome', $status_reserva->nome ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-5 col-md-6">
                        <div class="form-group form-group-status-reserva">
                            <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                :selected="old('ativo', isset($status_reserva->ativo) && $status_reserva->ativo ? $status_reserva->ativo : null)"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="status-reserva-section-title">Aparencia e Descricao</div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="form-group form-group-status-reserva">
                            <label for="cor">Cor</label>
                            <select name="cor" id="cor" class="form-control form-control-sm" required>
                                @foreach ($coresStatus as $valorCor => $labelCor)
                                    <option value="{{ $valorCor }}" {{ $corSelecionada === $valorCor ? 'selected' : '' }}>
                                        {{ $labelCor }}
                                    </option>
                                @endforeach
                            </select>
                            <x-form-errors name="cor" />
                        </div>
                    </div>

                    <div class="col-lg-8 col-md-6">
                        <div class="status-cor-preview-wrap">
                            <div class="status-cor-preview-label">Preview da cor selecionada</div>
                            <div class="status-cor-preview-content">
                                <span id="status-cor-preview-dot" class="status-cor-preview-dot bg-{{ $corSelecionada }}"></span>
                                <span id="status-cor-preview-text" class="status-cor-preview-text">
                                    {{ $coresStatus[$corSelecionada] ?? 'Cor selecionada' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group form-group-status-reserva">
                            <label for="descricao">Descricao</label>
                            <textarea name="descricao" id="descricao" class="form-control form-control-sm text-xs" rows="5">{{ old('descricao', isset($status_reserva->descricao) && $status_reserva->descricao ? $status_reserva->descricao : '') }}</textarea>
                            <x-form-errors name="descricao" />
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-save-status-reserva col-12 mt-2">
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
    .card-status-reserva {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-status-reserva {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-status-reserva h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .status-reserva-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-status-reserva {
        margin-bottom: 1rem;
    }

    .card-status-reserva .form-control,
    .card-status-reserva .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-status-reserva .form-control:focus,
    .card-status-reserva .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .status-cor-preview-wrap {
        border: 1px solid #dbe6f3;
        border-radius: 12px;
        padding: 12px;
        background: #f9fbff;
        min-height: 74px;
        margin-top: 27px;
    }

    .status-cor-preview-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 6px;
    }

    .status-cor-preview-content {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-cor-preview-dot {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        box-shadow: 0 0 0 2px #ffffff, 0 0 0 3px #d6dfec;
    }

    .status-cor-preview-text {
        font-size: 13px;
        font-weight: 700;
        color: #1f2937;
    }

    .btn-save-status-reserva {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-status-reserva {
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

        .card-header-status-reserva h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-status-reserva p {
            font-size: .85rem;
        }

        .status-reserva-section-title {
            font-size: .9rem;
        }

        .status-cor-preview-wrap {
            margin-top: 0;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-status-reserva {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-status-reserva {
            margin-bottom: .8rem;
        }

        .card-status-reserva .form-control,
        .card-status-reserva .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script>
        $(function () {
            const $cor = $('#cor');
            const $previewDot = $('#status-cor-preview-dot');
            const $previewText = $('#status-cor-preview-text');
            const classes = [
                'bg-warning',
                'bg-primary',
                'bg-info',
                'bg-cyan',
                'bg-secondary',
                'bg-indigo',
                'bg-orange',
                'bg-black',
                'bg-purple',
                'bg-pink',
                'bg-success',
                'bg-teal',
                'bg-lime',
                'bg-danger',
            ];

            function atualizarPreviewCor() {
                const valor = $cor.val() || 'primary';
                const texto = $cor.find('option:selected').text().trim();
                $previewDot.removeClass(classes.join(' ')).addClass(`bg-${valor}`);
                $previewText.text(texto || 'Cor selecionada');
            }

            $cor.on('change', atualizarPreviewCor);
            atualizarPreviewCor();
        });
    </script>
@stop
