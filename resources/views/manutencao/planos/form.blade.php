@php
    $isEdit = !empty($plano);
    $formTitle = $isEdit ? 'Editar plano' : 'Criar plano';
    $formSubtitle = $isEdit
        ? 'Atualize o escopo do plano, veículo vinculado e os itens periódicos.'
        : 'Organize o plano por veículo, grupo de frota e itens periódicos.';
    $submitLabel = 'Salvar plano';
    $grupoSelecionado = old('grupo_frota_id', $plano->grupo_frota_id ?? null);
    $veiculoIdSelecionado = old('veiculo_id', $plano->veiculo_id ?? null);
    $veiculoDisplaySelecionado = old('veiculo_display', $plano->veiculo?->codigo ?? $plano->veiculo?->placa ?? '');
    $rota = $rota ?? ($isEdit ? route('manutencao.planos.update', $plano->id) : route('manutencao.planos.store'));
    $metodo = $metodo ?? ($isEdit ? 'PUT' : 'POST');
@endphp

<style>
    .os-form-page {
        padding-bottom: 8px;
    }
    .os-main-card,
    .os-side-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }
    .os-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 14px;
        margin-top: 8px;
    }
    .os-tip {
        padding: 10px 12px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e5eaf2;
        margin-bottom: 8px;
        font-size: 0.8rem;
        color: #475569;
    }
    .os-tip strong {
        color: #0f172a;
    }
    .manutencao-table-wrap {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .manutencao-table-wrap .table {
        min-width: 640px;
    }
    .manutencao-table-wrap thead th {
        background: #f8fafc;
        font-weight: 800;
        color: #334155;
        white-space: nowrap;
    }
    .manutencao-table-wrap tbody tr:hover {
        background: #f8fbff;
    }
    .manutencao-form .form-control,
    .manutencao-form select,
    .manutencao-form textarea {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 42px;
    }
    .manutencao-form .btn {
        border-radius: 10px;
        font-weight: 700;
    }
    @media (max-width: 767.98px) {
        .manutencao-table-wrap {
            overflow-x: auto;
        }
        .manutencao-table-wrap .table {
            min-width: 720px;
        }
    }
</style>

<div class="container-fluid px-0 os-form-page">
    <div class="row">
        <div class="col-xl-9 col-lg-8">
            <div class="card p-2 os-main-card">
                <div class="card-body">
                    <form method="POST" action="{{ $rota }}" class="manutencao-form">
                        @csrf
                        @if (!empty($metodo) && strtoupper($metodo) === 'PUT')
                            @method('PUT')
                        @endif

                        <div class="form-row">
                            <div class="col-lg-7 form-group">
                                <label class="form-label-sm">Nome do Plano <span class="text-danger">*</span></label>
                                <input type="text" name="nome" class="form-control form-control-sm" value="{{ old('nome', $plano->nome ?? '') }}" required>
                            </div>
                            <div class="col-lg-5 form-group">
                                <label class="form-label-sm">Grupo de Frota</label>
                                <select name="grupo_frota_id" class="form-control form-control-sm">
                                    <option value="">- Opcional -</option>
                                    @foreach ($grupos as $grupo)
                                        <option value="{{ $grupo->id }}" {{ (string) $grupoSelecionado === (string) $grupo->id ? 'selected' : '' }}>
                                            {{ $grupo->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 form-group">
                                <label class="form-label-sm">Descrição</label>
                                <textarea name="descricao" class="form-control form-control-sm" rows="4">{{ old('descricao', $plano->descricao ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="alert alert-info small py-2 px-3 mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Sinalização do vínculo:</strong>
                            se você escolher apenas o <strong>grupo de frota</strong>, o plano será aplicado a <strong>todos os veículos do grupo</strong>.
                            Se preencher o <strong>veículo vinculado</strong>, o plano ficará <strong>exclusivo para esse veículo</strong>.
                            Quando os dois estiverem preenchidos, o vínculo com veículo é o que vale para esse cadastro.
                        </div>

                        <div class="os-section-title">Vínculo com veículo</div>
                        <div class="form-row">
                            <div class="col-lg-9 form-group">
                                <label class="form-label-sm">Veículo vinculado</label>
                                <input type="hidden" name="veiculo_id" id="veiculo_id" value="{{ $veiculoIdSelecionado }}">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="veiculo_display" class="form-control form-control-sm" readonly value="{{ $veiculoDisplaySelecionado }}" style="cursor:pointer;">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary" id="btnBuscarVeiculoPlano">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary d-none" id="btnLimparVeiculoPlano">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">Opcional. Se preenchido, este plano não dependerá apenas do grupo.</small>
                            </div>
                        </div>

                        <div class="os-section-title">Itens do plano</div>
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:.75rem;">
                            <small class="text-muted">Adicione itens e defina frequência por meses e KM.</small>
                            <div class="d-flex flex-wrap" style="gap:.5rem;">
                                <button type="button" class="btn btn-primary btn-sm" id="btnNovoItemManutencao">
                                    <i class="fas fa-plus mr-1"></i>Novo item
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" id="btnBuscarItemManutencao">
                                    <i class="fas fa-search mr-1"></i>Adicionar item
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive manutencao-table-wrap">
                            <table class="table table-striped table-bordered table-sm mb-0" id="tabelaItensPlano">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th style="width:160px;">Frequência (Meses)</th>
                                        <th style="width:160px;">Frequência (KM)</th>
                                        <th style="width:90px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($plano) && $plano->relationLoaded('itens'))
                                        @foreach ($plano->itens as $index => $planoItem)
                                            <tr data-item-row="1" data-item-id="{{ $planoItem->item_id }}">
                                                <td class="align-middle font-weight-bold">
                                                    <input type="hidden" name="itens[{{ $index }}][id]" value="{{ $planoItem->item_id }}">
                                                    <div>{{ $planoItem->item?->nome ?? ('Item #'.$planoItem->item_id) }}</div>
                                                    <small class="text-muted d-block">{{ $planoItem->item?->descricao ?? '' }}</small>
                                                </td>
                                                <td>
                                                    <input type="number" name="itens[{{ $index }}][meses]" class="form-control form-control-sm" value="{{ old('itens.'.$index.'.meses', $planoItem->frequencia_meses) }}">
                                                </td>
                                                <td>
                                                    <input type="number" name="itens[{{ $index }}][km]" class="form-control form-control-sm" value="{{ old('itens.'.$index.'.km', $planoItem->frequencia_km) }}">
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-primary js-remover-item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-4" style="gap:.75rem;">
                            <a href="{{ route('manutencao.planos.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">{{ $submitLabel }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4">
            <div class="card os-side-card">
                <div class="card-body">
                    <h5 class="text-bold mb-2">Como funciona</h5>
                    <p class="text-muted small mb-3">
                        O plano pode ser genérico por grupo de frota ou vinculado a um veículo específico.
                    </p>
                    <div class="os-tip">
                        <strong>1.</strong> Preencha nome e grupo.
                    </div>
                    <div class="os-tip">
                        <strong>2.</strong> Vincule um veículo só se o plano for exclusivo.
                    </div>
                    <div class="os-tip mb-0">
                        <strong>3.</strong> Adicione os itens preventivos com frequência.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-veiculo.busca-avancada-modal
    prefix="plano"
    title="Buscar veículo"
    trigger-id="btnBuscarVeiculoPlano"
    hidden-input-id="veiculo_id"
    display-input-id="veiculo_display"
    clear-btn-id="btnLimparVeiculoPlano"
/>

<x-manutencao.busca-item-modal
    modal-id="modalBuscarItemManutencao"
    novo-modal-id="modalNovoItemManutencao"
    title="Adicionar item de manutenção"
/>

@push('js')
<script>
(function () {
    const tbodyPlano = document.querySelector('#tabelaItensPlano tbody');
    const veiculoDisplay = document.getElementById('veiculo_display');
    const btnBuscarVeiculo = document.getElementById('btnBuscarVeiculoPlano');
    const btnNovo = document.getElementById('btnNovoItemManutencao');
    const btnBuscar = document.getElementById('btnBuscarItemManutencao');
    const modalBusca = document.getElementById('modalBuscarItemManutencao');
    const modalNovo = document.getElementById('modalNovoItemManutencao');
    const inputBusca = document.getElementById('buscaItemManutencao');
    const tbodyBusca = document.getElementById('resultadoBuscaItens');
    const perPageSelect = document.getElementById('itensManutencaoPerPage');
    const resumoItens = document.getElementById('itensManutencaoResumo');
    const paginacaoItens = document.getElementById('itensManutencaoPaginacao');
    const inputNovoNome = document.getElementById('novoItemNome');
    const inputNovoDescricao = document.getElementById('novoItemDescricao');
    const btnSalvarNovo = document.getElementById('btnSalvarNovoItemManutencao');
    const buscaUrl = @json(route('manutencao.planos.buscarItens'));
    const storeItemUrl = @json(route('manutencao.itens.store'));
    let timer = null;
    let paginaAtual = 1;

    if (tbodyPlano) {
        tbodyPlano.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-remover-item');
        if (!btn) return;
        const row = btn.closest('tr');
        if (row) row.remove();
        });
    }

    if (veiculoDisplay && btnBuscarVeiculo) {
        const abrirModalVeiculo = function () {
            if (window.$ && typeof window.$.fn?.modal === 'function') {
                window.$('#plano_modalBuscarVeiculo').modal('show');
            } else {
                btnBuscarVeiculo.click();
            }
        };

        veiculoDisplay.addEventListener('click', abrirModalVeiculo);
        veiculoDisplay.addEventListener('focus', abrirModalVeiculo);
        btnBuscarVeiculo.addEventListener('click', function (e) {
            e.preventDefault();
            abrirModalVeiculo();
        });
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
        });
    }

    function itemJaExiste(itemId) {
        if (!tbodyPlano) return false;
        return !!tbodyPlano.querySelector('tr[data-item-id="' + String(itemId) + '"]');
    }

    function abrirModalItemBusca() {
        if (modalBusca) {
            $(modalBusca).modal('show');
        }
        paginaAtual = 1;
        buscarItens();
    }

    function buscarItens() {
        if (!tbodyBusca || !inputBusca) return;
        const q = inputBusca.value.trim();
        const perPage = perPageSelect ? perPageSelect.value : '12';
        const url = buscaUrl + '?q=' + encodeURIComponent(q) + '&page=' + encodeURIComponent(paginaAtual) + '&per_page=' + encodeURIComponent(perPage);
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const items = data.items || [];
                const pagination = data.pagination || {};
                if (resumoItens) {
                    resumoItens.textContent = (pagination.total || items.length || 0) + ' item(ns)';
                }
                if (!items.length) {
                    tbodyBusca.innerHTML = '<div class="text-muted small text-center py-3">Nenhum item encontrado.</div>';
                    if (paginacaoItens) paginacaoItens.innerHTML = '';
                    return;
                }
                tbodyBusca.innerHTML = items.map(function (item) {
                    const desc = escapeHtml(item.descricao || '');
                    const initial = escapeHtml((item.nome || '-').trim().charAt(0).toUpperCase() || '-');
                    return `
                        <div class="manutencao-item-modal__card">
                            <div class="manutencao-item-modal__card-head">
                                <div class="d-flex align-items-start" style="gap:.65rem;">
                                    <div class="manutencao-item-modal__avatar">${initial}</div>
                                    <div>
                                        <h6 class="manutencao-item-modal__title">${escapeHtml(item.nome)}</h6>
                                        <div class="manutencao-item-modal__sub">${desc || 'Sem descrição'}</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary js-add-item"
                                    data-id="${item.id}"
                                    data-nome="${escapeHtml(item.nome)}"
                                    data-desc="${desc}">
                                    <i class="fas fa-plus mr-1"></i>Adicionar
                                </button>
                            </div>
                        </div>`;
                }).join('');
                if (paginacaoItens) {
                    const current = pagination.current_page || 1;
                    const last = pagination.last_page || 1;
                    let html = '';
                    if (last > 1) {
                        html += '<nav><ul class="pagination pagination-sm mb-0 manutencao-item-modal__pager">';
                        html += '<li class="page-item' + (current <= 1 ? ' disabled' : '') + '"><button type="button" class="page-link js-item-page" data-page="' + (current - 1) + '">Anterior</button></li>';
                        for (let p = Math.max(1, current - 2); p <= Math.min(last, current + 2); p++) {
                            html += '<li class="page-item' + (p === current ? ' active' : '') + '"><button type="button" class="page-link js-item-page" data-page="' + p + '">' + p + '</button></li>';
                        }
                        html += '<li class="page-item' + (current >= last ? ' disabled' : '') + '"><button type="button" class="page-link js-item-page" data-page="' + (current + 1) + '">Pr�xima</button></li>';
                        html += '</ul></nav>';
                    }
                    paginacaoItens.innerHTML = html;
                }
            })
            .catch(() => {
                tbodyBusca.innerHTML = '<div class="text-danger small">Falha ao buscar itens.</div>';
                if (paginacaoItens) paginacaoItens.innerHTML = '';
            });
    }

    function addItemRow(item) {
        if (!tbodyPlano) return;
        if (itemJaExiste(item.id)) {
            return;
        }
        const idx = tbodyPlano.querySelectorAll('tr[data-item-row="1"]').length;
        const row = document.createElement('tr');
        row.dataset.itemRow = '1';
        row.dataset.itemId = item.id;
        row.innerHTML = `
            <td class="align-middle font-weight-bold">
                <input type="hidden" name="itens[${idx}][id]" value="${item.id}">
                <div>${escapeHtml(item.nome)}</div>
                <small class="text-muted d-block">${escapeHtml(item.desc || '')}</small>
            </td>
            <td><input type="number" name="itens[${idx}][meses]" class="form-control form-control-sm" placeholder="Ex: 6"></td>
            <td><input type="number" name="itens[${idx}][km]" class="form-control form-control-sm" placeholder="Ex: 10000"></td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-primary js-remover-item">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbodyPlano.appendChild(row);
    }

    if (btnBuscar && modalBusca && inputBusca) {
        btnBuscar.addEventListener('click', abrirModalItemBusca);
        inputBusca.addEventListener('input', function () {
            paginaAtual = 1;
            clearTimeout(timer);
            timer = setTimeout(buscarItens, 250);
        });
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function () {
                paginaAtual = 1;
                buscarItens();
            });
        }
        tbodyBusca?.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-add-item');
            if (!btn) return;
            if (itemJaExiste(btn.dataset.id)) {
                $(modalBusca).modal('hide');
                return;
            }
            addItemRow({
                id: btn.dataset.id,
                nome: btn.dataset.nome,
                desc: btn.dataset.desc,
            });
            $(modalBusca).modal('hide');
        });
        paginacaoItens?.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-item-page');
            if (!btn || btn.closest('.disabled')) return;
            const nextPage = parseInt(btn.dataset.page, 10);
            if (!Number.isNaN(nextPage) && nextPage > 0) {
                paginaAtual = nextPage;
                buscarItens();
            }
        });
    }
    if (btnNovo && modalNovo) {
        btnNovo.addEventListener('click', function () {
            inputNovoNome.value = '';
            inputNovoDescricao.value = '';
            $(modalNovo).modal('show');
        });
    }

    if (btnSalvarNovo && modalNovo) {
        btnSalvarNovo.addEventListener('click', function () {
            const nome = inputNovoNome.value.trim();
            const descricao = inputNovoDescricao.value.trim();
            if (!nome) return;
            fetch(storeItemUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ nome, descricao })
            })
                .then(r => r.json())
                .then(data => {
                    if (data && data.id) {
                        addItemRow({ id: data.id, nome: data.nome || nome, desc: data.descricao || descricao });
                        $(modalNovo).modal('hide');
                    }
                });
        });
    }
})();
</script>
@endpush
