@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $storageMb = $plano->limite_armazenamento_mb ?? 0;
    $storageTxt = $storageMb >= 1024 ? number_format($storageMb / 1024, 1, ',', '.') . ' GB' : $storageMb . ' MB';
    $assinaturasRecentes = $plano->assinaturas->take(8);
@endphp

<div class="plano-show-page">
    <div class="plano-show-hero">
        <div class="plano-show-hero__accent"></div>
        <div class="plano-show-hero__body">
            <div class="plano-show-hero__left">
                <div class="plano-show-hero__eyebrow">
                    <i class="fas fa-layer-group"></i>
                    Plano #{{ $plano->id }}
                </div>
                <h1 class="plano-show-hero__title">{{ $plano->nome }}</h1>
                <div class="plano-show-hero__meta">
                    <span><i class="fas fa-tag"></i>{{ $plano->slug }}</span>
                    <span><i class="fas fa-calendar-plus"></i>Criado em {{ $plano->created_at?->format('d/m/Y H:i') ?? '-' }}</span>
                    <span><i class="fas fa-star"></i>{{ $plano->destaque ? 'Plano em destaque' : 'Plano comum' }}</span>
                </div>
            </div>
            <div class="plano-show-hero__right">
                <span class="plano-show-status {{ $plano->ativo ? 'is-success' : 'is-secondary' }}">
                    {{ $plano->ativo ? 'ATIVO' : 'INATIVO' }}
                </span>
                <div class="plano-show-price">
                    <div>
                        <span>Mensal</span>
                        <strong>R$ {{ number_format((float) $plano->valor_mensal, 2, ',', '.') }}</strong>
                    </div>
                    <div class="divider"></div>
                    <div>
                        <span>Anual</span>
                        <strong>R$ {{ number_format((float) $plano->valor_anual, 2, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="plano-show-grid">
        <div class="plano-show-main">
            <div class="plano-show-card">
                <div class="plano-show-card__head">
                    <h2>Visão Geral</h2>
                </div>
                <div class="plano-show-info-grid">
                    <div class="plano-info-box"><span>Limite de veículos</span><strong>{{ $plano->limite_veiculos_texto }}</strong></div>
                    <div class="plano-info-box"><span>Limite de armazenamento</span><strong>{{ $plano->limite_armazenamento_texto }}</strong></div>
                    <div class="plano-info-box"><span>Usuários</span><strong>{{ $plano->limite_usuarios_texto ?? 'Ilimitado' }}</strong></div>
                    <div class="plano-info-box"><span>Permissões por perfil</span><strong>{{ $plano->limite_permissoes_por_regra ? $plano->limite_permissoes_por_regra : 'Sem limite' }}</strong></div>
                </div>
            </div>

            <div class="plano-show-card">
                <div class="plano-show-card__head">
                    <h2>Adicionais</h2>
                </div>
                <div class="plano-show-addons">
                    <div class="plano-add-card">
                        <span>Veículo adicional</span>
                        <strong>R$ {{ number_format((float) ($plano->valor_veiculo_adicional ?? 0), 2, ',', '.') }}</strong>
                    </div>
                    <div class="plano-add-card">
                        <span>Quantidade de filiais</span>
                        <strong>{{ $plano->quantidade_filiais ?? 0 }}</strong>
                    </div>
                    <div class="plano-add-card">
                        <span>Filial adicional</span>
                        <strong>R$ {{ number_format((float) ($plano->valor_filial_adicional ?? 0), 2, ',', '.') }}</strong>
                    </div>
                    <div class="plano-add-card">
                        <span>Armazenamento adicional</span>
                        <strong>R$ {{ number_format((float) ($plano->valor_armazenamento_adicional ?? 0), 2, ',', '.') }}</strong>
                    </div>
                    <div class="plano-add-card">
                        <span>Qtd. armazenamento adicional</span>
                        <strong>{{ $plano->quantidade_armazenamento_adicional ?? 0 }}</strong>
                    </div>
                </div>
            </div>

            <div class="plano-show-card">
                <div class="plano-show-card__head">
                    <h2>Recursos</h2>
                </div>
                <div class="plano-show-features">
                    <div class="feature-chip {{ $plano->site_vendas ? 'is-on' : 'is-off' }}">Site de vendas</div>
                    <div class="feature-chip {{ $plano->modulo_multas ? 'is-on' : 'is-off' }}">Multas</div>
                    <div class="feature-chip {{ $plano->modulo_nfse ? 'is-on' : 'is-off' }}">NFS-e</div>
                    <div class="feature-chip {{ $plano->multi_estacao ? 'is-on' : 'is-off' }}">Multi-estação</div>
                    <div class="feature-chip {{ $plano->api_integracao ? 'is-on' : 'is-off' }}">API</div>
                    <div class="feature-chip is-on">Reservas</div>
                    <div class="feature-chip is-on">Contratos</div>
                    <div class="feature-chip is-on">Financeiro</div>
                </div>
            </div>

            <div class="plano-show-card">
                <div class="plano-show-card__head">
                    <h2>Itens do Plano</h2>
                </div>
                @if($plano->itens->isNotEmpty())
                    <div class="plano-items-table table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Descrição</th>
                                    <th style="width:130px;">Valor</th>
                                    <th style="width:90px;">Incluso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plano->itens as $item)
                                    <tr>
                                        <td>{{ $item->descricao }}</td>
                                        <td>R$ {{ number_format((float) ($item->valor ?? 0), 2, ',', '.') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $item->incluso ? 'success' : 'secondary' }}">
                                                {{ $item->incluso ? 'Sim' : 'Não' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="plano-empty">Nenhum item cadastrado nesse plano.</div>
                @endif
            </div>
        </div>

        <aside class="plano-show-side">
            <div class="plano-side-card">
                <h3>Resumo rápido</h3>
                <div class="plano-side-list">
                    <div><span>Assinaturas vinculadas</span><strong>{{ $plano->assinaturas_count ?? $plano->assinaturas->count() }}</strong></div>
                    <div><span>Armazenamento</span><strong>{{ $storageTxt }}</strong></div>
                    <div><span>Destaque</span><strong>{{ $plano->destaque ? 'Sim' : 'Não' }}</strong></div>
                    <div><span>Ordem</span><strong>{{ $plano->ordem ?? 0 }}</strong></div>
                </div>
                <div class="plano-side-actions">
                    <a href="{{ route('plano.edit', $plano) }}" class="btn btn-primary btn-block">Editar plano</a>
                    <a href="{{ route('plano.acessos', $plano) }}" class="btn btn-outline-primary btn-block">Política de acesso</a>
                </div>
            </div>

            <div class="plano-side-card">
                <h3>Assinaturas recentes</h3>
                @if($assinaturasRecentes->isNotEmpty())
                    <div class="plano-side-assinaturas">
                        @foreach($assinaturasRecentes as $assinatura)
                            <a href="{{ route('assinatura.show', $assinatura) }}" class="plano-assinatura-link">
                                <strong>{{ $assinatura->empresa?->nome ?? 'Empresa' }}</strong>
                                <span>{{ $assinatura->periodicidade_texto ?? ucfirst($assinatura->periodicidade ?? '-') }} • {{ $assinatura->status ?? '-' }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="plano-empty">Nenhuma assinatura vinculada.</div>
                @endif
            </div>
        </aside>
    </div>
</div>
@stop

@section('css')
<style>
    .plano-show-page {
        display: grid;
        gap: 16px;
        padding-bottom: 20px;
    }

    .plano-show-hero {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        box-shadow: 0 12px 32px rgba(15, 23, 42, .18);
    }

    .plano-show-hero__accent {
        height: 4px;
        background: linear-gradient(90deg, #2563eb 0%, #38bdf8 100%);
    }

    .plano-show-hero__body {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 24px 26px;
        flex-wrap: wrap;
    }

    .plano-show-hero__eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .14em;
        font-weight: 700;
        color: rgba(203, 213, 225, .85);
        margin-bottom: 6px;
    }

    .plano-show-hero__title {
        margin: 0;
        font-size: 1.9rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.15;
    }

    .plano-show-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 16px;
        margin-top: 8px;
        color: #cbd5e1;
        font-size: .84rem;
    }

    .plano-show-hero__meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .plano-show-hero__right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 14px;
    }

    .plano-show-status {
        display: inline-flex;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .plano-show-status.is-success {
        background: rgba(34, 197, 94, .16);
        color: #4ade80;
    }

    .plano-show-status.is-secondary {
        background: rgba(148, 163, 184, .15);
        color: #cbd5e1;
    }

    .plano-show-price {
        display: flex;
        align-items: center;
        gap: 0;
        background: rgba(255, 255, 255, .07);
        border: 1px solid rgba(255, 255, 255, .10);
        border-radius: 14px;
        overflow: hidden;
    }

    .plano-show-price > div {
        padding: 12px 18px;
        text-align: center;
    }

    .plano-show-price span {
        display: block;
        color: rgba(203, 213, 225, .80);
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .12em;
        margin-bottom: 4px;
    }

    .plano-show-price strong {
        display: block;
        color: #fff;
        font-size: 1.06rem;
        font-weight: 800;
    }

    .plano-show-price .divider {
        width: 1px;
        background: rgba(255, 255, 255, .08);
    }

    .plano-show-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(280px, .8fr);
        gap: 16px;
        align-items: start;
    }

    .plano-show-main,
    .plano-show-side {
        display: grid;
        gap: 14px;
    }

    .plano-show-card,
    .plano-side-card {
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
    }

    .plano-show-card__head h2,
    .plano-side-card h3 {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 12px;
    }

    .plano-show-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .plano-info-box,
    .plano-add-card,
    .plano-side-list > div {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 12px;
        padding: 12px;
    }

    .plano-info-box span,
    .plano-add-card span,
    .plano-side-list span {
        display: block;
        font-size: .72rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 700;
    }

    .plano-info-box strong,
    .plano-add-card strong,
    .plano-side-list strong {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .plano-show-addons {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .plano-show-features {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .feature-chip {
        padding: 8px 12px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
    }

    .feature-chip.is-on { background: #dcfce7; color: #166534; }
    .feature-chip.is-off { background: #f1f5f9; color: #64748b; }

    .plano-items-table .table {
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 0;
    }

    .plano-side-list {
        display: grid;
        gap: 10px;
    }

    .plano-side-actions {
        display: grid;
        gap: 8px;
        margin-top: 14px;
    }

    .plano-side-assinaturas {
        display: grid;
        gap: 8px;
    }

    .plano-assinatura-link {
        display: block;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 12px;
        padding: 10px 12px;
        text-decoration: none;
        color: inherit;
    }

    .plano-assinatura-link strong {
        display: block;
        font-size: .9rem;
        font-weight: 800;
        color: #0f172a;
    }

    .plano-assinatura-link span {
        display: block;
        margin-top: 2px;
        font-size: .76rem;
        color: #64748b;
    }

    .plano-empty {
        padding: 12px;
        border-radius: 12px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: .9rem;
    }

    @media (max-width: 991.98px) {
        .plano-show-grid,
        .plano-show-info-grid,
        .plano-show-addons {
            grid-template-columns: 1fr;
        }

        .plano-show-hero__body {
            padding: 20px;
        }

        .plano-show-hero__right {
            align-items: flex-start;
        }

        .plano-show-price {
            width: 100%;
        }
    }
</style>
@stop
