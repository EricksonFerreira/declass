<div class="vs-panel" data-step-panel="documentacao">
    <div class="vs-panel-head">
        <div>
            <span class="vs-panel-head__eyebrow">Etapa 08</span>
            <h4 class="vs-panel-head__title">Documentação</h4>
            <p class="vs-panel-head__sub">IPVA, licenciamento e seguros organizados por tipo, com meses, vencimentos e status.</p>
        </div>
        <div class="vs-kpis">
            <div class="vs-kpi">
                <span>Tipos cadastrados</span>
                <strong>{{ $veiculo->documentacoesVeiculo->pluck('tipo')->unique()->count() }}</strong>
            </div>
            <div class="vs-kpi">
                <span>Parcelas abertas</span>
                <strong>{{ $veiculo->documentacoesVeiculo->flatMap->parcelas->where('status', 'pendente')->count() }}</strong>
            </div>
            <div class="vs-kpi">
                <span>Documentos cadastrados</span>
                <strong>{{ $veiculo->documentacoesVeiculo->count() }}</strong>
            </div>
        </div>
    </div>

    <div class="vt-panel-actions">
        <a href="{{ route('veiculo.documentacao') }}" target="_blank" class="btn-xp btn-xp--sm">
            <i class="fas fa-file-alt mr-1"></i>Abrir documentação
        </a>
        <a href="{{ route('veiculo.documentacao.create', ['veiculo_id' => $veiculo->id]) }}" target="_blank" class="btn-xp btn-xp--sm btn-xp--outline">
            <i class="fas fa-file-alt mr-1"></i>Adicionar documentação
        </a>
    </div>

    @php
        $tipos = [
            'ipva' => 'IPVA',
            'licenciamento' => 'Licenciamento',
            'seguro' => 'Seguros',
        ];
        $documentacoesPorTipo = $veiculo->documentacoesVeiculo->groupBy('tipo');
    @endphp

    <div class="table-responsive">
        <table class="table table-hover xp-table">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Itens</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tipos as $tipo => $rotulo)
                    @php $documentos = $documentacoesPorTipo->get($tipo, collect()); @endphp
                    <tr>
                        <td class="align-top font-weight-bold">{{ $rotulo }}</td>
                        <td>
                            @forelse($documentos as $doc)
                                <div class="mb-3 border rounded p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <div class="font-weight-bold">{{ $doc->descricao ?: $rotulo }}</div>
                                            <div class="text-muted text-xs">
                                                {{ $doc->parcelas_total }} parcela(s) | Valor da parcela: R$ {{ number_format((float) $doc->valor_parcela, 2, ',', '.') }}
                                            </div>
                                        </div>
                                        <span class="badge badge-{{ $doc->status === 'ativo' ? 'success' : 'secondary' }}">{{ ucfirst($doc->status) }}</span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Mês</th>
                                                    <th>Vencimento</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($doc->parcelas as $parcela)
                                                    <tr>
                                                        <td>{{ optional($parcela->data_vencimento)->format('m/Y') ?? '-' }}</td>
                                                        <td>{{ optional($parcela->data_vencimento)->format('d/m/Y') ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge badge-{{ $parcela->status === 'pago' ? 'success' : ($parcela->status === 'atrasado' ? 'danger' : 'warning') }}">
                                                                {{ ucfirst($parcela->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @empty
                                <span class="text-muted">Nenhum item cadastrado.</span>
                            @endforelse
                        </td>
                        <td class="align-top">
                            @if($documentos->isNotEmpty())
                                <span class="badge badge-success">Ativo</span>
                            @else
                                <span class="badge badge-secondary">Sem itens</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
