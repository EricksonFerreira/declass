@php
    $danosCount = is_countable($danosVeiculo ?? null) ? count($danosVeiculo) : 0;
@endphp

<div class="card card-soft">
    <div class="card-header rev-table-header">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="rev-table-title">Danos do veículo</span>
               
            </div>
            <div class="rev-table-subtitle">
                {{ $danosCount }} dano(s) identificado(s) nesta revisão.
            </div>
        </div>
        {{-- <div class="rev-table-legend">
            <span><i class="fas fa-circle text-warning mr-1"></i>Com cobrança</span>
            <span><i class="fas fa-circle text-success mr-1"></i>Sem cobrança</span>
            <span><i class="fas fa-circle text-info mr-1"></i>Pendente</span>
        </div> --}}
    </div>

    <div class="card-body p-0">
        {{-- <div class="p-3 border-bottom bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary js-filter-danos active" data-filter="all">Todos</button>
                <button type="button" class="btn btn-sm btn-outline-warning js-filter-danos" data-filter="charge">Com cobrança</button>
                <button type="button" class="btn btn-sm btn-outline-success js-filter-danos" data-filter="free">Sem cobrança</button>
                <button type="button" class="btn btn-sm btn-outline-info js-filter-danos" data-filter="pending">Pendentes</button>
            </div>
            <div class="text-muted small">
                <i class="fas fa-info-circle mr-1"></i> A decisão financeira não altera o reparo físico.
            </div>
        </div> --}}

        <div class="table-responsive">
            <table class="table soft-table rev-danos-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Dano</th>
                        <th>Momento</th>
                        <th>Decisão</th>
                        <th>Valor</th>
                        <th>Reparo</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danosVeiculo as $dano)
                        @php
                            $valorReal = (float) ($dano['valor_exibido'] ?? $dano['valor_sugerido'] ?? 0);
                            $estado = !empty($dano['cobranca_pendente']) ? 'cobrar' : (($dano['status'] ?? '') === 'descartada' ? 'free' : (($dano['ja_cobrado'] ?? false) ? 'cobrado' : 'pending'));
                            $descricaoDano = $dano['descricao'] ?? 'Dano';
                            $origem = $dano['origem_label'] ?? ['label' => 'Outra reserva', 'class' => 'mini-chip--ok'];
                            $local = $dano['local'] ?? '-';
                            $statusReparo = $dano['model']->status_reparo ?? 'pendente';
                            $reparoLabel = match ($statusReparo) {
                                'em_os' => 'Em OS',
                                'em_correcao' => 'Em correção',
                                'corrigida' => 'Corrigida',
                                'nao_requer_reparo' => 'Não requer reparo',
                                default => 'Pendente',
                            };
                            $statusDanoLabel = match ($dano['status'] ?? '') {
                                'descartada' => 'Não cobrar',
                                'pendente' => 'Pendente',
                                default => 'Em revisão',
                            };
                            $decisaoLabel = match ($dano['model']->status_revisao ?? 'pendente') {
                                'cobrar_cliente' => 'Cobrar cliente',
                                'nao_cobrar' => 'Não cobrar',
                                'ja_existente' => 'Já existente',
                                'desgaste_normal' => 'Desgaste normal',
                                'possivel_sinistro' => 'Possível sinistro',
                                default => 'Pendente',
                            };
                        @endphp
                        <tr class="js-dano-item" data-state="{{ $estado }}" data-charge="{{ in_array($estado, ['cobrar', 'cobrado'], true) ? '1' : '0' }}" data-valor="{{ $valorReal }}">
                            <td>
                                {{ $dano['model']->id }}
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $local }} - {{ $descricaoDano }}</div>
                                {{-- <div class="mt-1 text-muted small">{{ $dano['observacoes'] ?: 'Sem observações.' }}</div> --}}
                            </td>
                            <td>
                                <div>
                                    <span class="mini-chip {{ $origem['class'] ?? 'mini-chip--info' }}">{{ $origem['label'] ?? 'Outra reserva' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="mini-chip {{ $estado === 'cobrar' ? 'mini-chip--warn' : ($estado === 'free' ? 'mini-chip--ok' : 'mini-chip--info') }}">
                                    {{ $decisaoLabel }}
                                </div>
                                {{-- @if(!empty($dano['cobranca_pendente']))
                                    <div class="small text-muted mt-1">Cobrança pendente</div>
                                @endif
                                @if(!empty($dano['cobranca_id']))
                                    <div class="small text-muted mt-1">Cobrança vinculada</div>
                                @endif --}}
                            </td>
                            <td>
                                @if(($dano['model']->status_revisao ?? '') === 'cobrar_cliente')
                                    @if((float) ($dano['valor_cobranca'] ?? 0) > 0 && (float) ($dano['valor_cobranca'] ?? 0) !== (float) ($dano['valor_sugerido'] ?? 0))
                                        <div class="font-weight-bold h6 mb-1">
                                            Aprovado: R$ {{ number_format((float) ($dano['valor_cobranca'] ?? 0), 2, ',', '.') }}
                                        </div>
                                        <div class="small text-muted">
                                            Estimado: R$ {{ number_format((float) ($dano['valor_sugerido'] ?? 0), 2, ',', '.') }}
                                        </div>
                                    @else
                                        <div class="font-weight-bold h6 mb-0">R$ {{ number_format($valorReal, 2, ',', '.') }}</div>
                                    @endif
                                @else
                                    <div class="font-weight-bold h6 mb-0">-</div>
                                @endif
                            </td>
                            <td>
                                <span class="mini-chip {{ $statusReparo === 'corrigida' ? 'mini-chip--ok' : ($statusReparo === 'nao_requer_reparo' ? 'mini-chip--info' : 'mini-chip--warn') }}">
                                    {{ $reparoLabel }}
                                </span>
                                {{-- <div class="small text-muted mt-1">Status do dano: {{ $statusDanoLabel }}</div> --}}
                            </td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary js-open-detalhes-dano"
                                        data-dano-id="{{ $dano['id'] }}"
                                        data-dano-nome="{{ e($descricaoDano) }}"
                                        data-dano-local="{{ e($local) }}"
                                        data-dano-observacao="{{ e($dano['observacoes'] ?? '') }}"
                                        data-dano-valor="{{ number_format($valorReal, 2, '.', '') }}"
                                        data-dano-valor-aprovado="{{ number_format((float) ($dano['valor_cobranca'] ?? $valorReal), 2, '.', '') }}"
                                        data-dano-imagens='@json($dano['imagens'] ?? [])'
                                        data-dano-usar-calcao="{{ !empty($dano['usar_calcao']) ? '1' : '0' }}"
                                        data-dano-mode="{{ $estado === 'free' ? 'free' : 'charge' }}"
                                    >
                                        Ver detalhes
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="sr-only">Abrir menu</span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <button type="button" class="dropdown-item js-open-cobrar-dano"
                                            data-toggle="modal"
                                            data-target="#modalCobrarDano"
                                            data-dano-id="{{ $dano['id'] }}"
                                            data-dano-nome="{{ e($descricaoDano) }}"
                                            data-dano-local="{{ e($local) }}"
                                            data-dano-observacao="{{ e($dano['observacoes'] ?? '') }}"
                                            data-dano-valor="{{ number_format($valorReal, 2, '.', '') }}"
                                            data-dano-valor-aprovado="{{ number_format((float) ($dano['valor_cobranca'] ?? $valorReal), 2, '.', '') }}"
                                            data-dano-imagens='@json($dano['imagens'] ?? [])'
                                            data-dano-usar-calcao="{{ !empty($dano['usar_calcao']) ? '1' : '0' }}"
                                            data-dano-mode="charge"
                                        >Alterar decisão</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum dano encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
