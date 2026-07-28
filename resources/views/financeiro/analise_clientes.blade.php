@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    {{-- <x-page-title :title="$titulo" /> --}}
@stop

@section('content')
@php
    $fmt = function ($value) use ($moedaEmpresa) {
        return ($moedaEmpresa === 'BRL' ? 'R$' : $moedaEmpresa) . ' ' . number_format((float) $value, 2, ',', '.');
    };

    $digits = function ($value) {
        return preg_replace('/\D+/', '', (string) $value);
    };

    $wa = function ($value, $message) use ($digits) {
        $phone = $digits($value);
        if ($phone === '') {
            return null;
        }
        if (! str_starts_with($phone, '55')) {
            $phone = '55'.$phone;
        }
        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    };
@endphp

<style>
    .ac-page { display: grid; gap: 20px; }
    .ac-hero {
        border-radius: 24px;
        padding: 24px;
        color: #fff;
        background:
            radial-gradient(circle at top right, rgba(56, 189, 248, .18), transparent 25%),
            radial-gradient(circle at bottom left, rgba(16, 185, 129, .15), transparent 22%),
            linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #2563eb 100%);
        box-shadow: 0 24px 60px rgba(15, 23, 42, .18);
    }
    .ac-hero__eyebrow { font-size: .72rem; letter-spacing: .18em; text-transform: uppercase; font-weight: 800; opacity: .72; }
    .ac-hero__title { margin: .35rem 0 .25rem; font-size: 1.6rem; font-weight: 900; letter-spacing: -.02em; }
    .ac-hero__text { margin: 0; max-width: 62rem; color: rgba(255,255,255,.86); line-height: 1.6; }
    .ac-hero__chips { display:flex; flex-wrap:wrap; gap:10px; margin-top:16px; }
    .ac-chip {
        display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px;
        background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.14); font-size:.8rem; font-weight:700;
    }
    .ac-panel, .ac-card {
        border: 1px solid #dbe4f0; border-radius: 18px; background: #fff;
        box-shadow: 0 14px 36px rgba(15, 23, 42, .06); overflow:hidden;
    }
    .ac-panel__header, .ac-card__header {
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        padding: 16px 18px; border-bottom:1px solid #e8eef6; background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
    }
    .ac-panel__body { padding: 18px; }
    .ac-title { margin:0; font-size:1rem; font-weight:800; color:#0f172a; }
    .ac-sub { margin:4px 0 0; font-size:.82rem; color:#64748b; }
    .ac-label { display:inline-block; margin-bottom:.4rem; font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:#64748b; }
    .ac-input { height:40px; border-radius:12px; border-color:#d7e0ea; box-shadow:none; }
    .ac-input:focus { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.12); }
    .ac-btn { min-height:40px; border-radius:12px; font-weight:800; }
    .ac-kpi {
        border:1px solid #dbe4f0; border-radius:18px; background:#fff; box-shadow:0 14px 36px rgba(15,23,42,.06); overflow:hidden; height:100%;
    }
    .ac-kpi__accent { height:4px; }
    .ac-kpi__body { padding:18px; position:relative; min-height:128px; }
    .ac-kpi__label { margin:0 0 10px; color:#64748b; font-size:.78rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .ac-kpi__value { margin:0; font-size:1.65rem; font-weight:900; letter-spacing:-.03em; }
    .ac-kpi__meta { display:block; margin-top:8px; color:#64748b; font-size:.82rem; }
    .ac-kpi__icon { position:absolute; right:18px; top:18px; font-size:2rem; opacity:.12; }
    .ac-kpi--blue .ac-kpi__accent { background:linear-gradient(90deg, #2563eb, #60a5fa); }
    .ac-kpi--green .ac-kpi__accent { background:linear-gradient(90deg, #059669, #34d399); }
    .ac-kpi--amber .ac-kpi__accent { background:linear-gradient(90deg, #d97706, #fbbf24); }
    .ac-kpi--red .ac-kpi__accent { background:linear-gradient(90deg, #dc2626, #fb7185); }
    .ac-table thead th {
        background:#f8f9fa; border-top:none; border-bottom:2px solid #edf2f7; text-transform:uppercase;
        font-size:.72rem; letter-spacing:.08em; color:#6c757d;
    }
    .ac-table td { vertical-align:middle; font-size:.88rem; border-top:1px solid #edf2f7; }
    .ac-pill {
        display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; font-size:.74rem; font-weight:800;
    }
    .ac-pill--warn { background:#fff7ed; color:#9a3412; }
    .ac-pill--ok { background:#ecfdf5; color:#065f46; }
    .ac-pill--muted { background:#f1f5f9; color:#475569; }
    .ac-split { display:grid; grid-template-columns: 1.2fr .8fr; gap: 20px; }
    @media (max-width: 991px) { .ac-split { grid-template-columns: 1fr; } }
</style>

<div class="ac-page">
    <section class="ac-hero">
        <div class="ac-hero__eyebrow">Financeiro</div>
        <h1 class="ac-hero__title">Análise por Cliente</h1>
        <p class="ac-hero__text">
            Acompanhe o que ainda precisa ser cobrado, destaque reservas próximas do horário e marque cobranças feitas pessoalmente
            com justificativa para limpar a sinalização.
        </p>
        <div class="ac-hero__chips">
            <span class="ac-chip"><i class="fas fa-user-check"></i> Cobrança pessoal</span>
            <span class="ac-chip"><i class="fas fa-calendar-alt"></i> Reserva próxima</span>
            <span class="ac-chip"><i class="fas fa-file-invoice-dollar"></i> Multas e sinistros</span>
        </div>
    </section>

    <section class="ac-panel">
        <div class="ac-panel__body">
            <form method="GET" action="{{ route('financeiro.analise-clientes') }}" class="row align-items-end">
                <div class="col-lg-4 col-md-6 mb-3">
                    <label class="ac-label" for="busca">Cliente / telefone</label>
                    <input type="text" name="busca" id="busca" class="form-control ac-input" value="{{ request('busca') }}" placeholder="Nome, celular ou telefone">
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="ac-label" for="cliente_id">Cliente</label>
                    <select name="cliente_id" id="cliente_id" class="form-control ac-input">
                        <option value="">Todos</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ (int) request('cliente_id') === (int) $cliente->id ? 'selected' : '' }}>{{ $cliente->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-3">
                    <label class="ac-label" for="status_cobranca">Cobrança</label>
                    <select name="status_cobranca" id="status_cobranca" class="form-control ac-input">
                        <option value="">Todos</option>
                        <option value="em_aberto" {{ request('status_cobranca') === 'em_aberto' ? 'selected' : '' }}>Em aberto</option>
                        <option value="pago" {{ request('status_cobranca') === 'pago' ? 'selected' : '' }}>Pago</option>
                        <option value="cancelado" {{ request('status_cobranca') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-3 mb-3">
                    <label class="ac-label" for="per_page">Por pág.</label>
                    <select name="per_page" id="per_page" class="form-control ac-input">
                        @foreach([10, 15, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 15) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 col-md-3 mb-3">
                    <label class="ac-label" for="data_inicio">Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control ac-input" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-lg-1 col-md-3 mb-3">
                    <label class="ac-label" for="data_fim">Fim</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control ac-input" value="{{ request('data_fim') }}">
                </div>
                <div class="col-lg-2 col-md-6 mb-3">
                    <div class="d-flex ac-actions">
                        <button type="submit" class="btn btn-primary ac-btn flex-grow-1 mr-2"><i class="fas fa-filter mr-1"></i> Filtrar</button>
                        <a href="{{ route('financeiro.analise-clientes') }}" class="btn btn-light border ac-btn" title="Limpar"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <div class="row">
        <div class="col-md-3 col-6 mb-3">
            <div class="ac-kpi ac-kpi--blue">
                <div class="ac-kpi__accent"></div>
                <div class="ac-kpi__body">
                    <div class="ac-kpi__icon"><i class="fas fa-users"></i></div>
                    <p class="ac-kpi__label">Clientes</p>
                    <h3 class="ac-kpi__value">{{ number_format((int) ($totais->clientes ?? 0), 0, ',', '.') }}</h3>
                    <small class="ac-kpi__meta">Base consolidada do relatório</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="ac-kpi ac-kpi--green">
                <div class="ac-kpi__accent"></div>
                <div class="ac-kpi__body">
                    <div class="ac-kpi__icon"><i class="fas fa-check-circle"></i></div>
                    <p class="ac-kpi__label">Pago</p>
                    <h3 class="ac-kpi__value text-success">{{ $fmt($totais->pago ?? 0) }}</h3>
                    <small class="ac-kpi__meta">Total já liquidado</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="ac-kpi ac-kpi--amber">
                <div class="ac-kpi__accent"></div>
                <div class="ac-kpi__body">
                    <div class="ac-kpi__icon"><i class="fas fa-clock"></i></div>
                    <p class="ac-kpi__label">Em aberto</p>
                    <h3 class="ac-kpi__value text-warning">{{ $fmt($totais->aberto ?? 0) }}</h3>
                    <small class="ac-kpi__meta">Pendências somadas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="ac-kpi ac-kpi--red">
                <div class="ac-kpi__accent"></div>
                <div class="ac-kpi__body">
                    <div class="ac-kpi__icon"><i class="fas fa-bell"></i></div>
                    <p class="ac-kpi__label">Alertas</p>
                    <h3 class="ac-kpi__value text-danger">{{ number_format((int) collect($analise->items())->filter(fn ($r) => (int) ($r->reservas_proximas_count ?? 0) > 0)->count(), 0, ',', '.') }}</h3>
                    <small class="ac-kpi__meta">Reservas próximas do horário</small>
                </div>
            </div>
        </div>
    </div>

    <div class="ac-split">
        <section class="ac-card">
            <div class="ac-card__header">
                <div>
                    <h3 class="ac-title">Clientes com maior pendência</h3>
                    <p class="ac-sub">Combina reservas, multas e sinistros em aberto.</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table ac-table mb-0">
                    <thead>
                        <tr>
                            <th class="px-4">Cliente</th>
                            <th>Reserva</th>
                            <th>Multa</th>
                            <th>Sinistro</th>
                            <th>Total</th>
                            <th>Sinalização</th>
                            <th class="text-right px-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analise as $cliente)
                            @php
                                $temReservaProxima = (int) ($cliente->reservas_proximas_count ?? 0) > 0;
                                $waLink = $wa($cliente->celular ?: $cliente->telefone, 'Olá '.$cliente->cliente_nome.', estamos entrando em contato sobre valores em aberto.');
                            @endphp
                            <tr>
                                <td class="px-4">
                                    <div class="font-weight-bold">{{ $cliente->cliente_nome }}</div>
                                    <div class="text-muted text-xs">{{ $cliente->celular ?: $cliente->telefone ?: 'Sem contato' }}</div>
                                </td>
                                <td class="font-weight-bold text-warning">{{ $fmt($cliente->total_reserva_pendente ?? 0) }}</td>
                                <td class="font-weight-bold text-warning">{{ $fmt($cliente->total_multa_pendente ?? 0) }}</td>
                                <td class="font-weight-bold text-warning">{{ $fmt($cliente->total_sinistro_pendente ?? 0) }}</td>
                                <td class="font-weight-bold text-dark">{{ $fmt($cliente->total_pendente ?? 0) }}</td>
                                <td>
                                    @if($temReservaProxima)
                                        <span class="ac-pill ac-pill--warn"><i class="fas fa-exclamation-triangle"></i> Reserva próxima</span>
                                    @else
                                        <span class="ac-pill ac-pill--muted">Sem alerta</span>
                                    @endif
                                </td>
                                <td class="text-right px-4">
                                    @if($temReservaProxima && !empty($cliente->proxima_reserva_id))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-warning mr-1 js-marcar-cobranca-pessoal"
                                            data-reserva-id="{{ $cliente->proxima_reserva_id }}"
                                            data-cliente-nome="{{ $cliente->cliente_nome }}"
                                            data-reserva-inicio="{{ $cliente->proxima_reserva_inicio ? \Carbon\Carbon::parse($cliente->proxima_reserva_inicio)->format('d/m/Y H:i') : '-' }}"
                                        >
                                            <i class="fas fa-user-check mr-1"></i>Pessoal
                                        </button>
                                    @endif
                                    @if($waLink)
                                        <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                                            <i class="fab fa-whatsapp mr-1"></i> Cobrar
                                        </a>
                                    @else
                                        <span class="text-muted">Sem número</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">Nenhum cliente encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($analise->hasPages())
                <div class="px-3 py-3 d-flex justify-content-end">{{ $analise->links() }}</div>
            @endif
        </section>

        <aside class="d-grid" style="gap: 20px;">
            <section class="ac-card">
                <div class="ac-card__header">
                    <div>
                        <h3 class="ac-title">Resumo rápido</h3>
                        <p class="ac-sub">O que entra na cobrança atual.</p>
                    </div>
                </div>
                <div class="p-3">
                    <div class="mb-3"><div class="text-muted text-xs">Reservas em aberto</div><div class="h4 mb-0">{{ number_format(collect($analise->items())->sum('reservas_pendentes_count'), 0, ',', '.') }}</div></div>
                    <div class="mb-3"><div class="text-muted text-xs">Multas pendentes</div><div class="h4 mb-0">{{ number_format(collect($analise->items())->sum('multas_pendentes_count'), 0, ',', '.') }}</div></div>
                    <div><div class="text-muted text-xs">Sinistros pendentes</div><div class="h4 mb-0">{{ number_format(collect($analise->items())->sum('sinistros_pendentes_count'), 0, ',', '.') }}</div></div>
                </div>
            </section>
            <section class="ac-card">
                <div class="ac-card__header">
                    <div>
                        <h3 class="ac-title">Ação manual</h3>
                        <p class="ac-sub">Use quando a cobrança for feita no balcão.</p>
                    </div>
                </div>
                <div class="p-3">
                    <div class="text-muted small">Clique em <strong>Pessoal</strong> para registrar a justificativa e remover o alerta da reserva.</div>
                </div>
            </section>
        </aside>
    </div>
</div>

<div class="modal fade" id="modalCobrancaPessoal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" id="formCobrancaPessoal">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Marcar cobrança pessoal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>Reserva:</strong> <span id="cobrancaReservaInfo">-</span></p>
                    <div class="form-group">
                        <label for="justificativa_cobranca_pessoal">Justificativa</label>
                        <textarea class="form-control" name="justificativa" id="justificativa_cobranca_pessoal" rows="4" required minlength="5" maxlength="1000" placeholder="Ex.: Cobrado presencialmente no balcão, pago em dinheiro."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function (event) {
        const btn = event.target.closest('.js-marcar-cobranca-pessoal');
        if (!btn) return;
        const reservaId = btn.getAttribute('data-reserva-id');
        const clienteNome = btn.getAttribute('data-cliente-nome') || '';
        const reservaInicio = btn.getAttribute('data-reserva-inicio') || '-';
        document.getElementById('cobrancaReservaInfo').textContent = `${clienteNome} | Reserva prevista: ${reservaInicio}`;
        document.getElementById('formCobrancaPessoal').action = `{{ url('/admin/financeiro/analise-clientes/reservas') }}/${reservaId}/cobranca-pessoal`;
        document.getElementById('justificativa_cobranca_pessoal').value = '';
        $('#modalCobrancaPessoal').modal('show');
    });
</script>
@stop
