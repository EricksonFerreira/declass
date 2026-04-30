
// Variável global para armazenar os dados da reserva
window.reserva = {
    data_inicio: null,
    data_fim: null,
    tarifa_base: 0,
    quantidade_dias: 0,
    estacao_retirada_id: null,
    estacao_retirada_nome: null,
    estacao_devolucao_id: null,
    estacao_devolucao_nome: null,
    grupo_frota_id: null,
    grupo_frota_nome: null,
    modelo_id: null,
    modelo_nome: null,
    user_id: null,
    user_nome: null,
    opcionais: [],
    cupom_desconto: null,
    taxa_coleta: 0,
    taxa_entrega: 0,
};
window.opcionais = [];

function formatarDataHoraReserva(valor) {
    if (!valor) {
        return '';
    }

    const data = new Date(valor);
    if (Number.isNaN(data.getTime())) {
        return String(valor);
    }

    return data.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).replace(',', '');
}

function formatarDataReserva(valor) {
    if (!valor) {
        return '';
    }

    const texto = String(valor);
    const dataParte = texto.includes('T') ? texto.split('T')[0] : texto.split(' ')[0];
    const partes = dataParte.split('-');

    if (partes.length !== 3) {
        return texto;
    }

    const [ano, mes, dia] = partes;
    return `${dia}/${mes}/${ano}`;
}

function formatarHoraReserva(valor) {
    if (!valor) {
        return '';
    }

    const texto = String(valor);
    const horaParte = texto.includes('T') ? texto.split('T')[1] : texto.split(' ')[1];

    if (!horaParte) {
        return '';
    }

    return horaParte.slice(0, 5);
}

function getOpcionais(){
    return window.opcionais;
}

function addOpcional(opcional){
    window.opcionais.push(opcional);
}

function removeAllOpcional(){
    window.opcionais = [];
}

// Funções para manipular os dados da reserva
function getDataInicio(){
    return window.reserva.data_inicio;
}

function getDataFim(){
    return window.reserva.data_fim;
}

function setDataInicio(data) {
    window.reserva.data_inicio = data;
    const display = document.getElementById('data_inicio_display');
    const dataHidden = document.getElementById('data_inicio_data');
    const horaSelect = document.getElementById('data_inicio_hora');
    const hidden = document.getElementById('data_inicio');

    if (display) {
        display.value = formatarDataReserva(data);
    }

    if (dataHidden) {
        const texto = data ? String(data) : '';
        const dataParte = texto.includes('T') ? texto.split('T')[0] : texto.split(' ')[0];
        dataHidden.value = dataParte || '';
    }

    if (horaSelect) {
        const horaParte = formatarHoraReserva(data);
        if (horaParte) {
            horaSelect.value = horaParte;
        } else {
            horaSelect.value = '';
        }
    }

    if (hidden) {
        const texto = data ? String(data) : '';
        const dataParte = texto.includes('T') ? texto.split('T')[0] : texto.split(' ')[0];
        const horaParte = formatarHoraReserva(data);
        hidden.value = dataParte && horaParte ? `${dataParte}T${horaParte}` : '';
    }
}

function setDataFim(data) {
    window.reserva.data_fim = data;
    const display = document.getElementById('data_fim_display');
    if (display) {
        display.value = formatarDataHoraReserva(data);
    }
    // Atualiza o hidden #data_fim (mesmo padrão de setDataInicio)
    const hidden = document.getElementById('data_fim');
    if (hidden) {
        const texto = data ? String(data) : '';
        const dataParte = texto.includes('T') ? texto.split('T')[0] : texto.split(' ')[0];
        const horaParte = formatarHoraReserva(data);
        hidden.value = dataParte && horaParte ? `${dataParte}T${horaParte}` : '';
    }
}

function setTarifaBase(tarifa) {
    window.reserva.tarifa_base = tarifa;
}

function calculaQuantidadeDias(data_inicio, data_fim){
    return Math.ceil((new Date(data_fim) - new Date(data_inicio)) / (1000 * 60 * 60 * 24));
}

function setQuantidadeDias(dias) {
    window.reserva.quantidade_dias = dias;
}

function setEstacaoRetirada(id, nome) {
    window.reserva.estacao_retirada_id = id;
    window.reserva.estacao_retirada_nome = nome;
}

function setEstacaoDevolucao(id, nome) {
    window.reserva.estacao_devolucao_id = id;
    window.reserva.estacao_devolucao_nome = nome;
}

function setGrupoFrota(id, nome) {
    window.reserva.grupo_frota_id = id;
    window.reserva.grupo_frota_nome = nome;
}

