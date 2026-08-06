@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :subtitle="$subtitulo">
        <a href="{{ route('planos.hub') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-th-large mr-1"></i> Central
        </a>
        <a href="{{ route('plano.create') }}" class="btn btn-xs btn-primary">
            <i class="fa fa-plus" style="font-size:10px"></i> Novo Plano
        </a>
    </x-page-title>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <x-page-header-card :showHeader="false">
        <div class="plano-index-toolbar">
            <div class="plano-index-kpi">
                <span>Total de planos</span>
                <strong>{{ $planos->count() }}</strong>
            </div>
            <div class="plano-index-kpi">
                <span>Ativos</span>
                <strong>{{ $planos->where('ativo', true)->count() }}</strong>
            </div>
            <div class="plano-index-kpi">
                <span>Assinaturas vinculadas</span>
                <strong>{{ $planos->sum('assinaturas_count') }}</strong>
            </div>
        </div>

        <div class="plano-index-grid">
            @forelse ($planos as $plano)
                @php
                    $storageMb = $plano->limite_armazenamento_mb ?? 0;
                    $storageTxt = $storageMb >= 1024 ? number_format($storageMb / 1024, 1, ',', '.') . ' GB' : $storageMb . ' MB';
                    $adicionais = collect([
                        ['label' => 'Veículo', 'valor' => $plano->valor_veiculo_adicional ?? null],
                        ['label' => 'Filial', 'valor' => $plano->valor_filial_adicional ?? null],
                        ['label' => 'Armazenamento', 'valor' => $plano->valor_armazenamento_adicional ?? null],
                    ])->filter(fn ($item) => filled($item['valor']));
                @endphp
                <article class="plano-card {{ $plano->destaque ? 'plano-card--featured' : '' }}" onclick="window.location='{{ route('plano.show', $plano) }}';" role="link" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){window.location='{{ route('plano.show', $plano) }}';}">
                    <div class="plano-card__accent"></div>
                    <div class="plano-card__head">
                        <div>
                            <div class="plano-card__name">{{ $plano->nome }}</div>
                            <div class="plano-card__slug">{{ $plano->slug }}</div>
                        </div>
                        <div class="plano-card__status">
                            <span class="badge badge-{{ $plano->ativo ? 'success' : 'secondary' }}">
                                {{ $plano->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>

                    <div class="plano-card__prices">
                        <div>
                            <span>Mensal</span>
                            <strong>R$ {{ number_format((float) $plano->valor_mensal, 2, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span>Anual</span>
                            <strong>R$ {{ number_format((float) $plano->valor_anual, 2, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="plano-card__meta">
                        <div><i class="fas fa-car"></i> {{ $plano->limite_veiculos_texto }}</div>
                        <div><i class="fas fa-database"></i> {{ $storageTxt }}</div>
                        <div><i class="fas fa-users"></i> {{ $plano->assinaturas_count }} assinatura(s)</div>
                        <div><i class="fas fa-user-shield"></i> {{ $plano->limite_permissoes_por_regra ? $plano->limite_permissoes_por_regra . ' permissões' : 'Sem limite de permissões' }}</div>
                    </div>

                    @if($adicionais->isNotEmpty())
                        <div class="plano-card__addons">
                            @foreach ($adicionais as $item)
                                <span class="plano-addon-pill">{{ $item['label'] }}: R$ {{ number_format((float) $item['valor'], 2, ',', '.') }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="plano-card__features">
                        <span class="plano-feature {{ $plano->site_vendas ? 'is-on' : 'is-off' }}"><i class="fas fa-globe"></i> Site</span>
                        <span class="plano-feature {{ $plano->modulo_multas ? 'is-on' : 'is-off' }}"><i class="fas fa-file-invoice"></i> Multas</span>
                        <span class="plano-feature {{ $plano->modulo_nfse ? 'is-on' : 'is-off' }}"><i class="fas fa-receipt"></i> NFS-e</span>
                        <span class="plano-feature {{ $plano->multi_estacao ? 'is-on' : 'is-off' }}"><i class="fas fa-map-marker-alt"></i> Filiais</span>
                    </div>

                    <div class="plano-card__actions" onclick="event.stopPropagation();">
                        <a href="{{ route('plano.show', $plano) }}" class="btn btn-xs btn-primary">
                            <i class="fas fa-eye mr-1"></i> Ver
                        </a>
                        <a href="{{ route('plano.edit', $plano) }}" class="btn btn-xs btn-outline-secondary">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                        <a href="{{ route('plano.acessos', $plano) }}" class="btn btn-xs btn-outline-primary">
                            <i class="fas fa-user-shield mr-1"></i> Acessos
                        </a>
                        @if ($plano->assinaturas_count === 0)
                            <form action="{{ route('plano.destroy', $plano) }}" method="POST" onsubmit="return confirm('Remover o plano {{ addslashes($plano->nome) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="plano-empty">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Nenhum plano cadastrado ainda.</strong>
                        <div>Crie o primeiro plano para começar.</div>
                    </div>
                    <a href="{{ route('plano.create') }}" class="btn btn-primary btn-sm ml-auto">Criar plano</a>
                </div>
            @endforelse
        </div>
    </x-page-header-card>
@stop

@section('css')
<style>
    .plano-index-toolbar {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .plano-index-kpi {
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 14px 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .plano-index-kpi span {
        display: block;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .plano-index-kpi strong {
        font-size: 1.3rem;
        font-weight: 800;
        color: #0f172a;
    }

    .plano-index-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
        gap: 14px;
    }

    .plano-card {
        position: relative;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
        display: grid;
        gap: 12px;
    }

    .plano-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.10);
    }

    .plano-card--featured {
        border-color: #2563eb;
        box-shadow: 0 14px 30px rgba(37, 99, 235, 0.10);
    }

    .plano-card__accent {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 18px 18px 0 0;
        background: linear-gradient(90deg, #2563eb 0%, #38bdf8 100%);
    }

    .plano-card__head {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
    }

    .plano-card__name {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .plano-card__slug {
        font-size: .78rem;
        color: #64748b;
        margin-top: 2px;
    }

    .plano-card__prices {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .plano-card__prices > div,
    .plano-card__addons {
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
    }

    .plano-card__prices span,
    .plano-addon-pill {
        display: block;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 700;
    }

    .plano-card__prices strong {
        font-size: 1.08rem;
        font-weight: 800;
        color: #0f172a;
    }

    .plano-card__meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px 12px;
        font-size: .82rem;
        color: #334155;
    }

    .plano-card__meta div {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .plano-card__meta i {
        color: #2563eb;
        width: 14px;
        text-align: center;
    }

    .plano-card__addons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .plano-addon-pill {
        margin: 0;
        padding: 6px 8px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #dbeafe;
    }

    .plano-card__features {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .plano-feature {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .plano-feature.is-on {
        background: #dcfce7;
        color: #166534;
    }

    .plano-feature.is-off {
        background: #f1f5f9;
        color: #64748b;
    }

    .plano-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .plano-card__actions form {
        margin: 0;
    }

    .plano-empty {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 16px;
        padding: 16px;
        color: #1e3a8a;
    }

    .plano-empty i {
        font-size: 1.4rem;
    }

    @media (max-width: 991.98px) {
        .plano-index-toolbar {
            grid-template-columns: 1fr;
        }

        .plano-card__meta {
            grid-template-columns: 1fr;
        }
    }
</style>
@stop
