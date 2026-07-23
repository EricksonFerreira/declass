<div class="rs-card mb-4">
    <div class="rs-card__header">
        <i class="fas fa-car-crash rs-card__icon"></i>
        <h2>Sinistros</h2>
        <span class="rs-card__badge">{{ $sinistros->count() }}</span>
    </div>
    <div class="rs-card__body">
        @if($sinistros->isEmpty())
            <div class="rs-empty">
                <i class="fas fa-check-circle" style="font-size:1.5rem;color:#10b981"></i>
                <span>Nenhum sinistro vinculado a esta reserva.</span>
            </div>
        @else
            <div class="rs-list">
                @foreach($sinistros as $sinistro)
                    <div class="rs-list__item">
                        <div class="rs-list__label">Sinistro #{{ $sinistro->id }}</div>
                        <div class="rs-list__value">{{ $sinistro->descricao ?? 'Sem descrição' }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
