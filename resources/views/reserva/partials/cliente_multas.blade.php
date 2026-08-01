<div class="rs-card">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-circle-exclamation"></i></div>
        <div>
            <div class="rs-card__title">Multas</div>
            <div class="rs-card__sub">Infrações vinculadas a esta reserva</div>
        </div>
    </div>
    <div class="rs-card__body">
        @php $multasCliente = $reserva->multas ?? collect(); @endphp
        @if($multasCliente->isEmpty())
            <div class="rs-note">Nenhuma multa vinculada a esta reserva.</div>
        @else
            <div class="rs-list">
                @foreach($multasCliente as $multa)
                    @php
                        $statusMulta = strtolower((string) ($multa->status_multa ?? 'pendente'));
                        $labelMulta = match ($statusMulta) {
                            'paga' => 'Paga',
                            'pendente', 'em_analise' => 'Pendente',
                            'cancelada' => 'Cancelada',
                            default => ucfirst($statusMulta),
                        };
                    @endphp
                    <div class="rs-list__item" style="border-bottom:1px solid var(--ma-line);padding:10px 0;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="rs-list__label">Multa #{{ $multa->id }}</div>
                                <div class="rs-list__value">{{ $multa->local_infracao ?: 'Local não informado' }}</div>
                                <div class="text-muted" style="font-size:.74rem;">
                                    {{ $multa->data_hora_infracao ? \Carbon\Carbon::parse($multa->data_hora_infracao)->format('d/m/Y H:i') : '-' }}
                                </div>
                            </div>
                            <span class="badge badge-{{ $statusMulta === 'paga' ? 'success' : ($statusMulta === 'cancelada' ? 'danger' : 'warning') }}">{{ $labelMulta }}</span>
                        </div>
                        <div class="mt-2 text-dark font-weight-bold">R$ {{ number_format((float) ($multa->valor_final ?? $multa->valor_original ?? 0), 2, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
