@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    <div class="plano-edit-page">
        <x-page-header-card :title="$titulo" :subtitle="$subtitulo">
            <x-slot:actions>
                <div class="plano-edit-actions">
                    <a href="{{ route('plano.index') }}" class="btn btn-xs btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </a>
                    <a href="{{ route('plano.acessos', $plano) }}" class="btn btn-xs btn-outline-primary">
                        <i class="fas fa-user-shield mr-1"></i> Política de Acesso
                    </a>
                </div>
            </x-slot:actions>

            <div class="plano-edit-shell">
                <aside class="plano-edit-summary">
                    <div class="plano-summary-card plano-summary-card--main">
                        <div class="plano-summary-kicker">Plano atual</div>
                        <div class="plano-summary-name">{{ $plano->nome }}</div>
                        <div class="plano-summary-meta">
                            <span><i class="fas fa-tag"></i> {{ $plano->slug }}</span>
                            <span><i class="fas fa-car"></i> {{ $plano->limite_veiculos_texto ?? 'Ilimitado' }}</span>
                            <span><i class="fas fa-database"></i> {{ $plano->limite_armazenamento_texto ?? 'Ilimitado' }}</span>
                        </div>
                    </div>

                    <div class="plano-summary-card">
                        <div class="plano-summary-kicker">Valores</div>
                        <div class="plano-summary-grid">
                            <div>
                                <span>Mensal</span>
                                <strong>R$ {{ number_format((float) ($plano->valor_mensal ?? 0), 2, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span>Anual</span>
                                <strong>R$ {{ number_format((float) ($plano->valor_anual ?? 0), 2, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="plano-summary-card">
                        <div class="plano-summary-kicker">Adicionais</div>
                        <div class="plano-summary-list">
                            <div><span>Veículo</span><strong>R$ {{ number_format((float) ($plano->valor_veiculo_adicional ?? 0), 2, ',', '.') }}</strong></div>
                            <div><span>Filial</span><strong>R$ {{ number_format((float) ($plano->valor_filial_adicional ?? 0), 2, ',', '.') }}</strong></div>
                            <div><span>Armazenamento</span><strong>R$ {{ number_format((float) ($plano->valor_armazenamento_adicional ?? 0), 2, ',', '.') }}</strong></div>
                        </div>
                    </div>
                </aside>

                <section class="plano-edit-form">
                    <form action="{{ route('plano.update', $plano) }}" method="POST" id="formPlano">
                        @csrf @method('PUT')

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @include('plano._form')

                        <div class="plano-edit-footer">
                            <a href="{{ route('plano.index') }}" class="btn btn-light">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Salvar alterações
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </x-page-header-card>
    </div>
@stop

@section('css')
<style>
    .plano-edit-page {
        padding-bottom: 18px;
    }

    .plano-edit-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .plano-edit-shell {
        display: grid;
        grid-template-columns: minmax(260px, 320px) minmax(0, 1fr);
        gap: 16px;
        align-items: start;
    }

    .plano-edit-summary {
        display: grid;
        gap: 12px;
        position: sticky;
        top: 12px;
    }

    .plano-summary-card {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .plano-summary-card--main {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        border: none;
    }

    .plano-summary-kicker {
        font-size: .72rem;
        letter-spacing: .12em;
        text-transform: uppercase;
        font-weight: 700;
        opacity: .7;
        margin-bottom: 6px;
    }

    .plano-summary-name {
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 8px;
    }

    .plano-summary-meta {
        display: flex;
        flex-direction: column;
        gap: 6px;
        font-size: .85rem;
        color: #cbd5e1;
    }

    .plano-summary-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .plano-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .plano-summary-grid span,
    .plano-summary-list span {
        display: block;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        margin-bottom: 4px;
    }

    .plano-summary-grid strong,
    .plano-summary-list strong {
        font-size: .98rem;
        font-weight: 800;
        color: #0f172a;
    }

    .plano-summary-list {
        display: grid;
        gap: 10px;
    }

    .plano-summary-list > div {
        padding: 10px 12px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
    }

    .plano-edit-form {
        min-width: 0;
    }

    .plano-edit-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 14px;
    }

    .plano-edit-footer .btn {
        min-width: 150px;
    }

    .plano-edit-page .card {
        border-radius: 16px;
    }

    .plano-edit-page .form-control,
    .plano-edit-page textarea {
        border-radius: 10px;
    }

    @media (max-width: 991.98px) {
        .plano-edit-shell {
            grid-template-columns: 1fr;
        }

        .plano-edit-summary {
            position: static;
        }

        .plano-edit-footer {
            flex-direction: column-reverse;
        }

        .plano-edit-footer .btn {
            width: 100%;
        }

        .plano-edit-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>
@stop
