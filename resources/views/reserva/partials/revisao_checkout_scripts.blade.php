<script>
(function () {
    const modalCobrar = document.getElementById('modalCobrarDano');
    const modalDetalhes = document.getElementById('detalhesDanoModal');
    const form = document.querySelector('.js-form-cobrar-dano');
    const resumoAvariasTotal = document.querySelector('.js-resumo-avarias-total');
    const resumoTotalFinal = document.querySelector('.js-resumo-total-final');
    const resumoStatus = document.querySelector('.js-resumo-danos-status');

    const hasCobrarModal = !!(modalCobrar && form);
    const danoIdInput = hasCobrarModal ? modalCobrar.querySelector('.js-dano-id') : null;
    const tituloInput = hasCobrarModal ? modalCobrar.querySelector('.js-dano-titulo') : null;
    const modoCobrancaInput = hasCobrarModal ? modalCobrar.querySelector('.js-modo-cobranca') : null;
    const descricaoInput = hasCobrarModal ? modalCobrar.querySelector('.js-dano-descricao') : null;
    const justificativaInput = hasCobrarModal ? modalCobrar.querySelector('.js-dano-justificativa') : null;
    const valorDisplayInput = hasCobrarModal ? modalCobrar.querySelector('.js-dano-valor-input') : null;
    const valorHiddenInput = hasCobrarModal ? modalCobrar.querySelector('input[name="valor"]') : null;
    const usarCalcaoHidden = hasCobrarModal ? modalCobrar.querySelector('.js-usar-calcao-hidden') : null;
    const valorWrap = hasCobrarModal ? modalCobrar.querySelector('.js-dano-valor-wrap') : null;
    const precoPreview = hasCobrarModal ? modalCobrar.querySelector('.js-dano-preco-preview') : null;
    const descricaoPreview = hasCobrarModal ? modalCobrar.querySelector('.js-dano-descricao-preview') : null;
    const estimativaPreview = hasCobrarModal ? modalCobrar.querySelector('.js-dano-estimativa-preview') : null;
    const localPreview = hasCobrarModal ? modalCobrar.querySelector('.js-dano-local-preview') : null;
    const nomePreview = hasCobrarModal ? modalCobrar.querySelector('.js-dano-nome-preview') : null;
    const imagensWrap = hasCobrarModal ? modalCobrar.querySelector('.js-dano-imagens') : null;
    const imagensEmpty = hasCobrarModal ? modalCobrar.querySelector('.js-dano-imagens-empty') : null;
    const resumoCalcao = hasCobrarModal ? modalCobrar.querySelector('.js-resumo-calcao') : null;
    const resumoTotalCobrancas = hasCobrarModal ? modalCobrar.querySelector('.js-resumo-total-cobrancas') : null;
    const resumoUsado = hasCobrarModal ? modalCobrar.querySelector('.js-resumo-usado') : null;
    const resumoAindaCobrar = hasCobrarModal ? modalCobrar.querySelector('.js-resumo-ainda-cobrar') : null;
    const resumoSaldo = hasCobrarModal ? modalCobrar.querySelector('.js-resumo-saldo') : null;
    const confirmarBtn = hasCobrarModal ? modalCobrar.querySelector('.js-btn-confirmar-cobranca') : null;
    const decisaoInputs = hasCobrarModal ? Array.from(modalCobrar.querySelectorAll('input[name="decisao_cobranca"]')) : [];

    let currentDano = null;

    function formatMoney(value) {
        return Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function parseMoney(text) {
        return Number((text || '0').replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.')) || 0;
    }

    function updateResumoTotals() {
        const rows = document.querySelectorAll('.js-dano-item');
        let total = 0;
        let cobrar = 0;
        let naoCobrar = 0;
        let pendentes = 0;

        rows.forEach(function (row) {
            const state = row.getAttribute('data-state') || 'pending';
            const value = Number(row.getAttribute('data-valor') || 0);

            if (state === 'cobrar') {
                cobrar += 1;
                total += value;
            } else if (state === 'free') {
                naoCobrar += 1;
            } else {
                pendentes += 1;
            }
        });

        if (resumoAvariasTotal) resumoAvariasTotal.textContent = formatMoney(total);
        if (resumoTotalFinal) resumoTotalFinal.textContent = formatMoney(total);
        if (resumoStatus) resumoStatus.textContent = cobrar + ' dano(s) serao cobrados, ' + naoCobrar + ' nao serao cobrados e ' + pendentes + ' ainda estao pendentes.';
    }

    function updateModalResumo() {
        const valorCaucao = parseMoney(resumoCalcao ? resumoCalcao.textContent : '0');
        const mostrarValor = !modoCobrancaInput || modoCobrancaInput.value !== 'nao_cobrar';
        const totalCobrancas = mostrarValor ? Number(valorDisplayInput?.value || currentDano?.valor || 0) : 0;
        const usarCalcao = (usarCalcaoHidden?.value || '1') === '1';
        const valorUsadoCaucao = usarCalcao ? Math.min(totalCobrancas, valorCaucao) : 0;
        const valorAindaCobrar = Math.max(0, totalCobrancas - valorUsadoCaucao);
        const valorDevolver = Math.max(0, valorCaucao - valorUsadoCaucao);

        if (resumoCalcao) resumoCalcao.textContent = formatMoney(valorCaucao);
        if (resumoTotalCobrancas) resumoTotalCobrancas.textContent = formatMoney(totalCobrancas);
        if (resumoUsado) resumoUsado.textContent = formatMoney(valorUsadoCaucao);
        if (resumoAindaCobrar) resumoAindaCobrar.textContent = formatMoney(valorAindaCobrar);
        if (resumoSaldo) resumoSaldo.textContent = formatMoney(valorDevolver);
        if (precoPreview) precoPreview.textContent = formatMoney(totalCobrancas);
        if (estimativaPreview) estimativaPreview.textContent = formatMoney(totalCobrancas);
        if (valorHiddenInput) valorHiddenInput.value = totalCobrancas.toFixed(2);
        if (currentDano) currentDano.valor = totalCobrancas.toFixed(2);
        if (valorWrap) valorWrap.style.display = mostrarValor ? '' : 'none';
        if (valorDisplayInput) valorDisplayInput.required = mostrarValor;
    }

    function openCobrarModal(btn) {
        const valorPadrao = btn.getAttribute('data-dano-valor-aprovado') || btn.getAttribute('data-dano-valor') || '0.00';
        currentDano = {
            id: btn.getAttribute('data-dano-id') || '',
            nome: btn.getAttribute('data-dano-nome') || 'Dano',
            local: btn.getAttribute('data-dano-local') || '-',
            observacao: btn.getAttribute('data-dano-observacao') || '',
            valor: valorPadrao,
            imagens: []
        };

        try { currentDano.imagens = JSON.parse(btn.getAttribute('data-dano-imagens') || '[]'); } catch (e) { currentDano.imagens = []; }

        const usarCalcao = btn.getAttribute('data-dano-usar-calcao') !== '0';
        const mode = btn.getAttribute('data-dano-mode') || 'charge';

        if (danoIdInput) danoIdInput.value = currentDano.id;
        if (tituloInput) tituloInput.value = 'Cobrança de dano - ' + currentDano.nome;
        if (modoCobrancaInput) modoCobrancaInput.value = mode === 'free' ? 'nao_cobrar' : 'cobrar';
        decisaoInputs.forEach(function (input) { input.checked = input.value === 'cobrar'; });
        if (descricaoInput) descricaoInput.value = 'Cobrança referente ao dano "' + currentDano.nome + '" localizado em ' + currentDano.local + (currentDano.observacao ? '. Observações: ' + currentDano.observacao : '.');
        if (justificativaInput) justificativaInput.value = '';
        if (valorDisplayInput) valorDisplayInput.value = currentDano.valor;
        if (valorHiddenInput) valorHiddenInput.value = currentDano.valor;
        if (usarCalcaoHidden) usarCalcaoHidden.value = usarCalcao ? '1' : '0';
        if (localPreview) localPreview.textContent = currentDano.local;
        if (nomePreview) nomePreview.textContent = currentDano.nome;
        if (descricaoPreview) descricaoPreview.textContent = currentDano.observacao || 'Sem observações.';

        if (imagensWrap) {
            imagensWrap.innerHTML = '';
            if (currentDano.imagens.length) {
                currentDano.imagens.forEach(function (url) {
                    const link = document.createElement('a');
                    link.href = url;
                    link.target = '_blank';
                    link.innerHTML = '<img src="' + url + '" class="img-thumb" alt="Imagem do dano">';
                    imagensWrap.appendChild(link);
                });
                if (imagensEmpty) imagensEmpty.style.display = 'none';
            } else if (imagensEmpty) {
                imagensEmpty.style.display = '';
            }
        }

        updateModalResumo();
        if (window.$) window.$(modalCobrar).modal('show');
    }

    function openDetailsModal(btn) {
        const nome = btn.getAttribute('data-dano-nome') || 'Dano';
        const local = btn.getAttribute('data-dano-local') || '-';
        const observacao = btn.getAttribute('data-dano-observacao') || '';
        const valor = btn.getAttribute('data-dano-valor') || '0.00';
        let imagens = [];

        try {
            imagens = JSON.parse(btn.getAttribute('data-dano-imagens') || '[]');
        } catch (e) {
            imagens = [];
        }

        const row = btn.closest('.js-dano-item');
        const decisao = row ? (row.querySelector('td:nth-child(2) .mini-chip')?.textContent || '').trim() : '-';
        const reparo = row ? (row.querySelector('td:nth-child(4) .mini-chip')?.textContent || '').trim() : '-';

        const detalheNome = modalDetalhes ? modalDetalhes.querySelector('.js-detalhe-nome') : null;
        const detalheLocal = modalDetalhes ? modalDetalhes.querySelector('.js-detalhe-local') : null;
        const detalheDescricao = modalDetalhes ? modalDetalhes.querySelector('.js-detalhe-descricao') : null;
        const detalheValor = modalDetalhes ? modalDetalhes.querySelector('.js-detalhe-valor') : null;
        const detalheDecisao = modalDetalhes ? modalDetalhes.querySelector('.js-detalhe-decisao') : null;
        const detalheReparo = modalDetalhes ? modalDetalhes.querySelector('.js-detalhe-reparo') : null;
        const detalheSubtitulo = modalDetalhes ? modalDetalhes.querySelector('.js-detalhe-subtitulo') : null;
        const detalheImagens = modalDetalhes ? modalDetalhes.querySelector('.js-detalhe-imagens') : null;
        const detalheSemImagens = modalDetalhes ? modalDetalhes.querySelector('.js-detalhe-sem-imagens') : null;
        const detalheIrCobranca = modalDetalhes ? modalDetalhes.querySelector('.js-detalhe-ir-cobranca') : null;

        if (detalheNome) detalheNome.textContent = nome;
        if (detalheLocal) detalheLocal.textContent = local;
        if (detalheDescricao) detalheDescricao.textContent = observacao || 'Sem observações.';
        if (detalheValor) detalheValor.textContent = formatMoney(valor);
        if (detalheDecisao) detalheDecisao.textContent = decisao || '-';
        if (detalheReparo) detalheReparo.textContent = reparo || '-';
        if (detalheSubtitulo) detalheSubtitulo.textContent = local + ' • ' + nome;

        if (detalheImagens) {
            detalheImagens.innerHTML = '';
            if (imagens.length) {
                imagens.forEach(function (url) {
                    const link = document.createElement('a');
                    link.href = url;
                    link.target = '_blank';
                    link.innerHTML = '<img src="' + url + '" class="img-thumb" alt="Imagem do dano">';
                    detalheImagens.appendChild(link);
                });
                if (detalheSemImagens) detalheSemImagens.style.display = 'none';
            } else if (detalheSemImagens) {
                detalheSemImagens.style.display = '';
            }
        }

        if (detalheIrCobranca) {
            detalheIrCobranca.onclick = function () {
                if (window.$) window.$(modalDetalhes).modal('hide');
                setTimeout(function () { openCobrarModal(btn); }, 200);
            };
        }

        if (window.$) window.$(modalDetalhes).modal('show');
    }

    document.addEventListener('click', function (event) {
        const detailBtn = event.target.closest('.js-open-detalhes-dano');
        if (detailBtn) {
            event.preventDefault();
            openDetailsModal(detailBtn);
            return;
        }

        const btn = event.target.closest('.js-open-cobrar-dano');
        if (btn) {
            event.preventDefault();
            openCobrarModal(btn);
            return;
        }
    });

    if (confirmarBtn) {
        confirmarBtn.addEventListener('click', function () {
            const selected = decisaoInputs.find(function (input) { return input.checked; });
            if (modoCobrancaInput) modoCobrancaInput.value = selected ? selected.value : 'cobrar';
            if (modoCobrancaInput && modoCobrancaInput.value === 'nao_cobrar' && justificativaInput && !justificativaInput.value) {
                justificativaInput.value = currentDano?.observacao || 'Dano nao cobrado apos revisao.';
            }
            updateModalResumo();
        });
    }

    form.addEventListener('submit', function () {
        if (danoIdInput && currentDano) danoIdInput.value = currentDano.id;
        if (valorHiddenInput && valorDisplayInput) valorHiddenInput.value = valorDisplayInput.value;
        updateModalResumo();
    });

    if (valorDisplayInput) {
        valorDisplayInput.addEventListener('input', updateModalResumo);
    }

    modalCobrar.addEventListener('change', function (event) {
        if (event.target && event.target.name === 'usar_calcao' && usarCalcaoHidden) {
            usarCalcaoHidden.value = event.target.value;
            updateModalResumo();
        }

        if (event.target && event.target.name === 'decisao_cobranca') {
            if (modoCobrancaInput) {
                modoCobrancaInput.value = event.target.value;
            }
            if (currentDano && event.target.value === 'nao_cobrar' && justificativaInput && !justificativaInput.value) {
                justificativaInput.value = currentDano.observacao || 'Dano nao cobrado apos revisao.';
            }
            updateModalResumo();
        }
    });

    updateResumoTotals();

    [modalCobrar, modalDetalhes].forEach(function (modal) {
        if (!modal) return;

        modal.addEventListener('hidden.bs.modal', function () {
            if (document.activeElement && modal.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        });
    });
})();
</script>
