@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('plano_motorista_app.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <div class="plano-motorista-app-filter-sticky">
            <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
        </div>
    @endif

    <div class="card card-plano-motorista-app shadow-sm border-0">
        <div class="card-header card-header-plano-motorista-app border-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Cadastre e organize os planos especiais para motorista de app.</p>
                </div>

                <div class="d-flex flex-row align-items-center flex-wrap" style="gap:6px;">
                    <form action="{{ route('plano_motorista_app.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="[5,10,15,25,50,100]" :selectedPerPage="$perPage" />
                    </form>

                    <div class="plano-motorista-app-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i>Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i>Tabela
                        </button>
                    </div>

                    <div class="d-flex align-items-end">
                        {{ $planos->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 plano-motorista-app-index-body" data-view="cards">
            <div class="plano-motorista-app-cards">
                @forelse($planos as $plano)
                    @php
                        $ativo = $plano->ativo === 'S';
                        $accentCls = $ativo ? 'pm-status--green' : 'pm-status--red';
                    @endphp
                    <article class="pm {{ $accentCls }}" onclick="window.location='{{ route('plano_motorista_app.edit', $plano->id) }}';">
                        <div class="pm__accent"></div>

                        <div class="pm__head">
                            <div class="pm__avatar">{{ strtoupper(mb_substr($plano->nome ?? 'PM', 0, 2)) }}</div>
                            <div class="pm__head-info">
                                <div class="pm__id">PLANO #{{ $plano->id }}</div>
                                <div class="pm__title">{{ $plano->nome }}</div>
                                <div class="pm__sub">{{ $plano->periodo_label }}</div>
                            </div>
                            <div class="pm__badges">
                                <span class="pm__badge {{ $ativo ? 'pm__badge--green' : 'pm__badge--red' }}">
                                    {{ $ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>

                        <div class="pm__meta">
                            <div class="pm__meta-row">
                                <span class="pm__meta-label"><i class="fas fa-tag mr-1"></i>Valor</span>
                                <strong class="pm__meta-value">R$ {{ number_format((float) $plano->valor_periodo, 2, ',', '.') }}</strong>
                            </div>
                            <div class="pm__meta-row">
                                <span class="pm__meta-label"><i class="fas fa-road mr-1"></i>Km</span>
                                <strong class="pm__meta-value">{{ $plano->km_resumo }}</strong>
                            </div>
                            <div class="pm__meta-row">
                                <span class="pm__meta-label"><i class="fas fa-hand-holding-usd mr-1"></i>Calção</span>
                                <strong class="pm__meta-value">{{ $plano->calcao !== null ? 'R$ ' . number_format((float) $plano->calcao, 2, ',', '.') : '-' }}</strong>
                            </div>
                            <div class="pm__meta-row pm__meta-row--full">
                                <span class="pm__meta-label"><i class="fas fa-puzzle-piece mr-1"></i>Opcionais</span>
                                <strong class="pm__meta-value">{{ $plano->opcionais->count() }}</strong>
                            </div>
                        </div>

                        <div class="pm__foot">
                            <a href="{{ route('plano_motorista_app.edit', $plano->id) }}" class="btn btn-xs btn-warning" onclick="event.stopPropagation();">
                                <i class="fa fa-edit mr-1"></i>Editar
                            </a>
                            <span onclick="event.stopPropagation();">
                                <x-delete-button :action="route('plano_motorista_app.destroy', $plano->id)" />
                            </span>
                        </div>
                    </article>
                @empty
                    <div class="text-center text-muted py-4">Nenhum plano de motorista de app cadastrado.</div>
                @endforelse
            </div>

            <div class="plano-motorista-app-table table-responsive">
                <table class="table table-striped table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Período</th>
                            <th>Valor</th>
                            <th>Km</th>
                            <th>Calção</th>
                            <th>Opcionais</th>
                            <th>Status</th>
                            <th style="width: 170px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($planos as $plano)
                            <tr onclick="window.location='{{ route('plano_motorista_app.edit', $plano->id) }}';" style="cursor:pointer;">
                                <td>
                                    <div class="font-weight-bold">{{ $plano->nome }}</div>
                                    <small class="text-muted">ID: {{ $plano->id }}</small>
                                </td>
                                <td>{{ $plano->periodo_label }}</td>
                                <td>R$ {{ number_format((float) $plano->valor_periodo, 2, ',', '.') }}</td>
                                <td>{{ $plano->km_resumo }}</td>
                                <td>{{ $plano->calcao !== null ? 'R$ ' . number_format((float) $plano->calcao, 2, ',', '.') : '-' }}</td>
                                <td>{{ $plano->opcionais->count() }}</td>
                                <td>
                                    <span class="badge badge-{{ $plano->ativo === 'S' ? 'success' : 'secondary' }}">
                                        {{ $plano->ativo === 'S' ? 'ATIVO' : 'INATIVO' }}
                                    </span>
                                </td>
                                <td onclick="event.stopPropagation();" style="width:170px;">
                                    <a href="{{ route('plano_motorista_app.edit', $plano->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fa fa-edit"></i> Editar
                                    </a>
                                    <x-delete-button :action="route('plano_motorista_app.destroy', $plano->id)" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Nenhum plano de motorista de app cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .plano-motorista-app-filter-sticky {
            position: sticky;
            top: 10px;
            z-index: 1030;
        }

        .plano-motorista-app-view-toggle {
            display: flex;
            gap: 5px;
            margin-left: 12px;
        }

        .plano-motorista-app-view-toggle .btn.active {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }

        .card-plano-motorista-app {
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header-plano-motorista-app {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 1rem 1.25rem;
        }

        .card-header-plano-motorista-app h4 {
            margin: 0;
            color: #0f172a;
            font-weight: 800;
        }

        .plano-motorista-app-index-body[data-view="table"] .plano-motorista-app-cards {
            display: none;
        }

        .plano-motorista-app-index-body[data-view="cards"] .plano-motorista-app-table {
            display: none;
        }

        .plano-motorista-app-index-body[data-view="table"] .plano-motorista-app-table {
            max-height: calc(100vh - 360px);
            overflow: auto;
            border-radius: 6px;
        }

        .plano-motorista-app-index-body[data-view="table"] .plano-motorista-app-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
        }

        .plano-motorista-app-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 16px;
            padding: 10px 8px 16px;
        }

        .pm {
            position: relative;
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1px solid #e2e9f4;
            border-radius: 18px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(11, 18, 32, .06);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .pm:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(11, 18, 32, .13);
        }

        .pm__accent {
            height: 3px;
            background: #e2e9f4;
        }

        .pm-status--green .pm__accent {
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .pm-status--red .pm__accent {
            background: linear-gradient(90deg, #ef4444, #f87171);
        }

        .pm__head {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 14px 16px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .pm__avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #fff;
            font-size: .82rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            letter-spacing: .04em;
        }

        .pm__head-info { flex: 1; min-width: 0; }

        .pm__id {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .pm__title {
            font-size: .92rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pm__sub {
            font-size: .74rem;
            color: #94a3b8;
        }

        .pm__badges {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .pm__badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .pm__badge--green { background: rgba(16,185,129,.15); color: #047857; }
        .pm__badge--red { background: rgba(239,68,68,.13); color: #b91c1c; }

        .pm__meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 6px 16px 10px;
            flex: 1;
        }

        .pm__meta-row {
            display: flex;
            align-items: baseline;
            gap: 6px;
            font-size: .8rem;
        }

        .pm__meta-row--full { flex-wrap: wrap; }

        .pm__meta-label {
            color: #64748b;
            min-width: 90px;
            flex-shrink: 0;
        }

        .pm__meta-value {
            color: #1e293b;
            font-size: .8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }

        .pm__foot {
            display: flex;
            gap: 6px;
            align-items: center;
            padding: 10px 16px;
            border-top: 1px solid #f1f5f9;
            background: #fafbfc;
        }

        @media (max-width: 575px) {
            .plano-motorista-app-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script>
        (function () {
            const body = document.querySelector('.plano-motorista-app-index-body');
            const buttons = document.querySelectorAll('.plano-motorista-app-view-toggle [data-view]');

            function setView(view) {
                body?.setAttribute('data-view', view);
                localStorage.setItem('planoMotoristaAppIndexView', view);
                buttons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.view === view);
                });
            }

            const saved = localStorage.getItem('planoMotoristaAppIndexView') || 'cards';
            setView(saved);

            buttons.forEach(btn => {
                btn.addEventListener('click', () => setView(btn.dataset.view));
            });
        })();
    </script>
@stop
