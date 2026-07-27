<div class="rs-card mb-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-car-crash rs-card__icon"></i>
            <h2>Sinistros</h2>
            <span class="rs-card__badge">{{ $sinistros->count() }}</span>
        </div>
        @can('sinistro.create')
            <a href="{{ route('sinistro.create', ['reserva_id' => $reserva->id, 'veiculo_id' => $reserva->veiculo_id]) }}" class="rs-btn rs-btn--outline">
                <i class="fas fa-plus"></i> Novo Sinistro
            </a>
        @endcan
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
                        <div class="rs-list__value">{{ $sinistro->descricao ?? 'Sem descricao' }}</div>
                        <div class="mt-1">
                            <a href="{{ route('sinistro.show', $sinistro->id) }}" class="btn btn-xs btn-outline-primary">
                                <i class="fas fa-eye"></i> Detalhes
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
