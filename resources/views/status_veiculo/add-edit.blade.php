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
        <div class="card card-status-veiculo shadow-sm border-0">
            <div class="card-header card-header-status-veiculo border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure nome, cor visual e status do veículo.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-status-veiculo" method="POST" action="{{ $rota }}">
                    @if (strtoupper($metodo ?? '') === 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <div class="status-veiculo-section-title">Informações básicas</div>
                    <div class="form-row">
                        <div class="col-lg-5 col-md-6">
                            <div class="form-group form-group-status-veiculo">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome" value="{{ old('nome', $status_veiculo->nome ?? '') }}" required />
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-status-veiculo">
                                <label for="cor" class="form-label-status-veiculo">Cor</label>
                                @php
                                    $corSelecionada = old('cor', $status_veiculo->cor ?? 'secondary');
                                @endphp
                                <select name="cor" id="cor" class="form-control form-control-sm" required>
                                    @foreach ($opcoesCor as $valor => $texto)
                                        <option value="{{ $valor }}" {{ $corSelecionada === $valor ? 'selected' : '' }}>
                                            {{ $texto }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-form-errors name="cor" />
                                <div class="status-veiculo-preview mt-2">
                                    <span id="status-veiculo-preview-badge" class="badge badge-xs bg-{{ $corSelecionada }}">
                                        {{ old('nome', $status_veiculo->nome ?? 'Pré-visualização') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <div class="form-group form-group-status-veiculo">
                                <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                    :selected="old('ativo', isset($status_veiculo->ativo) && $status_veiculo->ativo ? $status_veiculo->ativo : null)"
                                    class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-status-veiculo col-12 mt-2">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-status-veiculo {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-status-veiculo {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-status-veiculo h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .status-veiculo-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-status-veiculo {
        margin-bottom: 1rem;
    }

    .form-label-status-veiculo {
        display: block;
        margin-bottom: .45rem;
        font-size: .86rem;
        font-weight: 600;
        color: #334155;
    }

    .card-status-veiculo .form-control,
    .card-status-veiculo .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-status-veiculo .form-control:focus,
    .card-status-veiculo .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .status-veiculo-preview .badge {
        font-size: .78rem;
        padding: .45rem .7rem;
        border-radius: 999px;
    }

    .btn-save-status-veiculo {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-status-veiculo {
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

        .card-header-status-veiculo h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-status-veiculo p {
            font-size: .85rem;
        }

        .status-veiculo-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-status-veiculo {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-status-veiculo {
            margin-bottom: .8rem;
        }

        .card-status-veiculo .form-control,
        .card-status-veiculo .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
<script>
    (function () {
        const nome = document.getElementById('nome');
        const cor = document.getElementById('cor');
        const badge = document.getElementById('status-veiculo-preview-badge');

        function syncPreview() {
            if (!badge || !cor) return;
            badge.className = 'badge badge-xs bg-' + (cor.value || 'secondary');
            badge.textContent = (nome?.value || '').trim() || 'Pré-visualização';
        }

        nome?.addEventListener('input', syncPreview);
        cor?.addEventListener('change', syncPreview);
        syncPreview();
    })();
</script>
@stop
