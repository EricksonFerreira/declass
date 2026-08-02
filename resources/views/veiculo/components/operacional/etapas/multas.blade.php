{{-- ── PAINEL: Multas ── --}}
                <div class="vs-panel" data-step-panel="multas">
                    <div class="vs-panel-head">
                        <div>
                            <span class="vs-panel-head__eyebrow">Etapa 06</span>
                            <h4 class="vs-panel-head__title">Multas e infrações</h4>
                            <p class="vs-panel-head__sub">Use filtros mais ricos por status, órgão, auto, período, valor e desconto.</p>
                        </div>
                        <div class="vs-kpis">
                            <div class="vs-kpi">
                                <span>Pendentes</span>
                                <strong>{{ $veiculo->multas->whereIn('status_multa', ['pendente','em_analise'])->count() }}</strong>
                            </div>
                            <div class="vs-kpi">
                                <span>Pagas</span>
                                <strong>{{ $veiculo->multas->where('status_multa', 'paga')->count() }}</strong>
                            </div>
                            <div class="vs-kpi">
                                <span>Total</span>
                                <strong>{{ $veiculo->multas->count() }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="vs-filter-wrap">
                        <div class="vs-filter-top">
                            <div class="vs-filter-search">
                                <i class="fas fa-search"></i>
                                <input type="text" id="mtf_busca" class="vs-search-input" placeholder="Buscar por órgão, auto, local ou reserva..." oninput="filtrarMt()">
                            </div>

                            <div class="vs-filter-actions">
                                <button type="button" class="vs-chip is-active" data-chip-group="multas" onclick="chipMt(this, 'todos')">Todas</button>
                                <button type="button" class="vs-chip" data-chip-group="multas" onclick="chipMt(this, 'pendente')">Pendentes</button>
                                <button type="button" class="vs-chip" data-chip-group="multas" onclick="chipMt(this, 'paga')">Pagas</button>
                                <button type="button" class="vs-chip" data-chip-group="multas" onclick="chipMt(this, 'recorrida')">Recorridas</button>
                                <button type="button" class="vs-chip" data-chip-group="multas" onclick="chipMt(this, 'com_desconto')">Com desconto</button>
                                <button type="button" class="btn-xp btn-xp--sm btn-xp--outline" onclick="toggleFiltrosAvancados('multas')">
                                    <i class="fas fa-sliders-h mr-1"></i>Filtros avançados
                                </button>
                            </div>
                        </div>

                        <div class="vs-advanced-filters" id="vsAdvanced-multas">
                            <div class="vs-filter-grid">
                                <div class="vs-filter-field">
                                    <label>Status da Multa</label>
                                    <select id="mtf_status" class="vt-select" onchange="filtrarMt()">
                                        <option value="">Todos</option>
                                        @foreach(\App\Models\Multa::STATUS_MULTA as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="vs-filter-field">
                                    <label>Status Desconto</label>
                                    <select id="mtf_desconto" class="vt-select" onchange="filtrarMt()">
                                        <option value="">Todos</option>
                                        @foreach(\App\Models\Multa::STATUS_DESCONTO as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="vs-filter-field">
                                    <label>Órgão Autuador</label>
                                    <input type="text" id="mtf_orgao" class="vt-input" placeholder="Ex: DETRAN, PRF..." oninput="filtrarMt()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Nº Auto</label>
                                    <input type="text" id="mtf_auto" class="vt-input" placeholder="Número do auto" oninput="filtrarMt()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Infração de</label>
                                    <input type="date" id="mtf_data_de" class="vt-input" onchange="filtrarMt()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Infração até</label>
                                    <input type="date" id="mtf_data_ate" class="vt-input" onchange="filtrarMt()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Valor mín. (R$)</label>
                                    <input type="number" id="mtf_valor_min" class="vt-input" placeholder="0.00" step="0.01" oninput="filtrarMt()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Valor máx. (R$)</label>
                                    <input type="number" id="mtf_valor_max" class="vt-input" placeholder="9999.99" step="0.01" oninput="filtrarMt()">
                                </div>

                                <div class="vs-filter-field vs-filter-field--check">
                                    <label class="vs-check">
                                        <input type="checkbox" id="mtf_com_desconto" onchange="filtrarMt()">
                                        <span>Mostrar apenas multas com desconto aplicado</span>
                                    </label>
                                </div>
                            </div>

                            <div class="vs-filter-footer">
                                <div class="vs-filter-result" id="mtf_result"></div>
                                <div class="vs-filter-footer__actions">
                                    <button type="button" class="vt-btn-clear" onclick="limparFiltroMt()">
                                        <i class="fas fa-times"></i> Limpar
                                    </button>
                                    <button type="button" class="vt-btn-filter" onclick="filtrarMt()">
                                        <i class="fas fa-search"></i> Aplicar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vt-panel-actions">
                        <a target="_blank" href="{{ route('multa.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Cadastrar multa
                        </a>
                        <a target="_blank" href="{{ route('multa.index', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-list mr-1"></i>Ver todas
                        </a>
                    </div>

                    @if($veiculo->multas->isEmpty())
                        <div class="empty-state"><i class="fas fa-receipt"></i><p>Nenhuma multa registrada.</p></div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover xp-table" id="tabelaMt">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Data Infração</th>
                                    <th>Órgão</th>
                                    <th>Nº Auto</th>
                                    <th>Local</th>
                                    <th>Vencimento</th>
                                    <th>Pontos</th>
                                    <th class="text-right">Valor Orig.</th>
                                    <th class="text-right">Desconto</th>
                                    <th class="text-right">Valor Final</th>
                                    <th>Status Multa</th>
                                    <th>Status Desc.</th>
                                    <th>Reserva</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($veiculo->multas as $mt)
                                @php
                                    $mtBadge = match($mt->status_multa) {
                                        'paga','deferida' => 'success',
                                        'cancelada'       => 'secondary',
                                        'recorrida'       => 'info',
                                        'indeferida'      => 'danger',
                                        default           => 'warning',
                                    };
                                @endphp
                                <tr
                                    data-mt-status="{{ $mt->status_multa }}"
                                    data-mt-desconto="{{ $mt->status_desconto }}"
                                    data-mt-orgao="{{ strtolower($mt->orgao_autuador ?? '') }}"
                                    data-mt-auto="{{ strtolower($mt->numero_auto ?? '') }}"
                                    data-mt-data="{{ $mt->data_hora_infracao ? $mt->data_hora_infracao->format('Y-m-d') : '' }}"
                                    data-mt-valor="{{ (float)($mt->valor_final ?? 0) }}"
                                    data-mt-has-desconto="{{ $mt->valor_desconto !== null ? 1 : 0 }}"
                                >
                                    <td><span class="xp-id">#{{ $mt->id }}</span></td>
                                    <td>{{ $mt->data_hora_infracao ? $mt->data_hora_infracao->format('d/m/Y H:i') : '—' }}</td>
                                    <td>{{ $mt->orgao_autuador ?? '—' }}</td>
                                    <td>{{ $mt->numero_auto ?? '—' }}</td>
                                    <td class="obs-cell">{{ \Illuminate\Support\Str::limit($mt->local_infracao ?? '—', 30) }}</td>
                                    <td>{{ $mt->data_vencimento ? $mt->data_vencimento->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $mt->pontos ?? '—' }}</td>
                                    <td class="text-right">R$ {{ number_format((float)$mt->valor_original,2,',','.') }}</td>
                                    <td class="text-right">{{ $mt->valor_desconto !== null ? 'R$ '.number_format((float)$mt->valor_desconto,2,',','.') : '—' }}</td>
                                    <td class="text-right font-weight-bold">R$ {{ number_format((float)$mt->valor_final,2,',','.') }}</td>
                                    <td><span class="badge badge-{{ $mtBadge }}">{{ $mt->label_status_multa }}</span></td>
                                    <td><span class="badge badge-light text-dark">{{ $mt->label_status_desconto }}</span></td>
                                    <td>
                                        @if($mt->reserva?->codigoReserva?->codigo)
                                            <a href="{{ route('reserva.show', $mt->reserva->codigoReserva->codigo) }}" class="xp-link">#{{ $mt->reserva->codigoReserva->codigo }}</a>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('multa.show', $mt->id) }}" class="btn-xp btn-xp--sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