function setModelo(id, nome) {
    window.reserva.modelo_id = id;
    window.reserva.modelo_nome = nome;
}

function setUser(id, nome) {
    window.reserva.user_id = id;
    window.reserva.user_nome = nome;
}

function addOpcionalReserva(opcional_id, opcional_nome) {
    const idOpcional = Number(opcional_id);
    if (!Number.isFinite(idOpcional) || idOpcional <= 0) {
        return;
    }

    const quantidadeInformada = Number(opcional_nome);
    const quantidadeDesejada = Number.isFinite(quantidadeInformada) && quantidadeInformada > 0
        ? Math.floor(quantidadeInformada)
        : 1;

    const opcionaisReserva = getOpcionaisReserva();
    const opcionalIndex = opcionaisReserva.findIndex(op => Number(op.id) === idOpcional);

    if (opcionalIndex !== -1) {
        opcionaisReserva[opcionalIndex].quantidade = quantidadeDesejada;
        setOpcionaisReserva(opcionaisReserva);
        return;
    }

    if (getOpcionais().length > 0) {
        const opcionalBase = getOpcionais().find(op => Number(op.id) === idOpcional);
        if (!opcionalBase) {
            return;
        }

        window.reserva.opcionais.push({
            id: opcionalBase.id,
            nome: opcionalBase.nome,
            preco_por_dia: opcionalBase.preco_por_dia,
            preco_integral: opcionalBase.preco_integral,
            tipo_cobranca: opcionalBase.tipo_cobranca,
            preco_total: opcionalBase.preco_total ?? 0,
            quantidade: quantidadeDesejada,
        });
    }
}

function removeOpcionalReserva(opcional_id){
    const opcionaisReserva = getOpcionaisReserva();
    const opcionalIndex = opcionaisReserva.findIndex(op => op.id == opcional_id);
    if (opcionalIndex !== -1) {
        if (opcionaisReserva[opcionalIndex].quantidade > 1) {
            opcionaisReserva[opcionalIndex].quantidade--;
        } else {
            opcionaisReserva.splice(opcionalIndex, 1);
        }
    }
    setOpcionaisReserva(opcionaisReserva);
}


function removeAllOpcionalReserva(){
    window.reserva.opcionais = [];
}

function setOpcionaisReserva(opcionais) {
    window.reserva.opcionais = opcionais.map(opcional => ({
        id: opcional.id,
        nome: opcional.nome,
        preco_por_dia: Number(opcional.preco_por_dia ?? 0),
        preco_integral: Number(opcional.preco_integral ?? 0),
        tipo_cobranca: opcional.tipo_cobranca,
        preco_total: Number(opcional.preco_total ?? 0),
        quantidade: Number(opcional.quantidade || 0)
    }));
}


function setCupomDesconto(cupom) {
    window.reserva.cupom_desconto = cupom;
}

function getMinhaReserva() {
    return reserva;
}

function getTarifaBase() {
    return window.reserva.tarifa_base;
}

function getQuantidadeDias() {
    return isNaN(window.reserva.quantidade_dias) ? 0 : window.reserva.quantidade_dias;
}

function getOpcionaisReserva() {
    return window.reserva.opcionais;
}

function getCupomDescontoValor() {
    return window.reserva.cupom_desconto ? window.reserva.cupom_desconto.valor : 0;
}

function setTaxaColeta(valor) {
    window.reserva.taxa_coleta = Number(valor) || 0;
}

function setTaxaEntrega(valor) {
    window.reserva.taxa_entrega = Number(valor) || 0;
}

function getTaxaColeta() {
    return Number(window.reserva.taxa_coleta) || 0;
}

function getTaxaEntrega() {
    return Number(window.reserva.taxa_entrega) || 0;
}

function getEstacaoRetirada(){
    return window.reserva.estacao_retirada_id;
}

function getEstacaoDevolucao(){
    return window.reserva.estacao_devolucao_id;
}

