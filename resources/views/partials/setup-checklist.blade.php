@if($setupChecklist && $setupChecklist['progresso'] < 100)
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">
                <i class="fas fa-tasks text-primary"></i> Primeiros passos — coloque o ano letivo para funcionar
            </span>
            <span class="badge badge-info">{{ $setupChecklist['feitos'] }}/{{ $setupChecklist['total'] }}</span>
        </div>
        <div class="card-body">
            <div class="progress mb-3" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar"
                     style="width: {{ $setupChecklist['progresso'] }}%"
                     aria-valuenow="{{ $setupChecklist['progresso'] }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="row">
                @foreach($setupChecklist['itens'] as $item)
                    <div class="col-md-4 col-sm-6 mb-2">
                        <a href="{{ route($item['url']) }}" class="d-flex align-items-start" style="color: inherit;">
                            @if($item['feito'])
                                <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                            @else
                                <i class="far fa-circle text-muted mt-1 mr-2"></i>
                            @endif
                            <span>
                                <span class="font-weight-bold {{ $item['feito'] ? 'text-success' : '' }}">{{ $item['label'] }}</span>
                                <br>
                                <small class="text-muted">{{ $item['descricao'] }}</small>
                            </span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
