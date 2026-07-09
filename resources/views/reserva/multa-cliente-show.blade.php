@extends('adminlte::page')

@section('title', 'Multa #' . $multa->id)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $clienteNome = $multa->reserva?->usuario?->nome ?? $multa->cliente?->nome ?? auth()->user()?->nome ?? '-';
    $clienteEmail = $multa->reserva?->usuario?->email ?? $multa->cliente?->email ?? auth()->user()?->email ?? '-';
    $clienteTelefone = $multa->reserva?->usuario?->telefone ?? $multa->cliente?->telefone ?? '-';
    $statusLabel = match ($statusMulta) {
        'paga' => 'Paga',
        'cancelada' => 'Cancelada',
        'contestada' => 'Contestada',
        default => 'Em aberto',
    };
    $statusClass = match ($statusMulta) {
        'paga' => 'success',
        'cancelada' => 'secondary',
        'contestada' => 'warning',
        default => 'danger',
    };
    $vencimento = $multa->data_vencimento ? \Carbon\Carbon::parse($multa->data_vencimento)->format('d/m/Y') : '-';
    $infracao = $multa->data_hora_infracao ? \Carbon\Carbon::parse($multa->data_hora_infracao)->format('d/m/Y H:i') : '-';
@endphp

<style>
    .multa-view {
        --line: #dbe5f1;
        --ink: #0f172a;
        --muted: #64748b;
        --p: #1f4f7a;
        --s: #14324d;
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 16px;
    }
    .mv-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(15,23,42,.06);
        overflow: hidden;
    }
    .mv-hero {
        padding: 22px 24px;
        color: #fff;
        background: linear-gradient(120deg, var(--p), var(--s));
    }
    .mv-hero h1 { margin: 0 0 6px; font-size: 28px; font-weight: 900; }
    .mv-hero p { margin: 0; opacity: .92; }
    .mv-body { padding: 20px 24px 24px; }
    .mv-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .mv-item {
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #f8fbff;
        padding: 12px 14px;
    }
    .mv-item label {
        display: block;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6b7b93;
        margin-bottom: 4px;
    }
    .mv-item strong { color: var(--ink); font-size: .98rem; }
    .mv-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 18px; }
    .mv-btn {
        display: inline-flex; align-items: center; gap: 8px;
        border-radius: 999px; padding: 11px 16px; font-weight: 800; text-decoration: none;
        border: 1px solid var(--line); background: #fff; color: var(--ink);
    }
    .mv-btn--primary { background: var(--p); color: #fff; border-color: var(--p); }
    .mv-btn--disabled { opacity: .55; cursor: not-allowed; pointer-events: none; }
    .mv-summary {
        padding: 18px;
        position: sticky;
        top: 18px;
        height: fit-content;
    }
    .mv-summary h3 { margin: 0 0 12px; font-size: 18px; font-weight: 900; }
    .mv-kpi {
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #f8fbff;
        padding: 12px 14px;
        margin-bottom: 12px;
    }
    .mv-kpi small { display: block; font-size: .72rem; font-weight: 800; color: #7483a0; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 3px; }
    .mv-kpi strong { font-size: 1rem; color: var(--ink); }
    .mv-badge {
        display: inline-flex; align-items: center; gap: 6px;
        border-radius: 999px; padding: 8px 12px; font-weight: 800;
    }
    .mv-badge-success { background: #dcfce7; color: #166534; }
    .mv-badge-danger { background: #fee2e2; color: #991b1b; }
    .mv-badge-warning { background: #fef3c7; color: #92400e; }
    .mv-text { color: var(--muted); font-size: .92rem; line-height: 1.6; }
    @media (max-width: 980px) { .multa-view { grid-template-columns: 1fr; } .mv-summary { position: static; } }
    @media (max-width: 640px) { .mv-grid { grid-template-columns: 1fr; } }
</style>

<div class="multa-view">
    <section class="mv-card">
        <div class="mv-hero">
            <h1>Multa #{{ $multa->id }}</h1>
            <p>Veja os detalhes da multa e acesse o pagamento quando estiver em aberto.</p>
        </div>

        <div class="mv-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
                <span class="mv-badge mv-badge-{{ $statusClass }}">{{ $statusLabel }}</span>
                @if($multa->pago_em)
                    <span class="mv-badge mv-badge-success"><i class="fas fa-check"></i> Paga em {{ \Carbon\Carbon::parse($multa->pago_em)->format('d/m/Y H:i') }}</span>
                @endif
            </div>

            <div class="mv-grid">
                <div class="mv-item">
                    <label>Valor total</label>
                    <strong>R$ {{ number_format((float) $valorMulta, 2, ',', '.') }}</strong>
                </div>
                <div class="mv-item">
                    <label>Vencimento</label>
                    <strong>{{ $vencimento }}</strong>
                </div>
                <div class="mv-item">
                    <label>Data da infração</label>
                    <strong>{{ $infracao }}</strong>
                </div>
                <div class="mv-item">
                    <label>Órgão autuador</label>
                    <strong>{{ $multa->orgao_autuador ?? '-' }}</strong>
                </div>
                <div class="mv-item">
                    <label>Reserva</label>
                    <strong>#{{ $multa->reserva?->id ?? '-' }}</strong>
                </div>
                <div class="mv-item">
                    <label>Veículo</label>
                    <strong>{{ $multa->veiculo?->modelo?->nome ?? $multa->veiculo?->placa ?? '-' }}</strong>
                </div>
                <div class="mv-item">
                    <label>Placa no momento</label>
                    <strong>{{ $multa->placa_momento ?? $multa->veiculo?->placa ?? '-' }}</strong>
                </div>
                <div class="mv-item">
                    <label>Local</label>
                    <strong>{{ $multa->local_infracao ?? '-' }}</strong>
                </div>
            </div>

            <div style="margin-top:16px;" class="mv-item">
                <label>Descrição</label>
                <div class="mv-text">{{ $multa->descricao ?? 'Sem descrição cadastrada.' }}</div>
            </div>

            <div class="mv-actions">
                @if($podePagar)
                    <a href="{{ $urlPagamento }}" class="mv-btn mv-btn--primary">
                        <i class="fas fa-credit-card"></i> Ir para pagamento
                    </a>
                @else
                    <span class="mv-btn mv-btn--disabled" title="Esta multa já foi paga.">
                        <i class="fas fa-check"></i> Multa já paga
                    </span>
                @endif
                <a href="{{ route('reserva.minhas') }}" class="mv-btn">
                    <i class="fas fa-arrow-left"></i> Voltar para minhas reservas
                </a>
            </div>
        </div>
    </section>

    <aside class="mv-card mv-summary">
        <h3>Resumo</h3>

        <div class="mv-kpi">
            <small>Cliente</small>
            <strong>{{ $clienteNome }}</strong>
            <div class="mv-text">{{ $clienteEmail }}</div>
            <div class="mv-text">{{ $clienteTelefone }}</div>
        </div>

        <div class="mv-kpi">
            <small>Reserva vinculada</small>
            <strong>#{{ $multa->reserva?->id ?? '-' }}</strong>
            <div class="mv-text">{{ $multa->reserva?->data_inicio ? \Carbon\Carbon::parse($multa->reserva->data_inicio)->format('d/m/Y H:i') : '-' }}</div>
        </div>

        <div class="mv-kpi">
            <small>Valores</small>
            <strong>R$ {{ number_format((float) $valorMulta, 2, ',', '.') }}</strong>
            <div class="mv-text">Original: R$ {{ number_format((float) ($multa->valor_original ?? 0), 2, ',', '.') }}</div>
            <div class="mv-text">Desconto: R$ {{ number_format((float) ($multa->valor_desconto ?? 0), 2, ',', '.') }}</div>
        </div>

        <div class="mv-kpi">
            <small>Status</small>
            <strong>{{ $statusLabel }}</strong>
            <div class="mv-text">{{ $multa->pago_em ? 'Pagamento concluído.' : 'Aguardando pagamento.' }}</div>
        </div>
    </aside>
</div>
@stop
