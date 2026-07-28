<div class="vs-panel" data-step-panel="os">
    <div class="vs-panel-head">
        <div>
            <span class="vs-panel-head__eyebrow">Etapa 04</span>
            <h4 class="vs-panel-head__title">Ordens de serviço</h4>
            <p class="vs-panel-head__sub">Acompanhe aberturas, andamento, conclusão e vínculo com avarias em uma leitura só.</p>
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
                <input type="text" id="osf_busca" class="vs-search-input" placeholder="Buscar por número, status, categoria, avarias ou observações..." oninput="filtrarOs()">
            </div>

            <div class="vs-filter-actions">
                <button type="button" class="vs-chip is-active" data-chip-group="os" onclick="chipOs(this, 'todos')">Todas</button>
                <button type="button" class="vs-chip" data-chip-group="os" onclick="chipOs(this, 'aberta')">Abertas</button>
                <button type="button" class="vs-chip" data-chip-group="os" onclick="chipOs(this, 'em_andamento')">Em andamento</button>
                <button type="button" class="vs-chip" data-chip-group="os" onclick="chipOs(this, 'concluida')">Concluídas</button>
                <button type="button" class="vs-chip" data-chip-group="os" onclick="chipOs(this, 'cancelada')">Canceladas</button>
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
                        <option value="em_andamento">Em andamento</option>
                        <option value="concluida">Concluída</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>

                <div class="vs-filter-field">
                    <label>Número / ID</label>
                    <input type="text" id="osf_numero" class="vt-input" placeholder="Ex: OS-001" oninput="filtrarOs()">
                </div>

                <div class="vs-filter-field">
                    <label>Categoria</label>
                    <input type="text" id="osf_categoria" class="vt-input" placeholder="Ex: mecânica" oninput="filtrarOs()">
                </div>

                <div class="vs-filter-field">
                    <label>Data de abertura</label>
                    <input type="date" id="osf_data_de" class="vt-input" onchange="filtrarOs()">
                </div>

                <div class="vs-filter-field">
                    <label>Data até</label>
                    <input type="date" id="osf_data_ate" class="vt-input" onchange="filtrarOs()">
                </div>

                <div class="vs-filter-field">
                    <label>Valor mínimo</label>
                    <input type="number" step="0.01" id="osf_valor_min" class="vt-input" placeholder="0,00" oninput="filtrarOs()">
                </div>

                <div class="vs-filter-field">
                    <label>Valor máximo</label>
                    <input type="number" step="0.01" id="osf_valor_max" class="vt-input" placeholder="0,00" oninput="filtrarOs()">
                </div>

                <div class="vs-filter-field vs-filter-field--check">
                    <label class="vs-check">
                        <input type="checkbox" id="osf_com_avarias" onchange="filtrarOs()">
                        <span>Somente OS com avarias vinculadas</span>
                    </label>
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
        <div class="empty-state">
            <i class="fas fa-tools"></i>
            <p>Nenhuma ordem de serviço registrada.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover xp-table" id="tabelaOs">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Status</th>
                        <th>Abertura</th>
                        <th>Conclusão</th>
                        <th>Categoria</th>
                        <th>Itens</th>
                        <th>Valor</th>
                        <th>Vínculos</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($veiculo->ordensServico->sortByDesc('id') as $os)
                        @php
                            $osStatus = strtolower((string) ($os->status ?? ''));
                            $osNumero = strtolower((string) ($os->numero ?? $os->id));
                            $osData = $os->created_at ? $os->created_at->format('Y-m-d') : '';
                            $osCategoria = strtolower((string) ($os->categoria?->nome ?? ''));
                            $osTemAvarias = $os->itens->whereNotNull('veiculo_dano_id')->count() > 0;
                            $statusBadgeClass = match ($os->status) {
                                \App\Models\OrdemServico::STATUS_ABERTA => 'badge-warning',
                                \App\Models\OrdemServico::STATUS_EM_ANDAMENTO => 'badge-primary',
                                \App\Models\OrdemServico::STATUS_CONCLUIDA => 'badge-success',
                                \App\Models\OrdemServico::STATUS_CANCELADA => 'badge-secondary',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <tr
                            data-os-status="{{ $osStatus }}"
                            data-os-numero="{{ $osNumero }}"
                            data-os-data="{{ $osData }}"
                            data-os-categoria="{{ $osCategoria }}"
                            data-os-valor="{{ (float) ($os->valor_total ?? 0) }}"
                            data-os-avarias="{{ $osTemAvarias ? '1' : '0' }}"
                        >
                            <td>
                                <div class="font-weight-bold">{{ $os->numero ?? '#' . $os->id }}</div>
                                <div class="text-muted text-xs">OS #{{ $os->id }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $statusBadgeClass }}">
                                    {{ ucfirst(str_replace('_', ' ', (string) $os->status)) }}
                                </span>
                            </td>
                            <td>{{ $os->created_at ? $os->created_at->format('d/m/Y') : '—' }}</td>
                            <td>{{ $os->data_conclusao ? $os->data_conclusao->format('d/m/Y') : '—' }}</td>
                            <td>{{ $os->categoria?->nome ?? '—' }}</td>
                            <td>{{ $os->itens ? $os->itens->count() : '—' }}</td>
                            <td>{{ $os->valor_total > 0 ? 'R$ ' . number_format((float) $os->valor_total, 2, ',', '.') : '—' }}</td>
                            <td>
                                @if($os->reserva?->codigoReserva?->codigo)
                                    <div class="text-xs"><i class="fas fa-calendar-alt mr-1"></i>Reserva #{{ $os->reserva->codigoReserva->codigo }}</div>
                                @endif
                                @if($os->sinistro_id)
                                    <div class="text-xs"><i class="fas fa-shield-alt mr-1"></i>Sinistro #{{ $os->sinistro_id }}</div>
                                @endif
                                @if($osTemAvarias)
                                    <div class="text-xs"><i class="fas fa-car-crash mr-1"></i>Avarias vinculadas</div>
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

@push('js')
<script>
(function () {
    const tabela = document.getElementById('tabelaOs');
    if (!tabela) return;
    const chips = Array.prototype.slice.call(document.querySelectorAll('[data-chip-group="os"]'));

    function normalize(value) {
        return (value || '').toString().trim().toLowerCase();
    }

    function inRange(value, min, max) {
        const num = parseFloat(String(value || '0').replace(',', '.')) || 0;
        if (min !== '' && !isNaN(parseFloat(min)) && num < parseFloat(min)) return false;
        if (max !== '' && !isNaN(parseFloat(max)) && num > parseFloat(max)) return false;
        return true;
    }

    window.chipOs = function (button, action) {
        chips.forEach(function (chip) { chip.classList.remove('is-active'); });
        if (button) button.classList.add('is-active');

        const status = document.getElementById('osf_status');
        if (status) status.value = '';

        if (action === 'aberta' || action === 'em_andamento' || action === 'concluida' || action === 'cancelada') {
            if (status) status.value = action;
        }

        filtrarOs();
    };

    window.filtrarOs = function () {
        const busca = normalize(document.getElementById('osf_busca')?.value);
        const status = normalize(document.getElementById('osf_status')?.value);
        const numero = normalize(document.getElementById('osf_numero')?.value);
        const categoria = normalize(document.getElementById('osf_categoria')?.value);
        const dataDe = document.getElementById('osf_data_de')?.value || '';
        const dataAte = document.getElementById('osf_data_ate')?.value || '';
        const valorMin = document.getElementById('osf_valor_min')?.value || '';
        const valorMax = document.getElementById('osf_valor_max')?.value || '';
        const comAvarias = document.getElementById('osf_com_avarias')?.checked;

        const rows = Array.prototype.slice.call(document.querySelectorAll('#tabelaOs tbody tr'));
        let visiveis = 0;

        rows.forEach(function (row) {
            const ds = row.dataset || {};
            const texto = normalize(row.textContent);

            const ok =
                (!busca || texto.includes(busca)) &&
                (!status || normalize(ds.osStatus) === status) &&
                (!numero || normalize(ds.osNumero).includes(numero)) &&
                (!categoria || normalize(ds.osCategoria).includes(categoria)) &&
                (!dataDe || ds.osData >= dataDe) &&
                (!dataAte || ds.osData <= dataAte) &&
                inRange(ds.osValor, valorMin, valorMax) &&
                (!comAvarias || ds.osAvarias === '1');

            row.style.display = ok ? '' : 'none';
            if (ok) visiveis++;
        });

        const result = document.getElementById('osf_result');
        if (result) {
            result.textContent = visiveis + ' registro(s) visível(eis)';
        }
    };

    window.limparFiltroOs = function () {
        ['osf_busca', 'osf_status', 'osf_numero', 'osf_categoria', 'osf_data_de', 'osf_data_ate', 'osf_valor_min', 'osf_valor_max'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const avarias = document.getElementById('osf_com_avarias');
        if (avarias) avarias.checked = false;
        chips.forEach(function (chip) { chip.classList.remove('is-active'); });
        const firstChip = document.querySelector('[data-chip-group="os"]');
        if (firstChip) firstChip.classList.add('is-active');
        filtrarOs();
    };

    filtrarOs();
})();
</script>
@endpush
