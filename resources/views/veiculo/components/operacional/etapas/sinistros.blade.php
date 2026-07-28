<div class="vs-panel" data-step-panel="sinistros">
    <div class="vs-panel-head">
        <div>
            <span class="vs-panel-head__eyebrow">Etapa 05</span>
            <h4 class="vs-panel-head__title">Sinistros</h4>
            <p class="vs-panel-head__sub">Acompanhe ocorrências, responsável, cobranças e o andamento documental do sinistro.</p>
        </div>
        <div class="vs-kpis">
            <div class="vs-kpi">
                <span>Abertos</span>
                <strong>{{ $veiculo->sinistros->whereIn('status', ['aberto', 'em_analise'])->count() }}</strong>
            </div>
            <div class="vs-kpi">
                <span>Aguardando docs</span>
                <strong>{{ $veiculo->sinistros->where('status', 'aguardando_documentos')->count() }}</strong>
            </div>
            <div class="vs-kpi">
                <span>Encerrados</span>
                <strong>{{ $veiculo->sinistros->whereIn('status', ['encerrado', 'indenizado'])->count() }}</strong>
            </div>
        </div>
    </div>

    <div class="vs-filter-wrap">
        <div class="vs-filter-top">
            <div class="vs-filter-search">
                <i class="fas fa-search"></i>
                <input type="text" id="snf_busca" class="vs-search-input" placeholder="Buscar por tipo, status, seguradora ou descrição..." oninput="filtrarSn()">
            </div>

            <div class="vs-filter-actions">
                <button type="button" class="vs-chip is-active" data-chip-group="sinistros" onclick="chipSn(this, 'todos')">Todos</button>
                <button type="button" class="vs-chip" data-chip-group="sinistros" onclick="chipSn(this, 'aberto')">Abertos</button>
                <button type="button" class="vs-chip" data-chip-group="sinistros" onclick="chipSn(this, 'em_analise')">Em análise</button>
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
                        <option value="aberto">Aberto</option>
                        <option value="em_analise">Em análise</option>
                        <option value="aguardando_documentos">Aguardando documentos</option>
                        <option value="aprovado">Aprovado</option>
                        <option value="indenizado">Indenizado</option>
                        <option value="encerrado">Encerrado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <div class="vs-filter-field">
                    <label>Tipo</label>
                    <select id="snf_tipo" class="vt-select" onchange="filtrarSn()">
                        <option value="">Todos</option>
                        <option value="colisao">Colisão</option>
                        <option value="furto_roubo">Furto / Roubo</option>
                        <option value="incendio">Incêndio</option>
                        <option value="vandalismo">Vandalismo</option>
                        <option value="alagamento">Alagamento</option>
                        <option value="outros">Outros</option>
                    </select>
                </div>

                <div class="vs-filter-field">
                    <label>Data de</label>
                    <input type="date" id="snf_data_de" class="vt-input" onchange="filtrarSn()">
                </div>

                <div class="vs-filter-field">
                    <label>Data até</label>
                    <input type="date" id="snf_data_ate" class="vt-input" onchange="filtrarSn()">
                </div>

                <div class="vs-filter-field">
                    <label>Seguradora</label>
                    <input type="text" id="snf_seguradora" class="vt-input" placeholder="Nome da seguradora" oninput="filtrarSn()">
                </div>

                <div class="vs-filter-field">
                    <label>Número do BO / sinistro</label>
                    <input type="text" id="snf_num_seg" class="vt-input" placeholder="Ex: BO ou apólice" oninput="filtrarSn()">
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
        <a href="{{ route('sinistro.create', ['veiculo_id' => $veiculo->id]) }}" class="btn-xp btn-xp--sm btn-xp--danger">
            <i class="fas fa-plus mr-1"></i>Novo sinistro
        </a>
    </div>

    @if($veiculo->sinistros->isEmpty())
        <div class="empty-state">
            <i class="fas fa-shield-alt"></i>
            <p>Nenhum sinistro registrado para este veículo.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover xp-table" id="tabelaSn">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Ocorrência</th>
                        <th>Responsável</th>
                        <th>Valores</th>
                        <th>Vínculos</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($veiculo->sinistros->sortByDesc('id') as $sn)
                        @php
                            $snTipo = strtolower((string) $sn->tipo_sinistro);
                            $snStatus = strtolower((string) $sn->status);
                            $snData = $sn->data_hora_ocorrido ? $sn->data_hora_ocorrido->format('Y-m-d') : '';
                            $snSeguradora = strtolower((string) ($sn->seguradora ?? ''));
                            $snNumero = strtolower((string) ($sn->numero_sinistro_seguradora ?? $sn->numero_bo ?? $sn->id));
                        @endphp
                        <tr
                            data-sn-status="{{ $snStatus }}"
                            data-sn-tipo="{{ $snTipo }}"
                            data-sn-data="{{ $snData }}"
                            data-sn-seguradora="{{ $snSeguradora }}"
                            data-sn-num-seg="{{ $snNumero }}"
                        >
                            <td>
                                <div class="font-weight-bold">{{ $sn->labelTipo }}</div>
                                <div class="text-muted text-xs">{{ $sn->seguradora ?: 'Sem seguradora' }}</div>
                            </td>
                            <td>
                                @if($snStatus === 'aberto')
                                    <span class="badge badge-warning">Aberto</span>
                                @elseif($snStatus === 'em_analise')
                                    <span class="badge badge-info">Em análise</span>
                                @elseif($snStatus === 'aguardando_documentos')
                                    <span class="badge badge-secondary">Aguardando docs</span>
                                @elseif($snStatus === 'aprovado')
                                    <span class="badge badge-primary">Aprovado</span>
                                @elseif($snStatus === 'indenizado')
                                    <span class="badge badge-success">Indenizado</span>
                                @elseif($snStatus === 'encerrado')
                                    <span class="badge badge-dark">Encerrado</span>
                                @else
                                    <span class="badge badge-light">Cancelado</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $sn->data_hora_ocorrido ? $sn->data_hora_ocorrido->format('d/m/Y H:i') : '—' }}</div>
                                <div class="text-muted text-xs">{{ $sn->local_ocorrido ?: 'Sem local informado' }}</div>
                            </td>
                            <td>
                                {{ $sn->responsavel?->nome ?? '—' }}
                                @if($sn->condutor?->nome)
                                    <div class="text-muted text-xs">Condutor: {{ $sn->condutor->nome }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="text-xs text-muted">Prejuízo: {{ $sn->valor_prejuizo_estimado ? 'R$ ' . number_format((float) $sn->valor_prejuizo_estimado, 2, ',', '.') : '—' }}</div>
                                <div class="text-xs text-muted">Cliente: {{ $sn->valor_responsabilidade_cliente ? 'R$ ' . number_format((float) $sn->valor_responsabilidade_cliente, 2, ',', '.') : '—' }}</div>
                                <div class="text-xs text-muted">Empresa: {{ $sn->valor_custo_empresa ? 'R$ ' . number_format((float) $sn->valor_custo_empresa, 2, ',', '.') : '—' }}</div>
                            </td>
                            <td>
                                @if($sn->cliente)
                                    <div class="text-xs"><i class="fas fa-user mr-1"></i>{{ $sn->cliente->nome }}</div>
                                @endif
                                @if($sn->reserva)
                                    <div class="text-xs"><i class="fas fa-calendar-alt mr-1"></i>Reserva #{{ $sn->reserva->id }}</div>
                                @endif
                                @if($sn->ordensServico->isNotEmpty())
                                    <div class="text-xs"><i class="fas fa-tools mr-1"></i>{{ $sn->ordensServico->count() }} OS vinculada(s)</div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('sinistro.show', $sn->id) }}" class="btn-xp btn-xp--sm">
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

