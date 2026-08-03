/* global $, bootstrap */
(function () {
    if (window.VeiculoBuscaModal) return;

    function qs(id) {
        return document.getElementById(id);
    }

    function safeText(value) {
        return (value ?? '').toString();
    }

    function escapeAttr(value) {
        return safeText(value).replace(/"/g, '&quot;');
    }

    function normalize(value) {
        return safeText(value).trim();
    }

    function setHidden(hiddenInputId, value) {
        if (!hiddenInputId) return;
        var el = qs(hiddenInputId);
        if (!el) return;
        el.value = value;
        try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (e) { /* ignore */ }
    }

    function setDisplay(displayInputId, value) {
        if (!displayInputId) return;
        var el = qs(displayInputId);
        if (!el) return;
        el.value = value;
    }

    function setSelectValue(selectId, value, label) {
        if (!selectId) return;
        var select = qs(selectId);
        if (!select) return;
        var v = safeText(value);

        var esc = v;
        if (window.CSS && typeof window.CSS.escape === 'function') {
            esc = window.CSS.escape(v);
        } else {
            esc = v.replace(/["\\]/g, '\\$&');
        }

        if (v && !select.querySelector('option[value="' + esc + '"]')) {
            var opt = document.createElement('option');
            opt.value = v;
            opt.textContent = label || v;
            select.appendChild(opt);
        }

        select.value = v;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function openModal(modalId) {
        if (!modalId) return;
        if (window.$ && typeof window.$.fn?.modal === 'function') {
            window.$('#' + modalId).modal('show');
            return;
        }

        var el = qs(modalId);
        if (!el) return;
        // Fallback básico sem bootstrap JS.
        el.classList.add('show');
        el.style.display = 'block';
        el.removeAttribute('aria-hidden');
    }

    function closeModal(modalId) {
        if (!modalId) return;
        if (window.$ && typeof window.$.fn?.modal === 'function') {
            window.$('#' + modalId).modal('hide');
            return;
        }

        var el = qs(modalId);
        if (!el) return;
        el.classList.remove('show');
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
    }

    function buildLabel(vehicle) {
        var nome = safeText(vehicle.nome || '');
        var placa = safeText(vehicle.placa || '');
        var id = safeText(vehicle.id || '');
        if (placa) return nome + ' (' + placa + ')';
        if (id) return nome + ' (' + id + ')';
        return nome || '-';
    }

    window.VeiculoBuscaModal = {
        init: function (config) {
            var prefix = config.prefix || 'veiculo';
            var buscarUrl = config.buscarUrl || '';
            var modalId = config.modalId || (prefix + '_modalBuscarVeiculo');

            var triggerId = config.triggerId || null;
            var clearBtnId = config.clearBtnId || null;
            var hiddenInputId = config.hiddenInputId || null;
            var displayInputId = config.displayInputId || null;
            var targetSelectId = config.targetSelectId || null;
            var autoFillPlacaSelector = config.autoFillPlacaSelector || null;
            var autoFillKmsSelector = config.autoFillKmsSelector || null;
            var autoFillNivelSelector = config.autoFillNivelSelector || null;
            var clearBtnMode = config.clearBtnMode || 'auto'; // auto|always|never

            var ids = {
                placa: prefix + '_fv_placa',
                nome: prefix + '_fv_nome',
                chassi: prefix + '_fv_chassi',
                cor: prefix + '_fv_cor',
                modelo: prefix + '_fv_modelo',
                marca_id: prefix + '_fv_marca_id',
                grupo_frota_id: prefix + '_fv_grupo_frota_id',
                tipo_frota_id: prefix + '_fv_tipo_frota_id',
                status_veiculo_id: prefix + '_fv_status_veiculo_id',
                ativo: prefix + '_fv_ativo',
                ano_de: prefix + '_fv_ano_de',
                ano_ate: prefix + '_fv_ano_ate',
                kms_ate: prefix + '_fv_kms_ate',
                btnBuscar: prefix + '_btnAplicarFiltroVeiculo',
                btnLimpar: prefix + '_btnLimparFiltroVeiculo',
                loading: prefix + '_modalVeiculoLoading',
                resultados: prefix + '_modalVeiculoResultados',
            };

            function coletarFiltros() {
                var p = new URLSearchParams();
                function add(id, key) {
                    var el = qs(id);
                    var v = el ? normalize(el.value) : '';
                    if (v) p.set(key, v);
                }

                add(ids.placa, 'placa');
                add(ids.nome, 'nome');
                add(ids.chassi, 'chassi');
                add(ids.cor, 'cor');
                add(ids.modelo, 'modelo');
                add(ids.marca_id, 'marca_id');
                add(ids.grupo_frota_id, 'grupo_frota_id');
                add(ids.tipo_frota_id, 'tipo_frota_id');
                add(ids.status_veiculo_id, 'status_veiculo_id');
                add(ids.ativo, 'ativo');
                add(ids.ano_de, 'ano_de');
                add(ids.ano_ate, 'ano_ate');
                add(ids.kms_ate, 'kms_ate');
                return p.toString();
            }

            function limparFiltros() {
                [
                    ids.placa, ids.nome, ids.chassi, ids.cor, ids.modelo,
                    ids.ano_de, ids.ano_ate, ids.kms_ate,
                ].forEach(function (id) {
                    var el = qs(id);
                    if (el) el.value = '';
                });

                [
                    ids.marca_id, ids.grupo_frota_id, ids.tipo_frota_id,
                    ids.status_veiculo_id, ids.ativo,
                ].forEach(function (id) {
                    var el = qs(id);
                    if (el) el.selectedIndex = 0;
                });

                var resultados = qs(ids.resultados);
                if (resultados) resultados.innerHTML = '';
            }

            function executarBusca() {
                if (!buscarUrl) return;
                var loading = qs(ids.loading);
                var resultados = qs(ids.resultados);
                if (loading) loading.classList.remove('d-none');
                if (resultados) resultados.innerHTML = '';

                var url = buscarUrl + (buscarUrl.indexOf('?') === -1 ? '?' : '&') + coletarFiltros();

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (loading) loading.classList.add('d-none');
                        if (!resultados) return;

                        if (!data.veiculos || data.veiculos.length === 0) {
                            resultados.innerHTML = '<p class="text-muted text-center py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Nenhum veiculo encontrado com os filtros informados.</p>';
                            return;
                        }

                        var badge = '<span class="badge badge-secondary mb-2">' + data.veiculos.length + ' resultado(s)</span>';
                        var html = badge + '<div class="table-responsive veiculo-busca-modal__table-scroll">';
                        html += '<table class="table table-sm table-hover table-bordered mb-0 veiculo-busca-modal__table" style="font-size:.82rem">';
                        html += '<thead class="thead-light veiculo-busca-modal__thead"><tr>' +
                            '<th>Placa</th><th>Nome</th><th>Marca</th><th>Modelo</th>' +
                            '<th>Grupo</th><th>Tipo</th><th>Status</th>' +
                            '<th>Ano Fab.</th><th>Ano Mod.</th><th>Cor</th>' +
                            '<th>KMs</th><th>Ativo</th><th>Estacao</th>' +
                            '<th class="text-center">Acao</th>' +
                            '</tr></thead><tbody>';

                        data.veiculos.forEach(function (v) {
                            var label = buildLabel(v);
                            html += '<tr>' +
                                '<td><strong>' + (escapeAttr(v.placa) || '—') + '</strong></td>' +
                                '<td>' + escapeAttr(v.nome) + '</td>' +
                                '<td>' + (escapeAttr(v.marca) || '—') + '</td>' +
                                '<td>' + (escapeAttr(v.modelo) || '—') + '</td>' +
                                '<td>' + (escapeAttr(v.grupo) || '—') + '</td>' +
                                '<td>' + (escapeAttr(v.tipo) || '—') + '</td>' +
                                '<td><span class="badge badge-info">' + (escapeAttr(v.status) || '—') + '</span></td>' +
                                '<td>' + (escapeAttr(v.ano_fab) || '—') + '</td>' +
                                '<td>' + (escapeAttr(v.ano_mod) || '—') + '</td>' +
                                '<td>' + (escapeAttr(v.cor) || '—') + '</td>' +
                                '<td>' + (escapeAttr(v.kms) || '—') + '</td>' +
                                '<td>' + (escapeAttr(v.ativo) || '—') + '</td>' +
                                '<td>' + (escapeAttr(v.estacao) || '—') + '</td>' +
                                '<td class="text-center">' +
                                '<button type="button" class="btn btn-sm btn-success js-selecionar-veiculo" ' +
                                'data-id="' + escapeAttr(v.id) + '" data-label="' + escapeAttr(label) + '" data-placa="' + escapeAttr(v.placa || '') + '" data-kms="' + escapeAttr(v.kms || '') + '" data-nivel="' + escapeAttr(v.nivel_combustivel || '') + '">' +
                                '<i class="fas fa-check"></i>' +
                                '</button>' +
                                '</td>' +
                                '</tr>';
                        });

                        html += '</tbody></table></div>';
                        resultados.innerHTML = html;

                        resultados.querySelectorAll('.js-selecionar-veiculo').forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                var id = safeText(this.dataset.id || '');
                                var label = safeText(this.dataset.label || '');
                                var placa = safeText(this.dataset.placa || '');
                                var kms = safeText(this.dataset.kms || '');
                                var nivel = safeText(this.dataset.nivel || '');

                                if (hiddenInputId || displayInputId) {
                                    setHidden(hiddenInputId, id);
                                    setDisplay(displayInputId, label);
                                }

                                if (targetSelectId) {
                                    setSelectValue(targetSelectId, id, label);
                                }

                                if (autoFillPlacaSelector && placa) {
                                    var placaInput = document.querySelector(autoFillPlacaSelector);
                                    if (placaInput && !normalize(placaInput.value)) {
                                        placaInput.value = placa;
                                    }
                                }

                                if (autoFillKmsSelector && kms) {
                                    var kmsInput = document.querySelector(autoFillKmsSelector);
                                    if (kmsInput && !normalize(kmsInput.value)) {
                                        kmsInput.value = kms;
                                    }
                                }

                                if (autoFillNivelSelector && nivel) {
                                    var nivelInput = document.querySelector(autoFillNivelSelector);
                                    if (nivelInput && !normalize(nivelInput.value)) {
                                        nivelInput.value = nivel;
                                    }
                                }

                                try {
                                    var ev = new CustomEvent('veiculo:selecionado', { detail: { id: id, label: label, placa: placa, kms: kms, nivel: nivel } });
                                    document.dispatchEvent(ev);
                                } catch (e) {
                                    // ignore
                                }

                                updateClearButton();
                                closeModal(modalId);
                            });
                        });
                    })
                    .catch(function () {
                        if (loading) loading.classList.add('d-none');
                        var resultados = qs(ids.resultados);
                        if (resultados) {
                            resultados.innerHTML = '<p class="text-danger text-center py-3"><i class="fas fa-exclamation-triangle mr-1"></i>Erro ao buscar veiculos.</p>';
                        }
                    });
            }

            if (triggerId) {
                var trigger = qs(triggerId);
                if (trigger) {
                    trigger.addEventListener('click', function () {
                        openModal(modalId);
                        setTimeout(function () {
                            var el = qs(ids.placa);
                            if (el) el.focus();
                        }, 350);
                        executarBusca();
                    });
                }
            }

            function updateClearButton() {
                if (!clearBtnId) return;
                var clear = qs(clearBtnId);
                if (!clear) return;

                if (clearBtnMode === 'always') {
                    clear.classList.remove('d-none');
                    return;
                }
                if (clearBtnMode === 'never') {
                    clear.classList.add('d-none');
                    return;
                }

                var has = false;
                if (hiddenInputId) {
                    var h = qs(hiddenInputId);
                    has = !!(h && normalize(h.value));
                } else if (targetSelectId) {
                    var s = qs(targetSelectId);
                    has = !!(s && normalize(s.value));
                }
                clear.classList.toggle('d-none', !has);
            }

            if (clearBtnId) {
                var clear = qs(clearBtnId);
                if (clear) {
                    clear.addEventListener('click', function () {
                        if (hiddenInputId) setHidden(hiddenInputId, '');
                        if (displayInputId) setDisplay(displayInputId, '');
                        if (targetSelectId) setSelectValue(targetSelectId, '', '');
                        updateClearButton();
                    });
                }
            }

            var btnBuscar = qs(ids.btnBuscar);
            if (btnBuscar) btnBuscar.addEventListener('click', executarBusca);

            var btnLimpar = qs(ids.btnLimpar);
            if (btnLimpar) btnLimpar.addEventListener('click', limparFiltros);

            [
                ids.placa, ids.nome, ids.chassi, ids.cor, ids.modelo,
                ids.ano_de, ids.ano_ate, ids.kms_ate,
            ].forEach(function (id) {
                var el = qs(id);
                if (!el) return;
                el.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        executarBusca();
                    }
                });
            });

            updateClearButton();
        },
    };
}());
