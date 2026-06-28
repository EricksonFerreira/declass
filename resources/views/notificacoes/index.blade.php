@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb ?? []" />
@stop

@section('content')
    <style>
        .notif-page {
            display: grid;
            gap: 20px;
        }

        .notif-hero,
        .notif-card {
            border: 1px solid #dbe4f0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 14px 36px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        .notif-hero {
            padding: 22px 24px;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, .18), transparent 26%),
                radial-gradient(circle at bottom left, rgba(16, 185, 129, .15), transparent 22%),
                linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #2563eb 100%);
            box-shadow: 0 24px 60px rgba(15, 23, 42, .18);
        }

        .notif-hero__eyebrow {
            font-size: .72rem;
            letter-spacing: .18em;
            text-transform: uppercase;
            font-weight: 800;
            opacity: .72;
        }

        .notif-hero__title {
            margin: .35rem 0 .25rem;
            font-size: 1.55rem;
            font-weight: 900;
            letter-spacing: -.02em;
        }

        .notif-hero__text {
            margin: 0;
            max-width: 58rem;
            color: rgba(255,255,255,.86);
            line-height: 1.6;
        }

        .notif-hero__chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .notif-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.14);
            color: #fff;
            font-size: .8rem;
            font-weight: 700;
        }

        .notif-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid #e8eef6;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .notif-card__title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .notif-card__sub {
            margin: 4px 0 0;
            font-size: .82rem;
            color: #64748b;
        }

        .notif-card__body {
            padding: 0;
        }

        .notif-table {
            margin: 0;
        }

        .notif-table thead th {
            background: #f8fbff;
            border-top: 0;
            border-bottom: 1px solid #e8eef6;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: .72rem;
            color: #64748b;
            font-weight: 800;
            vertical-align: middle;
        }

        .notif-table td {
            vertical-align: middle;
            border-top: 1px solid #edf2f7;
            font-size: .86rem;
            padding-top: .9rem !important;
            padding-bottom: .9rem !important;
        }

        .notif-badge {
            padding: .42rem .78rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .notif-empty {
            padding: 48px 16px;
            color: #64748b;
        }

        .notif-empty i {
            font-size: 3rem;
            opacity: .18;
            margin-bottom: 14px;
        }

        @media (max-width: 767px) {
            .notif-card__header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    <div class="notif-page">
        <section class="notif-hero">
            <div class="notif-hero__eyebrow">Central de avisos</div>
            <h1 class="notif-hero__title">Notificações em um painel limpo e direto</h1>
            <p class="notif-hero__text">
                Consulte alertas de multas, vencimentos e eventos do sistema no mesmo padrão visual das demais telas administrativas.
            </p>
            <div class="notif-hero__chips">
                <span class="notif-chip"><i class="fas fa-bell"></i> Avisos recentes</span>
                <span class="notif-chip"><i class="fas fa-check-circle"></i> Lidas e não lidas</span>
                <span class="notif-chip"><i class="fas fa-triangle-exclamation"></i> Eventos críticos</span>
            </div>
        </section>

        <div class="notif-card">
            <div class="notif-card__header">
                <div>
                    <h2 class="notif-card__title">Lista de notificações</h2>
                    <p class="notif-card__sub">Acompanhe os alertas e marque como lidos quando necessário.</p>
                </div>
                @if(($naoLidas ?? 0) > 0)
                    <form method="POST" action="{{ route('notificacoes.marcarTodasComoLidas') }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-primary btn-sm font-weight-bold">
                            <i class="fas fa-check-double mr-1"></i> Marcar todas como lidas
                        </button>
                    </form>
                @endif
            </div>

            <div class="notif-card__body table-responsive">
                <table class="table table-hover mb-0 notif-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Assunto</th>
                            <th>Mensagem</th>
                            <th>Data</th>
                            <th class="text-right">Ações</th>
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
                                <td>
                                    <span class="notif-badge badge badge-{{ $cor }}"><i class="{{ $icone }}"></i></span>
                                    @if(!$lida)
                                        <span class="badge badge-danger ml-1">Nova</span>
                                    @else
                                        <span class="badge badge-secondary ml-1">Lida</span>
                                    @endif
                                </td>
                                <td class="font-weight-bold text-dark">{{ $tituloItem }}</td>
                                <td class="text-muted">{{ $mensagem }}</td>
                                <td>{{ $notificacao->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-right">
                                    @if($url)
                                        <a href="{{ $url }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                            Abrir
                                        </a>
                                    @endif
                                    @if(!$lida)
                                        <form method="POST" action="{{ route('notificacoes.marcarLida', $notificacao->id) }}" class="d-inline ml-1">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success rounded-pill">
                                                Marcar lida
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center notif-empty">
                                    <i class="far fa-folder-open d-block"></i>
                                    <h5 class="text-secondary font-weight-normal mb-0">Nenhuma notificação encontrada.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            {{ $notificacoes->links() }}
        </div>
    </div>
@stop
