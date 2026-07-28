<div class="vs-panel" data-step-panel="mov">
    <div class="vs-panel-head">
        <div>
            <span class="vs-panel-head__eyebrow">Etapa 03</span>
            <h4 class="vs-panel-head__title">Movimentações</h4>
            <p class="vs-panel-head__sub">Acompanhe saídas, chegadas, vínculo com reserva e OS em uma visão única.</p>
        </div>
        <div class="vs-kpis">
            <div class="vs-kpi">
                <span>Total</span>
                <strong>{{ $veiculo->movimentacoes->count() }}</strong>
            </div>
            <div class="vs-kpi">
                <span>Com reserva</span>
                <strong>{{ $veiculo->movimentacoes->whereNotNull('reserva_id')->count() }}</strong>
            </div>
            <div class="vs-kpi">
                <span>Sem chegada</span>
                <strong>{{ $veiculo->movimentacoes->whereNull('data_chegada_real')->count() }}</strong>
            </div>
        </div>
    </div>

    <div class="vs-filter-wrap">
        <div class="vs-filter-top">
            <div class="vs-filter-search">
                <i class="fas fa-search"></i>
                <input type="text" id="mvf_busca" class="vs-search-input" placeholder="Buscar por tipo, status, origem, destino, usuário, reserva ou OS..." oninput="filtrarMov()">
            </div>

            <div class="vs-filter-actions">
                <button type="button" class="vs-chip is-active" data-chip-group="mov" onclick="chipMov(this, 'todos')">Todas</button>
                <button type="button" class="vs-chip" data-chip-group="mov" onclick="chipMov(this, 'reserva')">Com reserva</button>
                <button type="button" class="vs-chip" data-chip-group="mov" onclick="chipMov(this, 'sem_chegada')">Sem chegada</button>
                <button type="button" class="vs-chip" data-chip-group="mov" onclick="chipMov(this, 'hoje')">Hoje</button>
                <button type="button" class="btn-xp btn-xp--sm btn-xp--outline" onclick="toggleFiltrosAvancados('mov')">
                    <i class="fas fa-sliders-h mr-1"></i>Filtros avançados
                </button>
            </div>
        </div>

        <div class="vs-advanced-filters" id="vsAdvanced-mov">
            <div class="vs-filter-grid">
                <div class="vs-filter-field">
                    <label>Tipo</label>
                    <select id="mvf_tipo" class="vt-select" onchange="filtrarMov()">
                        <option value="">Todos</option>
                        @foreach($veiculo->movimentacoes->pluck('tipoMovimentacao.nome')->filter()->unique() as $tn)
                            <option value="{{ strtolower($tn) }}">{{ $tn }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="vs-filter-field">
                    <label>Status</label>
                    <select id="mvf_status" class="vt-select" onchange="filtrarMov()">
                        <option value="">Todos</option>
                        @foreach($veiculo->movimentacoes->pluck('statusMovimentacao.nome')->filter()->unique() as $sn)
                            <option value="{{ strtolower($sn) }}">{{ $sn }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="vs-filter-field">
                    <label>Origem</label>
                    <input type="text" id="mvf_origem" class="vt-input" placeholder="Nome da estação" oninput="filtrarMov()">
                </div>

                <div class="vs-filter-field">
                    <label>Destino</label>
                    <input type="text" id="mvf_destino" class="vt-input" placeholder="Nome da estação" oninput="filtrarMov()">
                </div>

                <div class="vs-filter-field">
                    <label>Saída de</label>
                    <input type="date" id="mvf_data_de" class="vt-input" onchange="filtrarMov()">
                </div>

                <div class="vs-filter-field">
                    <label>Saída até</label>
                    <input type="date" id="mvf_data_ate" class="vt-input" onchange="filtrarMov()">
                </div>

                <div class="vs-filter-field">
                    <label>Reserva #</label>
                    <input type="number" id="mvf_reserva" class="vt-input" placeholder="ID" oninput="filtrarMov()">
                </div>

                <div class="vs-filter-field">
                    <label>Usuário</label>
                    <input type="text" id="mvf_usuario" class="vt-input" placeholder="Nome do usuário" oninput="filtrarMov()">
                </div>

                <div class="vs-filter-field vs-filter-field--check">
                    <label class="vs-check">
                        <input type="checkbox" id="mvf_com_reserva" onchange="filtrarMov()">
                        <span>Somente com reserva</span>
                    </label>
                </div>

                <div class="vs-filter-field vs-filter-field--check">
                    <label class="vs-check">
                        <input type="checkbox" id="mvf_sem_chegada" onchange="filtrarMov()">
                        <span>Somente sem chegada registrada</span>
                    </label>
                </div>
            </div>

            <div class="vs-filter-footer">
                <div class="vs-filter-result" id="mvf_result"></div>
                <div class="vs-filter-footer__actions">
                    <button type="button" class="vt-btn-clear" onclick="limparFiltroMov()">
                        <i class="fas fa-times"></i> Limpar
                    </button>
                    <button type="button" class="vt-btn-filter" onclick="filtrarMov()">
                        <i class="fas fa-search"></i> Aplicar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="vt-panel-actions">
        <a href="{{ route('movimentacao.create', ['veiculo_id' => $veiculo->id, 'tipo_movimentacao_id' => 10]) }}" class="btn-xp btn-xp--sm">
            <i class="fas fa-route mr-1"></i>Transferir entre estações
        </a>
    </div>

    @if($veiculo->movimentacoes->isEmpty())
        <div class="empty-state">
            <i class="fas fa-route"></i>
            <p>Nenhuma movimentação registrada.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover xp-table" id="tabelaMov">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Origem</th>
                        <th>Destino</th>
                        <th>Saída</th>
                        <th>KM Saída</th>
                        <th>Chegada</th>
                        <th>KM Chegada</th>
                        <th>Reserva</th>
                        <th>OS</th>
                        <th>Usuário</th>
                        <th>Obs</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($veiculo->movimentacoes->sortByDesc('id') as $mov)
                        @php
                            $mvTipo = strtolower(optional($mov->tipoMovimentacao)->nome ?? '');
                            $mvStatus = strtolower(optional($mov->statusMovimentacao)->nome ?? '');
                            $mvOrigem = strtolower(optional($mov->localOrigem)->nome ?? '');
                            $mvDestino = strtolower(optional($mov->localDestino)->nome ?? '');
                            $mvUsuario = strtolower(optional($mov->usuario)->nome ?? '');
                            $mvDataSaida = $mov->data_saida ? \Carbon\Carbon::parse($mov->data_saida)->format('Y-m-d') : '';
                            $statusCor = strtolower((string) (optional($mov->statusMovimentacao)->cor ?? 'secondary'));
                            $statusBadgeClass = [
                                'success' => 'bg-green',
                                'info' => 'bg-blue',
                                'primary' => 'bg-blue',
                                'warning' => 'bg-amber',
                                'orange' => 'bg-orange',
                                'danger' => 'bg-red',
                                'secondary' => 'bg-gray',
                                'gray' => 'bg-gray',
                                'grey' => 'bg-gray',
                                'purple' => 'bg-purple',
                                'teal' => 'bg-teal',
                            ][$statusCor] ?? 'bg-gray';
                        @endphp
                        <tr
                            data-mv-tipo="{{ $mvTipo }}"
                            data-mv-status="{{ $mvStatus }}"
                            data-mv-origem="{{ $mvOrigem }}"
                            data-mv-destino="{{ $mvDestino }}"
                            data-mv-reserva="{{ $mov->reserva_id ?? '' }}"
                            data-mv-usuario="{{ $mvUsuario }}"
                            data-mv-data="{{ $mvDataSaida }}"
                            data-mv-chegada="{{ $mov->data_chegada_real ? 1 : 0 }}"
                            data-mv-numero="{{ strtolower((string) $mov->id) }}"
                        >
                            <td><span class="xp-id">#{{ $mov->id }}</span></td>
                            <td>{{ optional($mov->tipoMovimentacao)->nome ?? '—' }}</td>
                            <td>
                                <span class="status-badge badge badge-xs {{ $statusBadgeClass }}">
                                    {{ optional($mov->statusMovimentacao)->nome ?? '—' }}
                                </span>
                            </td>
                            <td>{{ optional($mov->localOrigem)->nome ?? '—' }}</td>
                            <td>{{ optional($mov->localDestino)->nome ?? '—' }}</td>
                            <td>{{ $mov->data_saida ? \Carbon\Carbon::parse($mov->data_saida)->format('d/m/Y H:i') : '—' }}</td>
                            <td>{{ $mov->km_saida !== null ? number_format((float) $mov->km_saida, 0, ',', '.') : '—' }}</td>
                            <td>
                                @if($mov->data_chegada_real)
                                    {{ \Carbon\Carbon::parse($mov->data_chegada_real)->format('d/m/Y H:i') }}
                                @elseif($mov->data_chegada_prevista)
                                    <span class="text-muted">{{ \Carbon\Carbon::parse($mov->data_chegada_prevista)->format('d/m/Y H:i') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $mov->km_chegada !== null ? number_format((float) $mov->km_chegada, 0, ',', '.') : '—' }}</td>
                            <td>
                                @if($mov->reserva?->codigoReserva?->codigo)
                                    <a href="{{ route('reserva.show', $mov->reserva->codigoReserva->codigo) }}" class="xp-link">#{{ $mov->reserva->codigoReserva->codigo }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($mov->ordem_servico_id)
                                    <a href="{{ route('ordem-servico.show', $mov->ordem_servico_id) }}" class="xp-link">
                                        {{ optional($mov->ordemServico)->numero ?? ('#'.$mov->ordem_servico_id) }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ optional($mov->usuario)->nome ?? '—' }}</td>
                            <td class="obs-cell">{{ \Illuminate\Support\Str::limit((string) ($mov->observacoes ?? ''), 50) ?: '—' }}</td>
                            <td>
                                <a href="{{ route('movimentacao.edit', $mov->id) }}" class="btn btn-xs btn-outline-primary">
                                    <i class="fas fa-edit"></i>
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
    const tabela = document.getElementById('tabelaMov');
    if (!tabela) return;
    const chips = Array.prototype.slice.call(document.querySelectorAll('[data-chip-group="mov"]'));

    function normalize(value) {
        return (value || '').toString().trim().toLowerCase();
    }

    function inNumberRange(value, min, max) {
        const num = parseFloat(String(value || '0').replace(',', '.')) || 0;
        if (min !== '' && !isNaN(parseFloat(min)) && num < parseFloat(min)) return false;
        if (max !== '' && !isNaN(parseFloat(max)) && num > parseFloat(max)) return false;
        return true;
    }

    window.chipMov = function (button, action) {
        chips.forEach(function (chip) { chip.classList.remove('is-active'); });
        if (button) button.classList.add('is-active');

        const comReserva = document.getElementById('mvf_com_reserva');
        const semChegada = document.getElementById('mvf_sem_chegada');
        if (comReserva) comReserva.checked = false;
        if (semChegada) semChegada.checked = false;

        filtrarMov();
    };

    window.filtrarMov = function () {
        const busca = normalize(document.getElementById('mvf_busca')?.value);
        const tipo = normalize(document.getElementById('mvf_tipo')?.value);
        const status = normalize(document.getElementById('mvf_status')?.value);
        const origem = normalize(document.getElementById('mvf_origem')?.value);
        const destino = normalize(document.getElementById('mvf_destino')?.value);
        const dataDe = document.getElementById('mvf_data_de')?.value || '';
        const dataAte = document.getElementById('mvf_data_ate')?.value || '';
        const reserva = normalize(document.getElementById('mvf_reserva')?.value);
        const usuario = normalize(document.getElementById('mvf_usuario')?.value);
        const comReserva = document.getElementById('mvf_com_reserva')?.checked;
        const semChegada = document.getElementById('mvf_sem_chegada')?.checked;

        const rows = Array.prototype.slice.call(document.querySelectorAll('#tabelaMov tbody tr'));
        let visiveis = 0;

        rows.forEach(function (row) {
            const ds = row.dataset || {};
            const texto = normalize(row.textContent);

            const ok =
                (!busca || texto.includes(busca)) &&
                (!tipo || normalize(ds.mvTipo) === tipo) &&
                (!status || normalize(ds.mvStatus) === status) &&
                (!origem || normalize(ds.mvOrigem).includes(origem)) &&
                (!destino || normalize(ds.mvDestino).includes(destino)) &&
                (!dataDe || ds.mvData >= dataDe) &&
                (!dataAte || ds.mvData <= dataAte) &&
                (!reserva || normalize(ds.mvReserva).includes(reserva)) &&
                (!usuario || normalize(ds.mvUsuario).includes(usuario)) &&
                (!comReserva || (ds.mvReserva || '') !== '') &&
                (!semChegada || ds.mvChegada === '0');

            row.style.display = ok ? '' : 'none';
            if (ok) visiveis++;
        });

        const result = document.getElementById('mvf_result');
        if (result) {
            result.textContent = visiveis + ' registro(s) visível(eis)';
        }
    };

    window.limparFiltroMov = function () {
        ['mvf_busca', 'mvf_tipo', 'mvf_status', 'mvf_origem', 'mvf_destino', 'mvf_data_de', 'mvf_data_ate', 'mvf_reserva', 'mvf_usuario'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const comReserva = document.getElementById('mvf_com_reserva');
        const semChegada = document.getElementById('mvf_sem_chegada');
        if (comReserva) comReserva.checked = false;
        if (semChegada) semChegada.checked = false;
        chips.forEach(function (chip) { chip.classList.remove('is-active'); });
        const firstChip = document.querySelector('[data-chip-group="mov"]');
        if (firstChip) firstChip.classList.add('is-active');
        filtrarMov();
    };

    filtrarMov();
})();
</script>
@endpush
