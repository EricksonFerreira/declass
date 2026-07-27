{{-- ── PAINEL: Sinistros ── --}}
                <div class="vs-panel" data-step-panel="sinistros">
                    <div class="vs-panel-head">
                        <div>
                            <span class="vs-panel-head__eyebrow">Etapa 06</span>
                            <h4 class="vs-panel-head__title">Sinistros</h4>
                            <p class="vs-panel-head__sub">Entenda rapidamente status, período, seguradora e números do processo.</p>
                        </div>
                        <div class="vs-kpis">
                            <div class="vs-kpi">
                                <span>Abertos</span>
                                <strong>{{ $veiculo->sinistros->whereIn('status', ['aberto','em_analise','aguardando_documentos'])->count() }}</strong>
                            </div>
                            <div class="vs-kpi">
                                <span>Encerrados</span>
                                <strong>{{ $veiculo->sinistros->whereIn('status', ['encerrado','indenizado'])->count() }}</strong>
                            </div>
                            <div class="vs-kpi">
                                <span>Total</span>
                                <strong>{{ $veiculo->sinistros->count() }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="vs-filter-wrap">
                        <div class="vs-filter-top">
                            <div class="vs-filter-search">
                                <i class="fas fa-search"></i>
                                <input type="text" id="snf_busca" class="vs-search-input" placeholder="Buscar por local, seguradora, número ou tipo..." oninput="filtrarSn()">
                            </div>

                            <div class="vs-filter-actions">
                                <button type="button" class="vs-chip is-active" data-chip-group="sinistros" onclick="chipSn(this, 'todos')">Todos</button>
                                <button type="button" class="vs-chip" data-chip-group="sinistros" onclick="chipSn(this, 'aberto')">Abertos</button>
                                <button type="button" class="vs-chip" data-chip-group="sinistros" onclick="chipSn(this, 'aguardando_documentos')">Aguardando docs</button>
                                <button type="button" class="vs-chip" data-chip-group="sinistros" onclick="chipSn(this, 'encerrado')">Encerrados</button>
                                <button type="button" class="btn-xp btn-xp--sm btn-xp--outline" onclick="toggleFiltrosAvancados('sinistros')">
                                    <i class="fas fa-sliders-h mr-1"></i>Filtros avançados
                                </button>
                            </div>
                        </div>

                        <div class="vs-advanced-filters" id="vsAdvanced-sinistros">
                            <div class="vs-filter-grid">
                                <div class="vs-filter-field">
                                    <label>Status</label>
                                    <select id="snf_status" class="vt-select" onchange="filtrarSn()">
                                        <option value="">Todos</option>
                                        @foreach(\App\Models\Sinistro::STATUS as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="vs-filter-field">
                                    <label>Tipo</label>
                                    <select id="snf_tipo" class="vt-select" onchange="filtrarSn()">
                                        <option value="">Todos</option>
                                        @foreach(\App\Models\Sinistro::TIPOS as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="vs-filter-field">
                                    <label>Data ocorrido de</label>
                                    <input type="date" id="snf_data_de" class="vt-input" onchange="filtrarSn()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Data ocorrido até</label>
                                    <input type="date" id="snf_data_ate" class="vt-input" onchange="filtrarSn()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Seguradora</label>
                                    <input type="text" id="snf_seguradora" class="vt-input" placeholder="Nome da seguradora" oninput="filtrarSn()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Nº Sinistro Seguradora</label>
                                    <input type="text" id="snf_num_seg" class="vt-input" placeholder="Ex: SIN-2026-001" oninput="filtrarSn()">
                                </div>
                            </div>

                            <div class="vs-filter-footer">
                                <div class="vs-filter-result" id="snf_result"></div>
                                <div class="vs-filter-footer__actions">
                                    <button type="button" class="vt-btn-clear" onclick="limparFiltroSn()">
                                        <i class="fas fa-times"></i> Limpar
                                    </button>
                                    <button type="button" class="vt-btn-filter" onclick="filtrarSn()">
                                        <i class="fas fa-search"></i> Aplicar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vt-panel-actions">
                        <a href="{{ route('sinistro.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Registrar sinistro
                        </a>
                        <a href="{{ route('sinistro.index', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-list mr-1"></i>Ver todos
                        </a>
                    </div>

                    @if($veiculo->sinistros->isEmpty())
                        <div class="empty-state"><i class="fas fa-shield-alt"></i><p>Nenhum sinistro registrado.</p></div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover xp-table" id="tabelaSn">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th>Local</th>
                                    <th>Seguradora</th>
                                    <th>Nº Seg.</th>
                                    <th>Prejuízo Est.</th>
                                    <th>Resp. Cliente</th>
                                    <th>Status</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($veiculo->sinistros as $sn)
                                @php
                                    $snBadge = match($sn->status) {
                                        'encerrado','indenizado' => 'success',
                                        'aprovado'  => 'primary',
                                        'cancelado' => 'secondary',
                                        'aguardando_documentos' => 'warning',
                                        default     => 'danger',
                                    };
                                @endphp
                                <tr
                                    data-sn-status="{{ $sn->status }}"
                                    data-sn-tipo="{{ $sn->tipo_sinistro }}"
                                    data-sn-seguradora="{{ strtolower($sn->seguradora ?? '') }}"
                                    data-sn-num-seg="{{ strtolower($sn->numero_sinistro_seguradora ?? '') }}"
                                    data-sn-data="{{ $sn->data_hora_ocorrido ? $sn->data_hora_ocorrido->format('Y-m-d') : '' }}"
                                >
                                    <td><span class="xp-id">#{{ $sn->id }}</span></td>
                                    <td>{{ $sn->label_tipo }}</td>
                                    <td>{{ $sn->data_hora_ocorrido ? $sn->data_hora_ocorrido->format('d/m/Y') : '—' }}</td>
                                    <td class="obs-cell">{{ \Illuminate\Support\Str::limit($sn->local_ocorrido ?? '—', 35) }}</td>
                                    <td>{{ $sn->seguradora ?? '—' }}</td>
                                    <td>{{ $sn->numero_sinistro_seguradora ?? '—' }}</td>
                                    <td>{{ $sn->valor_prejuizo_estimado !== null ? 'R$ '.number_format((float)$sn->valor_prejuizo_estimado,2,',','.') : '—' }}</td>
                                    <td>{{ $sn->valor_responsabilidade_cliente !== null ? 'R$ '.number_format((float)$sn->valor_responsabilidade_cliente,2,',','.') : '—' }}</td>
                                    <td><span class="badge badge-{{ $snBadge }}">{{ $sn->label_status }}</span></td>
                                    <td>
                                        <a href="{{ route('sinistro.show', $sn->id) }}" class="btn-xp btn-xp--sm">
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
