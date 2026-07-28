@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Planos de Manutenção', 'url' => route('manutencao.planos.index')], ['name' => 'Editar']]" />
@stop

@section('content')
    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-opcional shadow-sm border-0">
            <div class="card-header card-header-opcional border-0">
                <div>
                    <h4 class="mb-1">Editar Plano de Manutenção</h4>
                    <p class="mb-0 text-muted">Vincule o plano a um veículo ou use como modelo para um grupo de frota.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('manutencao.planos.update', $plano->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="opcional-section-title">Informações Básicas</div>
                    <div class="form-row">
                        <div class="col-lg-6">
                            <div class="form-group form-group-opcional">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome do Plano" value="{{ old('nome', $plano->nome) }}" required />
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group form-group-opcional">
                                <x-form-select name="grupo_frota_id" label="Grupo de Frota" :options="$grupos->pluck('nome', 'id')"
                                    placeholder="Opcional" :selected="old('grupo_frota_id', $plano->grupo_frota_id)" class="form-control form-control-sm" />
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-lg-12">
                            <div class="form-group form-group-opcional">
                                <label class="small text-muted font-weight-bold">Veículo vinculado</label>
                                <div class="input-group">
                                    <input type="hidden" name="veiculo_id" id="veiculo_id" value="{{ old('veiculo_id', $plano->veiculo_id) }}">
                                    <input type="text" id="veiculo_display" class="form-control form-control-sm" placeholder="Nenhum veículo selecionado" value="{{ old('veiculo_display', $plano->veiculo?->codigo ?? $plano->veiculo?->placa ?? '') }}" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnBuscarVeiculoPlano">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimparVeiculoPlano">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">Opcional. Quando preenchido, o plano fica ligado a um único veículo.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group form-group-opcional mt-3">
                        <label for="descricao" class="small text-muted font-weight-bold">Descrição</label>
                        <textarea name="descricao" id="descricao" class="form-control" rows="3">{{ old('descricao', $plano->descricao) }}</textarea>
                    </div>

                    <div class="opcional-section-title mt-4 d-flex justify-content-between align-items-center">
                        <span>Itens de Manutenção Preventiva</span>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnNovoItemManutencao">
                                <i class="fas fa-plus mr-1"></i>Novo item
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnBuscarItemManutencao">
                                <i class="fas fa-search mr-1"></i>Adicionar item
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm" id="tabelaItensPlano">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th width="160">Frequência (Meses)</th>
                                    <th width="160">Frequência (KM)</th>
                                    <th width="90"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plano->itens as $index => $planoItem)
                                    <tr data-item-row="1" data-item-id="{{ $planoItem->item_id }}">
                                        <td class="align-middle font-weight-bold">
                                            <input type="hidden" name="itens[{{ $index }}][id]" value="{{ $planoItem->item_id }}">
                                            <div>{{ $planoItem->item?->nome ?? ('Item #'.$planoItem->item_id) }}</div>
                                            <small class="text-muted d-block">{{ $planoItem->item?->descricao ?? '' }}</small>
                                        </td>
                                        <td>
                                            <input type="number" name="itens[{{ $index }}][meses]" class="form-control form-control-sm" value="{{ old('itens.'.$index.'.meses', $planoItem->frequencia_meses) }}" placeholder="Ex: 6">
                                        </td>
                                        <td>
                                            <input type="number" name="itens[{{ $index }}][km]" class="form-control form-control-sm" value="{{ old('itens.'.$index.'.km', $planoItem->frequencia_km) }}" placeholder="Ex: 10000">
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-outline-danger js-remover-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-opcional col-12 mt-4">
                        Salvar Plano
                    </button>
                </form>
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
    .card-opcional { border-radius: 14px; overflow: hidden; }
    .card-header-opcional { background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%); padding: 1.1rem 1.5rem; }
    .card-header-opcional h4 { color: #0f2740; font-weight: 700; }
    .opcional-section-title { font-size: .96rem; font-weight: 700; color: #21364f; letter-spacing: .02em; padding-bottom: 8px; border-bottom: 1px solid #e5eaf2; margin-bottom: 15px; }
    .form-group-opcional { margin-bottom: 1rem; }
    .card-opcional .form-control, .card-opcional .form-select { border-radius: 10px; border-color: #d8e1ec; min-height: 38px; }
    .card-opcional .form-control:focus, .card-opcional .form-select:focus { border-color: #4b80ff; box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18); }
    .btn-save-opcional { border-radius: 10px; font-weight: 600; padding-top: .58rem; padding-bottom: .58rem; }
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
    let indice = tbodyPlano.querySelectorAll('tr[data-item-row="1"]').length;

    function itensExistentes() {
        return Array.from(tbodyPlano.querySelectorAll('tr[data-item-row="1"]')).map(row => String(row.dataset.itemId || ''));
    }

    function renderResultado(items) {
        if (!items.length) {
            tbodyBusca.innerHTML = '<tr><td colspan="3" class="text-muted">Nenhum item encontrado.</td></tr>';
            return;
        }

        const existentes = new Set(itensExistentes());
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

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
        });
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
