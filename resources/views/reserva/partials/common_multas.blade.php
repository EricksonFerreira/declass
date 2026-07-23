<div class="rs-card mb-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-exclamation-triangle rs-card__icon text-danger"></i>
            <h2>Multas</h2>
            <span class="rs-card__badge">{{ $totalMultas }}</span>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            @can('multa.create')
            @if($pagamentoEfetuado)
                <a href="{{ route('multa.create', ['reserva_id' => $reserva->id]) }}" class="rs-btn rs-btn--outline">
                    <i class="fas fa-plus"></i> Nova multa
                </a>
            @endif
            @endcan
            @if($totalMultas > 0)
            <a href="{{ route('multa.index', ['reserva_id' => $reserva->id]) }}" class="rs-btn rs-btn--outline">
                <i class="fas fa-list"></i> Ver todas
            </a>
            @endif
        </div>
    </div>
    <div class="rs-card__body">
        @if($reserva->multas->isEmpty())
            <div class="rs-empty">
                <i class="fas fa-check-circle" style="font-size:1.5rem;color:#10b981"></i>
                <span>Nenhuma multa registrada para esta reserva.</span>
            </div>
        @else
            <div class="rs-list">
                @foreach($reserva->multas as $multa)
                    @php
                        $multaData = $multa->data_infracao ? \Carbon\Carbon::parse($multa->data_infracao)->format('d/m/Y') : '-';
                        $multaValor = (float) ($multa->valor_total ?? $multa->valor_final ?? $multa->valor ?? 0);
                    @endphp
                    <div class="rs-list__item">
                        <div class="rs-list__label">
                            Auto {{ $multa->auto_infracao ?? ('#' . $multa->id) }} &middot; {{ $multaData }}
                        </div>
                        <div class="rs-list__value">{{ $multa->descricao ?? $multa->infracao ?? 'Infração de trânsito' }}</div>
                        <div class="mt-2 text-danger font-weight-bold">R$ {{ number_format($multaValor, 2, ',', '.') }}</div>
                        <div class="mt-1">
                            <a href="{{ route('multa.show', $multa->id) }}" class="btn btn-xs btn-outline-primary">
                                <i class="fas fa-eye"></i> Detalhes
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
