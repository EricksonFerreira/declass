{{-- ── PAINEL: Ordens de Serviço ── --}}
                <div class="vs-panel" data-step-panel="os">
                    <div class="vs-panel-head">
                        <div>
                            <span class="vs-panel-head__eyebrow">Etapa 04</span>
                            <h4 class="vs-panel-head__title">Ordens de serviço</h4>
                            <p class="vs-panel-head__sub">Acompanhe aberturas, andamento e conclusões com filtros mais objetivos.</p>
                        </div>
                        <div class="vs-kpis">
                            <div class="vs-kpi">
                                <span>Abertas</span>
                                <strong>{{ $veiculo->ordensServico->where('status', \App\Models\OrdemServico::STATUS_ABERTA)->count() }}</strong>
                            </div>
                            <div class="vs-kpi">
                                <span>Em andamento</span>
                                <strong>{{ $veiculo->ordensServico->where('status', \App\Models\OrdemServico::STATUS_EM_ANDAMENTO)->count() }}</strong>
                            </div>
                            <div class="vs-kpi">
                                <span>Concluídas</span>
                                <strong>{{ $veiculo->ordensServico->where('status', \App\Models\OrdemServico::STATUS_CONCLUIDA)->count() }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="vs-filter-wrap">
                        <div class="vs-filter-top">
                            <div class="vs-filter-search">
                                <i class="fas fa-search"></i>
                                <input type="text" id="osf_busca" class="vs-search-input" placeholder="Buscar por número, status ou itens..." oninput="filtrarOs()">
                            </div>

                            <div class="vs-filter-actions">
                                <button type="button" class="vs-chip is-active" data-chip-group="os" onclick="chipOs(this, 'todos')">Todas</button>
                                <button type="button" class="vs-chip" data-chip-group="os" onclick="chipOs(this, 'aberta')">Abertas</button>
                                <button type="button" class="vs-chip" data-chip-group="os" onclick="chipOs(this, 'em_andamento')">Em andamento</button>
                                <button type="button" class="vs-chip" data-chip-group="os" onclick="chipOs(this, 'concluida')">Concluídas</button>
                                <button type="button" class="vs-chip" data-chip-group="os" onclick="chipOs(this, '30dias')">Últimos 30 dias</button>
                                <button type="button" class="btn-xp btn-xp--sm btn-xp--outline" onclick="toggleFiltrosAvancados('os')">
                                    <i class="fas fa-sliders-h mr-1"></i>Filtros avançados
                                </button>
                            </div>
                        </div>

                        <div class="vs-advanced-filters" id="vsAdvanced-os">
                            <div class="vs-filter-grid">
                                <div class="vs-filter-field">
                                    <label>Status</label>
                                    <select id="osf_status" class="vt-select" onchange="filtrarOs()">
                                        <option value="">Todos</option>
                                        <option value="aberta">Aberta</option>
                                        <option value="em_andamento">Em Andamento</option>
                                        <option value="concluida">Concluída</option>
                                        <option value="cancelada">Cancelada</option>
                                    </select>
                                </div>

                                <div class="vs-filter-field">
                                    <label>Número / ID</label>
                                    <input type="text" id="osf_numero" class="vt-input" placeholder="Ex: OS-001" oninput="filtrarOs()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Abertura de</label>
                                    <input type="date" id="osf_data_de" class="vt-input" onchange="filtrarOs()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Abertura até</label>
                                    <input type="date" id="osf_data_ate" class="vt-input" onchange="filtrarOs()">
                                </div>
                            </div>

                            <div class="vs-filter-footer">
                                <div class="vs-filter-result" id="osf_result"></div>
                                <div class="vs-filter-footer__actions">
                                    <button type="button" class="vt-btn-clear" onclick="limparFiltroOs()">
                                        <i class="fas fa-times"></i> Limpar
                                    </button>
                                    <button type="button" class="vt-btn-filter" onclick="filtrarOs()">
                                        <i class="fas fa-search"></i> Aplicar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vt-panel-actions">
                        <a href="{{ route('ordem-servico.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Nova OS para este veículo
                        </a>
                    </div>

                    @if($veiculo->ordensServico->isEmpty())
                        <div class="empty-state"><i class="fas fa-tools"></i><p>Nenhuma ordem de serviço registrada.</p></div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover xp-table" id="tabelaOs">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Status</th>
                                    <th>Abertura</th>
                                    <th>Conclusão</th>
                                    <th>Itens</th>
                                    <th>Valor</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($veiculo->ordensServico->sortByDesc('id') as $os)
                                @php
                                    $osStatusVal = $os->status ?? '';
                                    $osDataAb    = $os->created_at ? $os->created_at->format('Y-m-d') : '';
                                    $osNumero    = strtolower($os->numero ?? (string)$os->id);
                                @endphp
                                <tr
                                    data-os-status="{{ $osStatusVal }}"
                                    data-os-numero="{{ $osNumero }}"
                                    data-os-data="{{ $osDataAb }}"
                                >
                                    <td><span class="xp-id">{{ $os->numero ?? '#' . $os->id }}</span></td>
                                    <td>
                                        @if($osStatusVal === \App\Models\OrdemServico::STATUS_ABERTA)
                                            <span class="badge badge-xs bg-amber">Aberta</span>
                                        @elseif($osStatusVal === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO)
                                            <span class="badge badge-xs bg-blue">Em Andamento</span>
                                        @elseif($osStatusVal === \App\Models\OrdemServico::STATUS_CONCLUIDA)
                                            <span class="badge badge-xs bg-green">Concluída</span>
                                        @else
                                            <span class="badge badge-xs bg-gray">Cancelada</span>
                                        @endif
                                    </td>
                                    <td>{{ $os->created_at ? $os->created_at->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $os->data_conclusao ? $os->data_conclusao->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $os->itens ? $os->itens->count() : '—' }}</td>
                                    <td>
                                        @if($os->valor_total > 0)
                                            <strong>{{ number_format($os->valor_total, 2, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('ordem-servico.show', $os) }}" class="btn-xp btn-xp--sm">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
