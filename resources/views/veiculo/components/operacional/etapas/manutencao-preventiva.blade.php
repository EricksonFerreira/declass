<div class="vs-panel" data-step-panel="manutencao">
    @php
        $planoGrupo = $planoManutencao ?? null;
        $itensPlano = $planoGrupo?->itens ?? collect();
    @endphp

    <div class="vs-panel-head">
        <div>
            <span class="vs-panel-head__eyebrow">Etapa 07</span>
            <h4 class="vs-panel-head__title">Manutenção preventiva</h4>
            <p class="vs-panel-head__sub">Acompanhe execuções e pendências do plano de manutenção, sem misturar isso com ordem de serviço.</p>
        </div>
        <div class="vs-kpis">
            <div class="vs-kpi">
                <span>Execuções</span>
                <strong>{{ $veiculo->manutencoesPreventivas->count() }}</strong>
            </div>
            <div class="vs-kpi">
                <span>Pendências</span>
                <strong>{{ $veiculo->pendenciasManutencaoPreventiva->whereIn('status', ['pendente','atrasado'])->count() }}</strong>
            </div>
            <div class="vs-kpi">
                <span>Atrasadas</span>
                <strong>{{ $veiculo->pendenciasManutencaoPreventiva->where('status', 'atrasado')->count() }}</strong>
            </div>
        </div>
    </div>

    <div class="vt-panel-actions">
        <a href="{{ route('manutencao.planos.index') }}" target="_blank"  class="btn-xp btn-xp--sm">
            <i class="fas fa-clipboard-list mr-1"></i>Ver planos de manutenção
        </a>
    </div>

    @if(! $planoGrupo)
        <div class="empty-state"><i class="fas fa-tools"></i><p>Nenhum plano de manutenção cadastrado para o grupo deste veículo.</p></div>
    @elseif($itensPlano->isEmpty())
        <div class="empty-state"><i class="fas fa-tools"></i><p>Este plano não possui itens cadastrados.</p></div>
    @else
        <div class="table-responsive">
            <table class="table table-hover xp-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Item</th>
                        <th>Próxima data</th>
                        <th>Próximo KM</th>
                        <th>Status</th>
                        <th>Última execução</th>
                        <th>Histórico</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($itensPlano as $planoItem)
                        @php
                            $ultimaExecucao = $veiculo->manutencoesPreventivas->where('item_id', $planoItem->item_id)->sortByDesc('data_execucao')->first();
                            $historicoExecucoes = $veiculo->manutencoesPreventivas->where('item_id', $planoItem->item_id)->sortByDesc('data_execucao');
                            $pendenciaAtual = $veiculo->pendenciasManutencaoPreventiva->where('item_id', $planoItem->item_id)->sortByDesc('id')->first();
                            $statusAtual = 'pendente';
                            $alertaVencido = false;
                            if ($pendenciaAtual) {
                                $statusAtual = $pendenciaAtual->status;
                                $alertaVencido =
                                    ($pendenciaAtual->data_prevista && \Carbon\Carbon::parse($pendenciaAtual->data_prevista)->isPast())
                                    || ($pendenciaAtual->km_previsto !== null && (int) $veiculo->kms >= (int) $pendenciaAtual->km_previsto);
                            } elseif ($ultimaExecucao) {
                                $statusAtual = 'concluida';
                            }
                        @endphp
                        <tr>
                            <td><span class="badge badge-primary">Plano</span></td>
                            <td>{{ $planoItem->item?->nome ?? ('Item #'.$planoItem->item_id) }}</td>
                            <td>
                                {{ $pendenciaAtual?->data_prevista ? \Carbon\Carbon::parse($pendenciaAtual->data_prevista)->format('d/m/Y') : '—' }}
                                @if($alertaVencido)
                                    <div><span class="badge badge-danger">Vencido</span></div>
                                @endif
                            </td>
                            <td>
                                {{ $pendenciaAtual?->km_previsto !== null ? number_format((float) $pendenciaAtual->km_previsto, 0, ',', '.') : '—' }}
                                @if($alertaVencido)
                                    <div><span class="badge badge-danger">KM atingido</span></div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ in_array($statusAtual, ['pendente','atrasado']) ? 'warning' : 'success' }}">
                                    {{ ucfirst($statusAtual) }}
                                </span>
                            </td>
                            <td>
                                @if($ultimaExecucao)
                                    {{ \Carbon\Carbon::parse($ultimaExecucao->data_execucao)->format('d/m/Y') }}
                                    <div class="text-xs text-muted">{{ number_format((float) $ultimaExecucao->km_execucao, 0, ',', '.') }} km</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-xs btn-outline-secondary" data-toggle="collapse" data-target="#historico-{{ $planoItem->id }}" aria-expanded="false">
                                    <i class="fas fa-history mr-1"></i>Ver histórico
                                </button>
                            </td>
                            <td>
                                {{-- @if(in_array($statusAtual, ['pendente','atrasado'])) --}}
                                    <form method="POST" action="{{ route('veiculo.manutencao.concluir', $veiculo->id) }}" class="mb-0 js-form-concluir-manutencao">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $planoItem->item_id }}">
                                        <button type="button" class="btn btn-xs btn-success js-btn-concluir-manutencao"
                                            data-item-nome="{{ e($planoItem->item?->nome ?? ('Item #'.$planoItem->item_id)) }}"
                                            data-proxima-data="{{ $pendenciaAtual?->data_prevista ? \Carbon\Carbon::parse($pendenciaAtual->data_prevista)->format('d/m/Y') : '' }}"
                                            data-proximo-km="{{ $pendenciaAtual?->km_previsto !== null ? number_format((float) $pendenciaAtual->km_previsto, 0, ',', '.') : '' }}"
                                            data-venceu="{{ $alertaVencido ? '1' : '0' }}">
                                            <i class="fas fa-check mr-1"></i>Concluir
                                        </button>
                                    </form>
                                {{-- @else
                                    <span class="text-muted">—</span>
                                @endif --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            @foreach($itensPlano as $planoItem)
                @php
                    $historicoExecucoes = $veiculo->manutencoesPreventivas->where('item_id', $planoItem->item_id)->sortByDesc('data_execucao');
                    $pendenciasItem = $veiculo->pendenciasManutencaoPreventiva->where('item_id', $planoItem->item_id)->sortBy('id')->values();
                    $execucoesItem = $historicoExecucoes->sortBy('id')->values();
                    $linhasHistorico = collect();
                    $totalLinhas = max($pendenciasItem->count(), $execucoesItem->count());

                    for ($i = 0; $i < $totalLinhas; $i++) {
                        $pendencia = $pendenciasItem[$i] ?? null;
                        $execucao = $execucoesItem[$i] ?? null;

                        $linhasHistorico->push([
                            'data_prevista' => $pendencia?->data_prevista,
                            'data_realizada' => $execucao?->data_execucao,
                            'km_previsto' => $pendencia?->km_previsto,
                            'km_realizado' => $execucao?->km_execucao,
                            'status' => $execucao ? 'Concluída' : (($pendencia?->status ?? 'pendente') === 'atrasado' ? 'Atrasada' : 'Pendente'),
                        ]);
                    }
                @endphp
                <div class="collapse mb-3" id="historico-{{ $planoItem->id }}">
                    <div class="card card-body">
                        <h5 class="mb-3">{{ $planoItem->item?->nome ?? ('Item #'.$planoItem->item_id) }}</h5>

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <h6 class="text-sm font-weight-bold">Execuções</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Data prevista</th>
                                                <th>Data realizada</th>
                                                <th>KM previsto</th>
                                                <th>KM realizado</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($linhasHistorico as $linha)
                                                <tr>
                                                    <td>{{ $linha['data_prevista'] ? \Carbon\Carbon::parse($linha['data_prevista'])->format('d/m/Y') : '—' }}</td>
                                                    <td>{{ $linha['data_realizada'] ? \Carbon\Carbon::parse($linha['data_realizada'])->format('d/m/Y') : '—' }}</td>
                                                    <td>{{ $linha['km_previsto'] !== null ? number_format((float) $linha['km_previsto'], 0, ',', '.') . ' km' : '—' }}</td>
                                                    <td>{{ $linha['km_realizado'] !== null ? number_format((float) $linha['km_realizado'], 0, ',', '.') . ' km' : '—' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $linha['status'] === 'Concluída' ? 'success' : ($linha['status'] === 'Atrasada' ? 'warning' : 'secondary') }}">
                                                            {{ $linha['status'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted">Nenhuma execução registrada.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <h6 class="text-sm font-weight-bold">Pendências</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Previsão</th>
                                                <th>KM</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($pendenciasItem as $pendencia)
                                                <tr>
                                                    <td>{{ $pendencia->data_prevista ? \Carbon\Carbon::parse($pendencia->data_prevista)->format('d/m/Y') : '—' }}</td>
                                                    <td>{{ $pendencia->km_previsto !== null ? number_format((float) $pendencia->km_previsto, 0, ',', '.') : '—' }}</td>
                                                    <td><span class="badge badge-{{ in_array($pendencia->status, ['pendente','atrasado']) ? 'warning' : 'success' }}">{{ ucfirst($pendencia->status) }}</span></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted">Nenhuma pendência encontrada.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('js')
<script>
(function () {
    function bindSwal() {
        const forms = document.querySelectorAll('.js-form-concluir-manutencao');
        if (!forms.length || !window.Swal || typeof window.Swal.fire !== 'function') {
            return false;
        }

        forms.forEach(function (form) {
            const btn = form.querySelector('.js-btn-concluir-manutencao');
            if (!btn || btn.dataset.bound === '1') return;
            btn.dataset.bound = '1';

            btn.addEventListener('click', function () {
                const itemNome = btn.dataset.itemNome || 'Manutenção';
                const proximaData = btn.dataset.proximaData || '—';
                const proximoKm = btn.dataset.proximoKm || '—';
                const venceu = btn.dataset.venceu === '1';

                window.Swal.fire({
                    icon: venceu ? 'warning' : 'success',
                    title: venceu ? 'Manutenção vencida' : 'Concluir manutenção?',
                    html: `
                        <div class="text-left">
                            <div><strong>Item:</strong> ${itemNome}</div>
                            <div><strong>Próxima data:</strong> ${proximaData}</div>
                            <div><strong>Próximo KM:</strong> ${proximoKm}</div>
                            ${venceu ? '<div class="text-danger mt-2 font-weight-bold">Este item já passou do prazo ou da quilometragem prevista.</div>' : ''}
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check mr-1"></i>Concluir',
                    cancelButtonText: 'Cancelar',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        return true;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            if (!bindSwal()) {
                setTimeout(bindSwal, 250);
            }
        });
    } else if (!bindSwal()) {
        setTimeout(bindSwal, 250);
    }
})();
</script>
@endpush
