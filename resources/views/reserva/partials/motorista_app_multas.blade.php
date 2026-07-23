
<div class="ma-card">
    <div class="ma-card__head">
        <div class="ma-card__icon"><i class="fas fa-circle-exclamation"></i></div>
        <div>
            <div class="ma-card__title">Multas</div>
            <div class="ma-card__sub">Infrações vinculadas a esta reserva</div>
        </div>
    </div>
    <div class="ma-card__body">
        @if($multas->isEmpty())
            <div class="ma-history-empty">Nenhuma multa registrada para esta reserva.</div>
        @else
            <div class="ma-list">
                @foreach($multas as $multa)
                    @php
                        $multaData = $multa->data_infracao ? \Carbon\Carbon::parse($multa->data_infracao)->format('d/m/Y') : '-';
                        $multaValor = (float) ($multa->valor_total ?? $multa->valor ?? 0);
                    @endphp
                    <div class="ma-list__item">
                        <div class="ma-list__label">Auto {{ $multa->auto_infracao ?? ('#' . $multa->id) }} &middot; {{ $multaData }}</div>
                        <div class="ma-list__value">{{ $multa->descricao ?? 'Infração de trânsito' }}</div>
                        <div class="mt-2 text-dark font-weight-bold">R$ {{ number_format($multaValor, 2, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
