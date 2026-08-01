<div class="rs-card">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-car-crash"></i></div>
        <div>
            <div class="rs-card__title">Sinistros</div>
            <div class="rs-card__sub">Ocorrências ligadas a esta reserva</div>
        </div>
    </div>
    <div class="rs-card__body">
        @php $sinistrosCliente = $reserva->avariaCobrancas ?? collect(); @endphp
        @if($sinistrosCliente->isEmpty())
            <div class="rs-note">Nenhum sinistro vinculado a esta reserva.</div>
        @else
            <div class="rs-list">
                @foreach($sinistrosCliente as $sinistro)
                    @php
                        $statusSinistro = strtolower((string) ($sinistro->status ?? 'aberto'));
                        $labelStatus = match ($statusSinistro) {
                            'aberto', 'em_analise' => 'Em análise',
                            'pago', 'cobrado' => 'Cobrado',
                            'cancelado' => 'Cancelado',
                            default => ucfirst($statusSinistro),
                        };
                    @endphp
                    <div class="rs-list__item" style="border-bottom:1px solid var(--ma-line);padding:10px 0;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="rs-list__label">{{ $sinistro->descricao ?: 'Sinistro' }}</div>
                                <div class="text-muted" style="font-size:.74rem;">
                                    {{ $sinistro->created_at ? \Carbon\Carbon::parse($sinistro->created_at)->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                            <span class="badge badge-{{ $statusSinistro === 'pago' || $statusSinistro === 'cobrado' ? 'success' : ($statusSinistro === 'cancelado' ? 'danger' : 'warning') }}">{{ $labelStatus }}</span>
                        </div>
                        <div class="mt-2 text-dark font-weight-bold">R$ {{ number_format((float) ($sinistro->valor ?? 0), 2, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
