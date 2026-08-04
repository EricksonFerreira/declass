@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Planos de Manutenção', 'url' => route('manutencao.planos.index')], ['name' => 'Criar']]" />
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xxl-11">
            <div class="card manutencao-card shadow-sm border-0">
                <div class="card-header manutencao-card__header border-0">
                    <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap:.75rem;">
                        <div>
                            <div class="manutencao-card__eyebrow">Planos de manutenção preventiva</div>
                            <h4 class="mb-1">Criar plano</h4>
                            <p class="mb-0 text-muted">Organize o plano por veículo, grupo de frota e itens periódicos.</p>
                        </div>
                        <div class="manutencao-card__badge">
                            <i class="fas fa-clipboard-list mr-1"></i> Estrutura responsiva
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <form method="POST" action="{{ route('manutencao.planos.store') }}" class="p-3 p-md-4">
                        @csrf

                        <div class="row">
                            <div class="col-12 col-lg-7">
                                <div class="form-group form-group-opcional">
                                    <x-form-input type="text" class="form-control form-control-lg" id="nome" name="nome"
                                        label="Nome do Plano" value="{{ old('nome') }}" required />
                                </div>
                            </div>
                            <div class="col-12 col-lg-5">
                                <div class="form-group form-group-opcional">
                                    <x-form-select name="grupo_frota_id" label="Grupo de Frota" :options="$grupos->pluck('nome', 'id')"
                                        placeholder="Opcional" :selected="old('grupo_frota_id')" class="form-control form-control-lg" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="manutencao-card__tip mb-3">
                                    <strong>Vínculo do plano</strong>
                                    <span>Escolha um veículo específico ou mantenha o plano como referência por grupo de frota.</span>
                                </div>
                                <div class="form-group form-group-opcional">
                                    <label class="small text-muted font-weight-bold">Veículo vinculado</label>
                                    <div class="input-group input-group-lg">
                                        <input type="hidden" name="veiculo_id" id="veiculo_id" value="{{ old('veiculo_id') }}">
                                        <input type="text" id="veiculo_display" class="form-control" placeholder="Nenhum veículo selecionado" value="{{ old('veiculo_display') }}" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-primary" id="btnBuscarVeiculoPlano">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" id="btnLimparVeiculoPlano">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1">Opcional. Quando preenchido, o plano fica ligado a um único veículo.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group form-group-opcional mt-2">
                            <label for="descricao" class="small text-muted font-weight-bold">Descrição</label>
                            <textarea name="descricao" id="descricao" class="form-control" rows="4" placeholder="Explique a cobertura e a lógica do plano">{{ old('descricao') }}</textarea>
                        </div>

                        <div class="manutencao-section mt-4">
                            <div class="manutencao-section__header d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
                                <div>
                                    <div class="manutencao-section__eyebrow">Itens do plano</div>
                                    <h5 class="mb-0">Manutenção preventiva</h5>
                                </div>
                                <div class="d-flex flex-wrap" style="gap:.5rem;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnNovoItemManutencao">
                                        <i class="fas fa-plus mr-1"></i>Novo item
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnBuscarItemManutencao">
                                        <i class="fas fa-search mr-1"></i>Adicionar item
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive manutencao-table-wrap">
                                <table class="table table-striped table-bordered table-sm mb-0" id="tabelaItensPlano">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th style="width: 160px;">Frequência (Meses)</th>
                                            <th style="width: 160px;">Frequência (KM)</th>
                                            <th style="width: 90px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-end mt-4" style="gap:.75rem;">
                            <a href="{{ route('manutencao.planos.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">Salvar plano</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-veiculo.busca-avancada-modal
        prefix="plano"
        title="Buscar veículo para o plano"
        trigger-id="btnBuscarVeiculoPlano"
        hidden-input-id="veiculo_id"
        display-input-id="veiculo_display"
        clear-btn-id="btnLimparVeiculoPlano"
    />

    <div class="modal fade" id="modalBuscarItemManutencao" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar item de manutenção</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="buscaItemManutencao">Buscar item</label>
                        <input type="text" id="buscaItemManutencao" class="form-control" placeholder="Digite o nome do item">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Descrição</th>
                                    <th width="120"></th>
                                </tr>
                            </thead>
                            <tbody id="resultadoBuscaItens">
                                <tr><td colspan="3" class="text-muted">Digite para pesquisar.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNovoItemManutencao" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo item de manutenção</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="novoItemNome">Nome</label>
                        <input type="text" id="novoItemNome" class="form-control" maxlength="255">
                    </div>
                    <div class="form-group mb-0">
                        <label for="novoItemDescricao">Descrição</label>
                        <textarea id="novoItemDescricao" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarNovoItemManutencao">Salvar item</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .manutencao-page {
        padding-bottom: 8px;
    }
    .manutencao-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 16px 44px rgba(15, 23, 42, .08);
    }
    .manutencao-card__header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #f7fafc 0%, #eef4ff 55%, #f7fbff 100%);
        border-bottom: 1px solid #e8edf5;
    }
    .manutencao-card__eyebrow,
    .manutencao-section__eyebrow {
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #5b6b7f;
        margin-bottom: .25rem;
    }
    .manutencao-card__header h4,
    .manutencao-section__header h5 { font-weight: 800; color: #12263a; }
    .manutencao-card__badge,
    .manutencao-card__tip {
        border-radius: 14px;
        padding: .8rem .95rem;
        background: #fff;
        border: 1px solid #dfe7f1;
        box-shadow: 0 6px 20px rgba(18, 38, 58, .04);
    }
    .manutencao-card__badge { font-size: .9rem; font-weight: 700; color: #355070; white-space: nowrap; }
    .manutencao-card__tip {
        display: flex;
        flex-direction: column;
        gap: .25rem;
        color: #52657a;
        min-height: 100%;
    }
    .form-group-opcional { margin-bottom: 1rem; }
    .manutencao-card .form-control,
    .manutencao-card .form-select {
        border-radius: 12px;
        border-color: #d8e1ec;
        min-height: 42px;
    }
    .manutencao-card .input-group-lg > .form-control,
    .manutencao-card .input-group-lg > .input-group-append > .btn {
        min-height: 46px;
    }
    .manutencao-card .form-control:focus,
    .manutencao-card .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .15);
    }
    .manutencao-section {
        border: 1px solid #e5eaf2;
        border-radius: 18px;
        background: #fff;
        padding: 1rem;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
    }
    .manutencao-section__header {
        padding-bottom: .85rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #edf2f7;
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
    .manutencao-card .btn {
        border-radius: 12px;
        font-weight: 700;
    }
    .modal-content {
        border-radius: 18px;
        overflow: hidden;
    }
    .modal-header {
        background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 100%);
    }
    .modal-title {
        font-weight: 800;
        color: #0f172a;
    }
    .modal-body .form-control {
        border-radius: 12px;
        border-color: #dbe4ef;
    }
    .modal-body .form-control:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .15);
    }
    @media (max-width: 767.98px) {
        .manutencao-card__header,
        .manutencao-card .card-body {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .manutencao-section {
            padding: .85rem;
        }
        .manutencao-section__header {
            flex-direction: column;
            align-items: flex-start !important;
        }
        .manutencao-section__header .d-flex {
            width: 100%;
        }
        .manutencao-section__header .btn {
            flex: 1 1 100%;
        }
        .manutencao-card__badge {
            width: 100%;
            text-align: center;
        }
    }
</style>
@stop

@push('js')
<script>
(function () {
    const buscaUrl = @json(route('manutencao.planos.buscarItens'));
    const storeItemUrl = @json(route('manutencao.itens.store'));
    const modal = document.getElementById('modalBuscarItemManutencao');
    const btnAbrir = document.getElementById('btnBuscarItemManutencao');
    const btnNovo = document.getElementById('btnNovoItemManutencao');
    const modalNovo = document.getElementById('modalNovoItemManutencao');
    const btnSalvarNovo = document.getElementById('btnSalvarNovoItemManutencao');
    const inputNovoNome = document.getElementById('novoItemNome');
    const inputNovoDescricao = document.getElementById('novoItemDescricao');
    const inputBusca = document.getElementById('buscaItemManutencao');
    const tbodyBusca = document.getElementById('resultadoBuscaItens');
    const tbodyPlano = document.querySelector('#tabelaItensPlano tbody');

    if (!modal || !btnAbrir || !inputBusca || !tbodyBusca || !tbodyPlano || !modalNovo || !btnNovo || !btnSalvarNovo) return;

    let timer = null;
    let indice = 0;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
        });
    }

    function renderResultado(items) {
        if (!items.length) {
            tbodyBusca.innerHTML = '<tr><td colspan="3" class="text-muted">Nenhum item encontrado.</td></tr>';
            return;
        }

        const existentes = new Set(Array.from(tbodyPlano.querySelectorAll('tr[data-item-row="1"]')).map(row => String(row.dataset.itemId || '')));
        tbodyBusca.innerHTML = items.map(item => {
            const jaAdicionado = existentes.has(String(item.id));
            const btn = jaAdicionado
                ? '<button type="button" class="btn btn-sm btn-secondary" disabled>Adicionado</button>'
                : `<button type="button" class="btn btn-sm btn-primary js-adicionar-item" data-id="${item.id}" data-nome="${escapeHtml(item.nome)}" data-desc="${escapeHtml(item.descricao || '')}"><i class="fas fa-plus mr-1"></i>Adicionar</button>`;
            return `<tr>
                <td>${escapeHtml(item.nome)}</td>
                <td>${escapeHtml(item.descricao || '')}</td>
                <td class="text-right">${btn}</td>
            </tr>`;
        }).join('');
    }

    function buscarItens() {
        const q = inputBusca.value.trim();
        fetch(buscaUrl + '?q=' + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => renderResultado(data.items || []))
        .catch(() => {
            tbodyBusca.innerHTML = '<tr><td colspan="3" class="text-danger">Falha ao buscar itens.</td></tr>';
        });
    }

    function adicionarItem(item) {
        const row = document.createElement('tr');
        row.dataset.itemRow = '1';
        row.dataset.itemId = item.id;
        row.innerHTML = `
            <td class="align-middle font-weight-bold">
                <input type="hidden" name="itens[${indice}][id]" value="${item.id}">
                <div>${escapeHtml(item.nome)}</div>
                <small class="text-muted d-block">${escapeHtml(item.descricao || '')}</small>
            </td>
            <td><input type="number" name="itens[${indice}][meses]" class="form-control form-control-sm" placeholder="Ex: 6"></td>
            <td><input type="number" name="itens[${indice}][km]" class="form-control form-control-sm" placeholder="Ex: 10000"></td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger js-remover-item"><i class="fas fa-trash"></i></button>
            </td>
        `;
        indice++;
        tbodyPlano.appendChild(row);
    }

    function abrirNovoItem() {
        inputNovoNome.value = '';
        inputNovoDescricao.value = '';
        $(modalNovo).modal('show');
        setTimeout(() => inputNovoNome.focus(), 150);
    }

    function salvarNovoItem() {
        const nome = inputNovoNome.value.trim();
        if (!nome) return;

        const formData = new FormData();
        formData.append('nome', nome);
        formData.append('descricao', inputNovoDescricao.value.trim());
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content ?? '');

        fetch(storeItemUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
        })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw data;
            return data;
        })
        .then(data => {
            $(modalNovo).modal('hide');
            if (data.item) {
                adicionarItem(data.item);
                $(modal).modal('hide');
            }
        })
        .catch(() => {
            alert('Não foi possível salvar o item.');
        });
    }

    btnAbrir.addEventListener('click', function () {
        $(modal).modal('show');
        inputBusca.value = '';
        tbodyBusca.innerHTML = '<tr><td colspan="3" class="text-muted">Digite para pesquisar.</td></tr>';
        inputBusca.focus();
    });

    btnNovo.addEventListener('click', abrirNovoItem);
    btnSalvarNovo.addEventListener('click', salvarNovoItem);

    inputBusca.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(buscarItens, 250);
    });

    tbodyBusca.addEventListener('click', function (event) {
        const btn = event.target.closest('.js-adicionar-item');
        if (!btn) return;
        adicionarItem({
            id: btn.dataset.id,
            nome: btn.dataset.nome,
            descricao: btn.dataset.desc,
        });
        $(modal).modal('hide');
    });

    tbodyPlano.addEventListener('click', function (event) {
        const btn = event.target.closest('.js-remover-item');
        if (!btn) return;
        const row = btn.closest('tr');
        if (row) row.remove();
    });
})();
</script>
@endpush