function calcularValorTotal() {
    let valorTotal = 0;
    let valorTotalOpcionais = 0;
    const quantidadeDias = Number(getQuantidadeDias() || 0);

    getOpcionaisReserva().forEach(opcional => {
        const quantidade = Math.max(0, Number((opcional && opcional.quantidade) || 0));
        if (!quantidade) {
            return;
        }

        const precoDia = Number(opcional && opcional.preco_por_dia !== undefined && opcional.preco_por_dia !== null ? opcional.preco_por_dia : 0);
        const precoIntegral = Number(opcional && opcional.preco_integral !== undefined && opcional.preco_integral !== null ? opcional.preco_integral : 0);
        const tipo = String((opcional && opcional.tipo_cobranca) || '');

        const ehPorDia = tipo === 'por_dia' || (!tipo && precoDia > 0 && precoIntegral <= 0);
        if (ehPorDia) {
            valorTotalOpcionais += precoDia * quantidadeDias * quantidade;
        } else {
            valorTotalOpcionais += precoIntegral * quantidade;
        }
    });

    valorTotal = (Number(getTarifaBase() || 0) * quantidadeDias) + valorTotalOpcionais
        + getTaxaColeta() + getTaxaEntrega();

    const valorCupomDesconto = Number(getCupomDescontoValor() || 0);
    let valorDesconto = 0;
    if (valorCupomDesconto > 0) {
        valorDesconto = (valorTotal * valorCupomDesconto / 100);
    }

    const final = valorTotal - valorDesconto;
    return Number.isFinite(final) ? final : 0;
}

// Função para calcular o valor total da reserva
// function calcularValorTotal() {
//     let valorTotal = getTarifaBase() * getQuantidadeDias();

//     // Aplicar desconto do cupom, se houver
//     const descontoCupom = getCupomDescontoValor();
//     if (descontoCupom > 0) {
//         valorTotal -= (valorTotal * descontoCupom / 100);
//     }

//     // Adicionar valor dos opcionais
//     const opcionais = getOpcionaisReserva();
//     if(opcionais.length > 0){
//         opcionais.forEach(opcional => {
//             if (opcional.tipo_cobranca === "por_dia") {
//                 valorTotal += opcional.preco_por_dia * getQuantidadeDias() * opcional.quantidade;
//             } else {
//                 valorTotal += opcional.preco_integral * opcional.quantidade;
//             }
//         });
//     }

//     return valorTotal.toFixed(2);
// }

// Atualizar a função getValorTotal para usar o novo cálculo
function getValorTotal() {
    const v = Number(calcularValorTotal());
    return Number.isFinite(v) ? v : 0;
}

function getValorSemDesconto() {
    const quantidadeDias = getQuantidadeDias() || 0;
    let valorTotalOpcionais = 0;
    getOpcionaisReserva().forEach(op => {
        const qtd = op.quantidade || 1;
        if (op.tipo_cobranca === 'por_dia') {
            valorTotalOpcionais += Number(op.preco_por_dia || 0) * quantidadeDias * qtd;
        } else {
            valorTotalOpcionais += Number(op.preco_integral || 0) * qtd;
        }
    });
    const v = (Number(getTarifaBase() || 0) * quantidadeDias) + valorTotalOpcionais + getTaxaColeta() + getTaxaEntrega();
    return Number.isFinite(v) ? v : 0;
}

// Atualizar a função getValorDiario
function getValorDiario() {
    const valorTotal = parseFloat(getValorTotal());
    const quantidadeDias = getQuantidadeDias();

    if (!quantidadeDias || quantidadeDias <= 0) {
        return 0;
    }

    const valorDiario = (valorTotal / quantidadeDias);
    return Number.isFinite(valorDiario) ? valorDiario : 0;
}

