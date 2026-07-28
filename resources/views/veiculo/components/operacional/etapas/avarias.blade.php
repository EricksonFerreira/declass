{{-- Painel: Avarias --}}
<div class="vs-panel" data-step-panel="avarias">
    <div class="vs-panel-head">
        <div>
            <span class="vs-panel-head__eyebrow">Etapa 02</span>
            <h4 class="vs-panel-head__title">Avarias</h4>
            <p class="vs-panel-head__sub">Acompanhe as avarias registradas, separando as que ainda estão pendentes das já corrigidas.</p>
        </div>
        <div class="vs-kpis">
            <div class="vs-kpi">
                <span>Pendentes</span>
                <strong>{{ $veiculo->veiculoDanos->whereNull('correcao_data')->count() }}</strong>
            </div>
            <div class="vs-kpi">
                <span>Corrigidas</span>
                <strong>{{ $veiculo->veiculoDanos->whereNotNull('correcao_data')->count() }}</strong>
            </div>
            <div class="vs-kpi">
                <span>Total</span>
                <strong>{{ $veiculo->veiculoDanos->count() }}</strong>
            </div>
        </div>
    </div>

    <div class="vt-panel-actions">
        <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'pendentes']) }}" class="btn-xp btn-xp--sm">
            <i class="fas fa-shield-alt mr-1"></i>Ver monitoramento
        </a>
        <a href="{{ route('sinistro.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--outline">
            <i class="fas fa-plus mr-1"></i>Registrar sinistro
        </a>
    </div>

    @if($veiculo->veiculoDanos->isEmpty())
        <div class="empty-state"><i class="fas fa-car-crash"></i><p>Nenhuma avaria registrada.</p></div>
    @else
        <div class="table-responsive">
            <table class="table table-hover xp-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Dano</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th>Correção</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($veiculo->veiculoDanos as $dano)
                        <tr>
                            <td>{{ $dano->created_at ? $dano->created_at->format('d/m/Y H:i') : '—' }}</td>
                            <td>{{ $dano->dano?->nome ?? ('Dano #'.$dano->dano_id) }}</td>
                            <td>{{ $dano->dano?->categoria?->nome ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $dano->correcao_data ? 'success' : 'warning' }}">
                                    {{ $dano->correcao_data ? 'Corrigida' : 'Pendente' }}
                                </span>
                            </td>
                            <td>{{ $dano->correcao_data ? $dano->correcao_data->format('d/m/Y H:i') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