@push('js')
<script>
(function () {
    const chips = Array.prototype.slice.call(document.querySelectorAll('[data-chip-group="sinistros"]'));
    const tabela = document.getElementById('tabelaSn');
    if (!tabela) return;

    function normalize(value) {
        return (value || '').toString().trim().toLowerCase();
    }

    function matchesRange(value, from, to) {
        if (!value) return false;
        if (from && value < from) return false;
        if (to && value > to) return false;
        return true;
    }

    window.chipSn = function (button, action) {
        chips.forEach(function (chip) { chip.classList.remove('is-active'); });
        if (button) button.classList.add('is-active');

        const status = document.getElementById('snf_status');
        if (status) status.value = '';

        if (action === 'aberto' || action === 'em_analise' || action === 'aguardando_documentos' || action === 'encerrado') {
            if (status) status.value = action;
        }

        filtrarSn();
    };

    window.filtrarSn = function () {
        const busca = normalize(document.getElementById('snf_busca')?.value);
        const status = normalize(document.getElementById('snf_status')?.value);
        const tipo = normalize(document.getElementById('snf_tipo')?.value);
        const dataDe = document.getElementById('snf_data_de')?.value || '';
        const dataAte = document.getElementById('snf_data_ate')?.value || '';
        const seguradora = normalize(document.getElementById('snf_seguradora')?.value);
        const numSeg = normalize(document.getElementById('snf_num_seg')?.value);

        const rows = Array.prototype.slice.call(document.querySelectorAll('#tabelaSn tbody tr'));
        let visiveis = 0;

        rows.forEach(function (row) {
            const ds = row.dataset || {};
            const texto = normalize(row.textContent);

            const ok =
                (!busca || texto.includes(busca)) &&
                (!status || normalize(ds.snStatus) === status) &&
                (!tipo || normalize(ds.snTipo) === tipo) &&
                matchesRange(ds.snData || '', dataDe, dataAte) &&
                (!seguradora || normalize(ds.snSeguradora).includes(seguradora)) &&
                (!numSeg || normalize(ds.snNumSeg).includes(numSeg));

            row.style.display = ok ? '' : 'none';
            if (ok && !row.classList.contains('bg-light')) {
                visiveis++;
            }
        });

        const result = document.getElementById('snf_result');
        if (result) {
            result.textContent = visiveis + ' registro(s) visível(eis)';
        }
    };

    window.limparFiltroSn = function () {
        ['snf_busca', 'snf_status', 'snf_tipo', 'snf_data_de', 'snf_data_ate', 'snf_seguradora', 'snf_num_seg'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = '';
        });

        chips.forEach(function (chip) {
            chip.classList.remove('is-active');
        });

        const firstChip = document.querySelector('[data-chip-group="sinistros"]');
        if (firstChip) firstChip.classList.add('is-active');

        filtrarSn();
    };

    filtrarSn();
})();
</script>
@endpush