// Atualizar a função atualizarMinhaReserva para usar a formatação de moeda
function atualizarMinhaReserva() {
    $('#tarifa_base').text(formatarMoeda(getTarifaBase()));

    const grupoNome = window.reserva.grupo_frota_nome || null;
    if (grupoNome) {
        $('#minhaReserva_grupo_nome').text(grupoNome);
        $('#minhaReserva_grupo_li').removeClass('d-none');
    } else {
        $('#minhaReserva_grupo_li').addClass('d-none');
    }

    const desconto = Number(getCupomDescontoValor() || 0);
    const cupom = window.reserva.cupom_desconto;
    if (desconto > 0 && cupom) {
        $('#desconto').text(desconto + '%');
        $('#minhaReserva_cupom_badge').text(cupom.codigo || '');
        $('#minhaReserva_desconto_li').removeClass('d-none');
    } else {
        $('#minhaReserva_cupom_badge').text('');
        $('#minhaReserva_desconto_li').addClass('d-none');
    }

    $('#quantidade_dias').text(getQuantidadeDias());

    // Período: estação + data/hora retirada e devolução
    const _fmtDT = function(iso) {
        if (!iso) return null;
        const d = new Date(iso.includes('T') ? iso : iso + 'T00:00');
        if (isNaN(d)) return null;
        const data = d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const hora = d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        return data + ' às ' + hora;
    };
    const estRet  = window.reserva.estacao_retirada_nome  || null;
    const estDev  = window.reserva.estacao_devolucao_nome || null;
    const dtRet   = _fmtDT(window.reserva.data_inicio);
    const dtDev   = _fmtDT(window.reserva.data_fim);
    const temPeriodo = estRet || estDev || dtRet || dtDev;
    $('#minhaReserva_periodo_section').toggle(!!temPeriodo);
    $('#minhaReserva_estacao_retirada').text(estRet || '—');
    $('#minhaReserva_data_retirada').text(dtRet  || '—');
    $('#minhaReserva_estacao_devolucao').text(estDev || '—');
    $('#minhaReserva_data_devolucao').text(dtDev  || '—');

    $('#minhaReserva_opcionals_ul').empty();

    getOpcionaisReserva().forEach(opcional => {
        const valorOpcional = opcional.tipo_cobranca === "por_dia" ? opcional.preco_por_dia : opcional.preco_integral;
        adicionarOpcionalNoHtmlDaMinhaReserva(opcional.nome, opcional.quantidade, formatarMoeda(valorOpcional));
    });

    const taxaColeta = getTaxaColeta();
    const taxaEntrega = getTaxaEntrega();
    const $liColeta = $('#minhaReserva_taxa_coleta_li');
    const $liEntrega = $('#minhaReserva_taxa_entrega_li');
    if (taxaColeta > 0) {
        $('#minhaReserva_taxa_coleta_val').text(formatarMoeda(taxaColeta));
        $liColeta.removeClass('d-none');
    } else {
        $liColeta.addClass('d-none');
    }
    if (taxaEntrega > 0) {
        $('#minhaReserva_taxa_entrega_val').text(formatarMoeda(taxaEntrega));
        $liEntrega.removeClass('d-none');
    } else {
        $liEntrega.addClass('d-none');
    }

    const desconto2 = Number(getCupomDescontoValor() || 0);
    const $semDesc = $('#minhaReserva_valor_sem_desconto');
    if (desconto2 > 0) {
        $semDesc.text(formatarMoeda(getValorSemDesconto())).removeClass('d-none');
    } else {
        $semDesc.addClass('d-none');
    }

    $('#valor_total_reserva').text(formatarMoeda(getValorTotal()));
    $('#quantidade_dias').text(`/ ${getQuantidadeDias()} dias`);
    $('#valor_diario_reserva').text(formatarMoeda(getValorDiario()));
}

// Função para atualizar a quantidade de um opcional
function atualizarQuantidadeOpcional(opcionalId, novaQuantidade) {
    const opcionais = getOpcionaisReserva();
    const idOpcional = Number(opcionalId);
    const opcionalIndex = opcionais.findIndex(op => Number(op.id) === idOpcional);

    if (opcionalIndex !== -1) {
        const quantidade = Math.max(0, Number(novaQuantidade) || 0);
        opcionais[opcionalIndex].quantidade = quantidade;

        if (quantidade === 0) {
            opcionais.splice(opcionalIndex, 1);
        }

        setOpcionaisReserva(opcionais);
        atualizarMinhaReserva();
    }
}

// Função para incrementar a quantidade de um opcional
function incrementarOpcional(opcionalId) {
    const opcionais = getOpcionaisReserva();
    const opcional = opcionais.find(op => op.id === opcionalId);

    if (opcional) {
        atualizarQuantidadeOpcional(opcionalId, opcional.quantidade + 1);
    }
}

// Função para decrementar a quantidade de um opcional
function decrementarOpcional(opcionalId) {
    const opcionais = getOpcionaisReserva();
    const opcional = opcionais.find(op => op.id === opcionalId);

    if (opcional && opcional.quantidade > 0) {
        atualizarQuantidadeOpcional(opcionalId, opcional.quantidade - 1);
    }
}

// Atualizar a função generateOpcionalHtml para incluir os botões de incremento e decremento
function generateOpcionalHtml(opcional) {
    return `
        <div class="border p-2 mx-1">
            <label class="m-0" for="btn-check-${opcional.id}-outlined">${opcional.nome}</label><br>
            <span>${formatarMoeda(opcional.preco_por_dia)}</span>
            <br>
            <span><strong style="font-size:0.7rem">Quantidade:</strong><br>
                <button class='btn btn-xs btn-dark' onclick="decrementarOpcional(${opcional.id})">-</button>
                <span id="quantidade-${opcional.id}">${opcional.quantidade || 0}</span>
                <button class='btn btn-xs btn-dark' onclick="incrementarOpcional(${opcional.id})">+</button>
            </span>
        </div>
    `;
}

