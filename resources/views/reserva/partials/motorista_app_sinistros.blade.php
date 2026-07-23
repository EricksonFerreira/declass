<div class="ma-card mb-4">
    <div class="ma-card__head">
        <div class="ma-card__icon"><i class="fas fa-car-crash"></i></div>
        <div>
            <div class="ma-card__title">Sinistros</div>
            <div class="ma-card__sub">Ocorrencias ligadas a esta reserva</div>
        </div>
    </div>
    <div class="ma-card__body">
        @if($sinistros->isEmpty())
            <div class="ma-history-empty">Nenhum sinistro vinculado a esta reserva.</div>
        @else
            <div class="ma-list">
                @foreach($sinistros as $sinistro)
                    <div class="ma-list__item">
                        <div class="ma-list__label">Sinistro #{{ $sinistro->id }}</div>
                        <div class="ma-list__value">{{ $sinistro->descricao ?? 'Sem descricao' }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>