<div class="modal fade" id="modalEditarReservaShow" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rs-dano-modal">
            <div class="modal-header rs-dano-modal__header">
                <div>
                    <div class="rs-dano-modal__eyebrow">Edicao rapida</div>
                    <h5 class="modal-title" id="editModalTitle">Editar Reserva #{{ $reserva->id }}</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="editModalBody">
                <div id="editFormLoading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                    <p class="text-muted">Carregando dados da reserva...</p>
                </div>

                <div id="editFormContent" class="d-none">
                    <div id="editFormView">
                        <div class="rs-dano-modal__grid mb-3">
                            <div class="rs-dano-modal__info">
                                <span><i class="fas fa-calendar-day mr-1"></i> Data/Hora retirada</span>
                                <input type="datetime-local" class="form-control form-control-sm" id="edit_data_inicio">
                            </div>
                            <div class="rs-dano-modal__info">
                                <span><i class="fas fa-calendar-check mr-1"></i> Data/Hora devolucao</span>
                                <input type="datetime-local" class="form-control form-control-sm" id="edit_data_fim">
                            </div>
                            <div class="rs-dano-modal__info">
                                <span><i class="fas fa-store mr-1"></i> Estacao retirada</span>
                                <select class="form-control form-control-sm" id="edit_estacao_retirada_id"></select>
                            </div>
                            <div class="rs-dano-modal__info">
                                <span><i class="fas fa-store-alt mr-1"></i> Estacao devolucao</span>
                                <select class="form-control form-control-sm" id="edit_estacao_devolucao_id"></select>
                            </div>
                        </div>
                        <div class="rs-dano-modal__info mb-3">
                            <span><i class="fas fa-sticky-note mr-1"></i> Observacoes</span>
                            <textarea class="form-control form-control-sm" id="edit_observacoes" rows="3" maxlength="1000"></textarea>
                        </div>
                        <div id="editOpcionaisSection" class="d-none">
                            <h6 class="font-weight-bold mb-3" style="font-size:.85rem;color:#0f172a"><i class="fas fa-boxes mr-1"></i> Opcionais</h6>
                            <div id="editOpcionaisContainer"></div>
                        </div>
                    </div>

                    <div id="editResultView" class="d-none">
                        <div class="alert alert-success text-center" id="editSuccessAlert">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <h5 class="mb-1">Reserva atualizada com sucesso!</h5>
                            <p class="mb-0 text-muted small" id="editSuccessMessage">As alteracoes foram aplicadas.</p>
                        </div>

                        <div id="editChangesContainer" class="mt-3">
                            <h6 class="font-weight-bold mb-3"><i class="fas fa-exchange-alt mr-1"></i> Alteracoes realizadas</h6>
                            <div id="editChangesList"></div>
                        </div>
                    </div>

                    <div id="editErrorView" class="d-none">
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-times-circle fa-2x mb-2"></i>
                            <h5 class="mb-1">Erro ao atualizar</h5>
                            <p class="mb-0" id="editErrorMessage"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="editModalFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> <span id="editBtnCloseText">Cancelar</span>
                </button>
                <button type="button" class="btn btn-primary" id="editBtnSave">
                    <i class="fas fa-save mr-1"></i> Salvar alteracoes
                </button>
                <button type="button" class="btn btn-success d-none" id="editBtnReload" onclick="location.reload()">
                    <i class="fas fa-redo mr-1"></i> Recarregar pagina
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
(function () {
    const RESERVA_ID = @json($reserva->id);
    const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const DADOS_URL = @json(route('reserva.dadosEdicaoNoShow', $reserva->id));
    const UPDATE_URL = @json(route('reserva.atualizarNoShow', $reserva->id));
    const MODAL_ID  = 'modalEditarReservaShow';
    const modal     = document.getElementById(MODAL_ID);
    const podeGerenciarOpcionais = @json(auth()->user()?->hasPermissao('reserva.gerenciar_opcionais') ?? false);

    if (!modal) return;

    let reservaData = null;
    let estacoesMap = {};
    let changesData = null;
    let initialFormData = null;

    const $modal = $(modal);

    function showView(viewId) {
        ['editFormView', 'editResultView', 'editErrorView'].forEach(id => {
            document.getElementById(id)?.classList.toggle('d-none', id !== viewId);
        });
    }

    function formatDateTimeLocal(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr.replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function populateEstacoes(selectId, estacoes, selectedId) {
        const sel = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '<option value="">Selecione...</option>';
        estacoes.forEach(function (e) {
            const opt = document.createElement('option');
            opt.value = e.id;
            opt.textContent = e.nome + (e.cidade ? ' - ' + e.cidade : '');
            if (String(e.id) === String(selectedId)) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    function loadEditData() {
        document.getElementById('editFormLoading').classList.remove('d-none');
        document.getElementById('editFormContent').classList.add('d-none');

        fetch(DADOS_URL, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            document.getElementById('editFormLoading').classList.add('d-none');
            document.getElementById('editFormContent').classList.remove('d-none');

            const r = data.reserva || {};
            reservaData = data;

            document.getElementById('edit_data_inicio').value = formatDateTimeLocal(r.data_inicio);
            document.getElementById('edit_data_fim').value    = formatDateTimeLocal(r.data_fim);
            document.getElementById('edit_observacoes').value = r.observacoes || '';

            estacoesMap = {};
            (data.estacoes || []).forEach(function (e) { estacoesMap[e.id] = e; });
            populateEstacoes('edit_estacao_retirada_id', data.estacoes || [], r.estacao_retirada_id);
            populateEstacoes('edit_estacao_devolucao_id', data.estacoes || [], r.estacao_devolucao_id);

            if (podeGerenciarOpcionais && data.opcionais_pivot && data.opcionais_pivot.length) {
                document.getElementById('editOpcionaisSection').classList.remove('d-none');
                renderOpcionais(data.opcionais_pivot);
            }

            initialFormData = collectFormData();

            showView('editFormView');
            document.getElementById('editBtnCloseText').textContent = 'Cancelar';
            document.getElementById('editBtnSave').classList.remove('d-none');
            document.getElementById('editBtnReload').classList.add('d-none');
        })
        .catch(function () {
            document.getElementById('editFormLoading').innerHTML = '<p class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Erro ao carregar dados. Tente novamente.</p>';
        });
    }

    function renderOpcionais(opcionais) {
        const container = document.getElementById('editOpcionaisContainer');
        container.innerHTML = '';
        opcionais.forEach(function (opc) {
            const div = document.createElement('div');
            div.className = 'form-row align-items-center mb-2 p-2 border rounded';
            div.style.background = '#f8fafc';
            div.innerHTML = `
                <div class="col">
                    <input type="hidden" class="opc-id" value="${opc.id}">
                    <strong class="small">${opc.nome || 'Opcional #' + opc.id}</strong>
                </div>
                <div class="col-3">
                    <label class="small mb-0">Qtd</label>
                    <input type="number" class="form-control form-control-sm opc-qtd" value="${opc.quantidade || 1}" min="1">
                </div>
                <div class="col-3">
                    <label class="small mb-0">Preco total</label>
                    <input type="number" step="0.01" class="form-control form-control-sm opc-preco" value="${opc.preco_total || 0}" min="0">
                </div>
            `;
            container.appendChild(div);
        });
    }

    function collectFormData() {
        return {
            data_inicio: document.getElementById('edit_data_inicio').value,
            data_fim: document.getElementById('edit_data_fim').value,
            estacao_retirada_id: document.getElementById('edit_estacao_retirada_id').value,
            estacao_devolucao_id: document.getElementById('edit_estacao_devolucao_id').value,
            observacoes: document.getElementById('edit_observacoes').value,
        };
    }

    function collectOpcionais() {
        if (!podeGerenciarOpcionais) return null;
        const items = document.querySelectorAll('#editOpcionaisContainer .opc-id');
        if (!items.length) return null;
        return Array.from(items).map(function (el) {
            const row = el.closest('.form-row');
            return {
                id: parseInt(el.value),
                quantidade: parseInt(row.querySelector('.opc-qtd').value) || 1,
                preco_total: parseFloat(row.querySelector('.opc-preco').value) || 0,
            };
        });
    }

    function renderChanges(before, after) {
        const container = document.getElementById('editChangesList');
        container.innerHTML = '';

        const fieldsMap = {
            data_inicio: 'Data retirada',
            data_fim: 'Data devolucao',
            estacao_retirada_id: 'Estacao retirada',
            estacao_devolucao_id: 'Estacao devolucao',
            observacoes: 'Observacoes',
        };

        let hasChanges = false;

        Object.keys(fieldsMap).forEach(function (field) {
            const label = fieldsMap[field];
            const vBefore = before && before[field] !== undefined ? before[field] : null;
            const vAfter  = after && after[field] !== undefined ? after[field] : null;
            const sBefore = formatFieldValue(field, vBefore);
            const sAfter  = formatFieldValue(field, vAfter);
            const changed = String(vBefore ?? '') !== String(vAfter ?? '');

            if (changed) hasChanges = true;

            const item = document.createElement('div');
            item.className = 'rs-dano-item' + (changed ? ' rs-dano-item--changed' : '');
            item.style.cursor = 'default';
            item.innerHTML = '<div style="flex:1">'
                + '<strong>' + label + '</strong>'
                + '<div class="rs-diff-values" style="display:flex;gap:16px;margin-top:4px">'
                + '<span class="rs-diff-before' + (changed ? '' : ' text-muted') + '"><span class="small text-muted">Antes:</span> ' + sBefore + '</span>'
                + '<span class="rs-diff-after' + (changed ? ' font-weight-bold' : ' text-muted') + '"><span class="small text-muted">Depois:</span> ' + sAfter + '</span>'
                + '</div></div>';
            container.appendChild(item);
        });

        if (!hasChanges) {
            container.innerHTML = '<div class="rs-empty"><i class="fas fa-minus-circle"></i><span>Nenhuma alteracao detectada.</span></div>';
        }
    }

    function formatFieldValue(field, value) {
        if (value === null || value === undefined || value === '') return '—';
        if (field === 'estacao_retirada_id' || field === 'estacao_devolucao_id') {
            const est = estacoesMap[value];
            return est ? (est.nome + (est.cidade ? ' - ' + est.cidade : '')) : 'Estacao #' + value;
        }
        if (field === 'data_inicio' || field === 'data_fim') {
            if (value.includes('T')) value = value.replace('T', ' ');
            try {
                const d = new Date(value.replace(' ', 'T'));
                if (!isNaN(d.getTime())) return d.toLocaleString('pt-BR');
            } catch(e) {}
            return value;
        }
        return value;
    }

    function submitUpdate() {
        const btn = document.getElementById('editBtnSave');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';

        const payload = collectFormData();
        const opcionais = collectOpcionais();
        if (opcionais) payload.opcionais = opcionais;

        fetch(UPDATE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        })
        .then(function (r) { return r.json().then(function (d) { return { status: r.status, data: d }; }); })
        .then(function (res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save mr-1"></i> Salvar alteracoes';

            if (res.status === 200 && res.data.success) {
                changesData = {
                    antes: initialFormData,
                    depois: payload,
                };
                document.getElementById('editSuccessAlert').classList.remove('alert-danger');
                document.getElementById('editSuccessAlert').classList.add('alert-success');
                document.getElementById('editSuccessMessage').textContent = res.data.message || 'As alteracoes foram aplicadas.';

                renderChanges(initialFormData, payload);
                showView('editResultView');
                document.getElementById('editBtnCloseText').textContent = 'Fechar';
                document.getElementById('editBtnSave').classList.add('d-none');
                document.getElementById('editBtnReload').classList.remove('d-none');

                setTimeout(function () { location.reload(); }, 3000);
            } else {
                document.getElementById('editErrorMessage').textContent = res.data.error || 'Falha ao atualizar a reserva.';
                showView('editErrorView');
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save mr-1"></i> Salvar alteracoes';
            document.getElementById('editErrorMessage').textContent = 'Erro de conexao. Tente novamente.';
            showView('editErrorView');
        });
    }

    $modal.on('show.bs.modal', function () {
        showView('editFormView');
        loadEditData();
    });

    $modal.on('hidden.bs.modal', function () {
        if (changesData) {
            location.reload();
        }
    });

    document.getElementById('editBtnSave')?.addEventListener('click', submitUpdate);

})();
</script>
@endpush
