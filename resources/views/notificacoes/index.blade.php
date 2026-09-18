@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb ?? []" />
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    /* =====================================================
       PADRÃO VISUAL DO SISTEMA (mesmo da tela de projeto)
    ===================================================== */
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }

    .notif-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 18px rgba(0,0,0,0.05);
    }
    .notif-card > .card-header:first-child { border-radius: 12px 12px 0 0; }

    .notif-icon {
        width: 44px; height: 44px; border-radius: 12px;
        background: #fef3c7; color: #d97706;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }

    .notif-badge {
        padding: .42rem .78rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
    }

    /* Mobile: notificacoes viram cards empilhados com rotulos */
    @media (max-width: 767.98px) {
        .notif-table thead { display: none; }
        .notif-table,
        .notif-table tbody,
        .notif-table tr,
        .notif-table td { display: block; width: 100%; }

        .notif-table tbody tr {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 12px;
            padding: 10px 14px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .notif-table tbody tr.table-warning {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
        }

        .notif-table td {
            border: none !important;
            padding: 5px 0 !important;
            text-align: left !important;
        }
        .notif-table td::before {
            content: attr(data-label);
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #94a3b8;
            margin-bottom: 3px;
        }
        .notif-table td[data-label=""]::before { display: none; }
        .notif-table td.cell-acoes {
            padding-top: 10px !important;
            border-top: 1px dashed #e2e8f0 !important;
        }
    }
</style>
@stop

@section('content')
<!-- Cabeçalho -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card notif-card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="notif-icon mr-3"><i class="fas fa-bell"></i></span>
                        <div>
                            <h2 class="font-weight-bold text-dark mb-1" style="letter-spacing: -0.02em;">Central de Notificações</h2>
                            <p class="text-muted mb-0">
                                Alertas de prazos, eventos e avisos do sistema
                                <span class="mx-2 text-muted">|</span>
                                <span class="badge badge-{{ ($naoLidas ?? 0) > 0 ? 'danger' : 'success' }} px-2 py-1 text-xs">
                                    {{ $naoLidas ?? 0 }} não lida(s)
                                </span>
                            </p>
                        </div>
                    </div>
                    @if(($naoLidas ?? 0) > 0)
                        <form method="POST" action="{{ route('notificacoes.marcarTodasComoLidas') }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-primary font-weight-bold shadow-sm">
                                <i class="fas fa-check-double mr-1"></i> Marcar todas como lidas
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listagem -->
<div class="row">
    <div class="col-12">
        <div class="card notif-card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h3 class="card-title text-bold text-dark mb-0">
                    <i class="fas fa-list text-primary mr-2"></i> Lista de Notificações
                    <small class="text-muted font-weight-normal ml-2">— marque como lidas quando necessário</small>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 notif-table">
                        <thead class="thead-light">
                            <tr class="text-xs text-uppercase">
                                <th class="pl-4">Status</th>
                                <th>Assunto</th>
                                <th>Mensagem</th>
                                <th>Data</th>
                                <th class="text-center" style="width: 190px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($notificacoes as $notificacao)
                                @php
                                    $dados = $notificacao->data ?? [];
                                    $icone = $dados['icone'] ?? 'fas fa-bell';
                                    $cor = $dados['cor'] ?? 'warning';
                                    $tituloItem = $dados['titulo'] ?? 'Notificação';
                                    $mensagem = $dados['mensagem'] ?? '';
                                    $url = $dados['url'] ?? null;
                                    $lida = (bool) $notificacao->read_at;
                                @endphp
                                <tr class="{{ $lida ? '' : 'table-warning' }}">
                                    <td class="pl-4 align-middle" data-label="Status">
                                        <span class="notif-badge badge badge-{{ $cor }}"><i class="{{ $icone }}"></i></span>
                                        @if(!$lida)
                                            <span class="badge badge-danger px-2 py-1 text-xs ml-1">Nova</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1 text-xs ml-1">Lida</span>
                                        @endif
                                    </td>
                                    <td class="align-middle font-weight-bold text-dark text-sm" data-label="Assunto">{{ $tituloItem }}</td>
                                    <td class="align-middle text-muted text-sm" data-label="Mensagem">{{ $mensagem }}</td>
                                    <td class="align-middle text-sm text-secondary" data-label="Data">{{ $notificacao->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="align-middle text-center cell-acoes" data-label="">
                                        @if($url)
                                            <a href="{{ $url }}" class="btn btn-xs btn-info font-weight-bold mr-1">
                                                <i class="fas fa-external-link-alt mr-1"></i> Abrir
                                            </a>
                                        @endif
                                        @if(!$lida)
                                            <form method="POST" action="{{ route('notificacoes.marcarLida', $notificacao->id) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-xs btn-success font-weight-bold">
                                                    <i class="fas fa-check mr-1"></i> Marcar lida
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="far fa-bell-slash fa-3x mb-3 text-muted"></i>
                                        <h5 class="font-weight-bold">Nenhuma notificação encontrada</h5>
                                        <p class="text-sm text-muted mb-0">Quando houver avisos e eventos do sistema, eles aparecem aqui.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($notificacoes->hasPages())
                <div class="card-footer bg-white py-2 d-flex justify-content-end border-top">
                    {{ $notificacoes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@stop