// Atualizar a função adicionarOpcionalNoHtmlDaMinhaReserva para usar a formatação de moeda
function adicionarOpcionalNoHtmlDaMinhaReserva(nome, quantidade, valor) {
    let novaQuantidade = quantidade;
    if (quantidade === true) {
        novaQuantidade = 1;
    }

    const html = `
        <li class="d-flex px-3" style="border-bottom: 1px solid #9d9c9c4d;">
            <div class="col d-flex align-items-center" style="justify-content:left">
                <strong>${nome}</strong>
                <span class="badge badge-secondary" style="padding:3px;font-size:8px;background:#7f8c8d">
                    ${novaQuantidade ? novaQuantidade + 'x' : '0x'}
                </span>
            </div>
            <div class="col-md-6 d-flex align-items-center" style="justify-content:right">
                ${formatarMoeda(valor)}
            </div>
        </li>
    `;

    $('#minhaReserva_opcionals_ul').append(html);
}

// Função para formatar valores monetários
function formatarMoeda(valor) {
    const numero = Number(valor ?? 0);
    const moeda = (window.reservaCurrency || 'BRL').toUpperCase();

    if (!Number.isFinite(numero)) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: moeda
        }).format(0);
    }

    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: moeda
    }).format(numero);
}

// Função para atualizar a quantidade de um opcional na interface
function atualizarQuantidadeOpcionalInterface(opcionalId, quantidade) {
    const elementoQuantidade = document.getElementById(`quantidade-${opcionalId}`);
    if (elementoQuantidade) {
        elementoQuantidade.textContent = quantidade;
    }
}

function faltaPreencherEstacoesEDatas(){
    return window.reserva.estacao_retirada_id == null || window.reserva.estacao_devolucao_id == null || window.reserva.data_inicio == null || window.reserva.data_fim == null;
}

function formatarPrecosOpcionais() {
    if (typeof formatarMoeda !== 'function') {
        return;
    }

    $('.opcional-price__value').each(function() {
        const valor = Number($(this).data('valor'));
        $(this).text(formatarMoeda(Number.isFinite(valor) ? valor : 0));
    });
}

function toggleDescCard(code) {
    $('#desc_card_' + code).stop(true, true).slideToggle(120);
}

function modificaEstado(id) {
    const botaoElemento = $('#modificaEstado' + id);
    const idOpcional = id.replace('opcional', '');
    const opcionalCard = $('#list_' + id);

    if (botaoElemento.length && botaoElemento.text().trim() === 'ADICIONAR') {
        botaoElemento.text('REMOVER');
        botaoElemento.addClass('btn-outline-dark').removeClass('btn-dark');
        opcionalCard.addClass('activated');
        addOpcionalReserva(idOpcional, true);
    } else {
        if (botaoElemento.length) {
            botaoElemento.text('ADICIONAR');
            botaoElemento.removeClass('btn-outline-dark').addClass('btn-dark');
        }
        opcionalCard.removeClass('activated');
        removeOpcionalReserva(idOpcional);
    }

    if (typeof atualizarMinhaReserva === 'function') {
        atualizarMinhaReserva();
    }
}

function increment(id) {
    const valueElement = $('#value' + id);
    const incrementButton = $('#increment' + id);
    const decrementButton = $('#decrement' + id);
    const countMaxIncrement = $('#max_accepted_qty' + id);
    const value = parseInt(valueElement.text(), 10) || 0;
    const novoValor = value + 1;

    incrementButton.removeClass('disabled');
    decrementButton.removeClass('disabled');

    const max = parseInt(countMaxIncrement.val(), 10);
    if (Number.isFinite(max) && max >= novoValor) {
        valueElement.text(novoValor);
        $('#list_' + id).addClass('activated');

        if (max === novoValor) {
            incrementButton.addClass('disabled');
        }
    } else if (!Number.isFinite(max)) {
        valueElement.text(novoValor);
        $('#list_' + id).addClass('activated');
    }

    const idOpcional = id.replace('opcional', '');
    addOpcionalReserva(idOpcional, novoValor);

    if (typeof atualizarMinhaReserva === 'function') {
        atualizarMinhaReserva();
    }
}

function decrement(id) {
    const valueElement = $('#value' + id);
    const incrementButton = $('#increment' + id);
    const decrementButton = $('#decrement' + id);
    const value = parseInt(valueElement.text(), 10) || 0;

    incrementButton.removeClass('disabled');
    decrementButton.removeClass('disabled');

    let novoValor = 0;
    if (value > 0) {
        novoValor = value - 1;
        valueElement.text(novoValor);

        if (novoValor === 0) {
            $('#list_' + id).removeClass('activated');
            decrementButton.addClass('disabled');
        }
    }

    const idOpcional = id.replace('opcional', '');
    removeOpcionalReserva(idOpcional);

    if (typeof atualizarMinhaReserva === 'function') {
        atualizarMinhaReserva();
    }
}
