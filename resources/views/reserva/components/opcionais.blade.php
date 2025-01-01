<style>
    .disabled {
        pointer-events: none;
        opacity: 0.5;
    }

    .btn-xs {
        font-size: 10px;
    }

    .activated {
        background-color: #34495e;
        color: #ffffff;
    }

    .activated>.row>.col>.detalhes {
        color: #f39c12 !important;
    }

    .activated>.row>.botoes>.btn {
        background: #fff !important;
    }

    .activated>.row>.botoes>.btn:hover {
        background: #9d9d9d !important;
    }

    .texto_detalhe {
        font-weight: bold;
        font-size: 11px;
        color: #7f8c8d;
        cursor: pointer;
    }

    .titulo_opcional {
        font-weight: bold;
        font-size: 13px
    }
</style>
<div class="col-md-12">
    <div class="">
        @foreach ($tiposOpcionais as $tipoOpcional)
            @if (count($tipoOpcional['opcionais']) > 0)
                <h3 class="col-md-12 mt-1" style="font-size:18px;font-weight:600">{{ $tipoOpcional['tipo']->nome }}</h3>
                <ul class="list-group option-animate col-md-12">
                    @if (isset($tipoOpcional['opcionais']) && count($tipoOpcional['opcionais']) > 0)
                        @foreach ($tipoOpcional['opcionais'] as $opcional)
                            <li class="list-group-item py-1 d-flex flex-column @if(in_array($opcional->id,  array_column($opcionaisSelecionados, 'id'))) activated @endif" id="list_opcional{{ $opcional->id }}">
                                <div class="row">

                                    <div class="col-md-3 Titulo">
                                        <span class="titulo_opcional"
                                            id="{{ $opcional->id }}_nome">{{ $opcional->nome }}</span>
                                    </div>
                                    <div class="d-flex justify-content-around col">
                                        <div class="valor_diario" style="font-size: 12px">
                                          @if($opcional->tipo_cobranca == "por_dia")
                                            € {{ $opcional->preco_por_dia }} <span style="font-size: 10px">/por dia</span>
                                          @else
                                            € {{ $opcional->preco_integral }} <span style="font-size: 10px">/integral</span>
                                          @endif

                                            {{-- € 10 <span style="font-size: 10px">/por dia</span> --}}
                                        </div>
                                        <div class="valor_total" class="titulo_opcional">
                                            {{-- € 40 --}}
                                        </div>
                                        <div class="detalhes d-flex align-items-center texto_detalhe">
                                            <a onclick="toggleDescCard('opcional{{ $opcional->id }}')">
                                                Detalhes <i class="	fa fa-arrow-circle-down"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <input type="hidden" id="opcional{{ $opcional->id }}_input"
                                        name="opcional[{{ $opcional->id }}]">
                                    @if ($opcional->tipo_cobranca == 'valor_integral')
                                        @if(in_array($opcional->id, array_column($opcionaisSelecionados, 'id')))
                                        <div class="botoes col-md-2" style="text-align:center">
                                            <a class="btn btn-xs btn-outline-dark"
                                                id="modificaEstadoopcional{{ $opcional->id }}" style="width: 70%"
                                                onclick="modificaEstado('opcional{{ $opcional->id }}')">REMOVER</a>

                                        </div>
                                        @else
                                        <div class="botoes col-md-2" style="text-align:center">
                                            <a class="btn btn-xs btn-dark"
                                                id="modificaEstadoopcional{{ $opcional->id }}" style="width: 70%"
                                                onclick="modificaEstado('opcional{{ $opcional->id }}')">ADICIONAR</a>

                                        </div>
                                        @endif
                                    @else
                                        <div class="botoes col-md-2" style="text-align:center">
                                            <div>
                                                <a onclick="decrement('opcional{{ $opcional->id }}')"
                                                    class="btn btn-sm btn-dark"
                                                    id="decrementopcional{{ $opcional->id }}">-</a>

                                                <span id="valueopcional{{ $opcional->id }}">
                                                {{ in_array($opcional->id,  array_column($opcionaisSelecionados, 'id')) ? $opcionaisSelecionados[array_search($opcional->id, array_column($opcionaisSelecionados, 'id'))]['quantidade'] : 0 }}
                                                </span>
                                                <a onclick="increment('opcional{{ $opcional->id }}')"
                                                    class="btn btn-sm btn-dark"
                                                    id="incrementopcional{{ $opcional->id }}">+</a>
                                                <input type="hidden" id="max_accepted_qtyopcional{{ $opcional->id }}"
                                                    name="max_accepted_qtyopcional{{ $opcional->id }}" value="2">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div id="desc_card_opcional{{ $opcional->id }}" class="desc_card"
                                    style="font-size: 10px;display:none">
                                    <hr style="opacity: 0.1; margin: 4px 0px 0px 0px;">
                                    <b>Descrição: <br></b>
                                    {{$opcional->descricao}}

                                </div>
                            </li>
                        @endforeach
                    @endif
                </ul>
            @endif
        @endforeach
    </div>
</div>

{{-- Opcionais --}}
<script>
    $(document).ready(function() {
        const tiposOpcionais = {!! isset($tiposOpcionais) ? json_encode($tiposOpcionais) : '[]' !!};
        // console.log("tiposOpcionais", tiposOpcionais)
        if(tiposOpcionais.length > 0){
             tiposOpcionais.forEach(tipoOpcional => {
          //      console.log("tipoOpcional", tipoOpcional)
                if(tipoOpcional.opcionais.length > 0){
                    tipoOpcional.opcionais.forEach(opcional => {
            //            console.log(opcional)
                    addOpcional(opcional)
                    });
                }
             });

         }
//         console.log(getOpcionais())
    });

    function toggleDescCard(code) {
        $('#desc_card_' + code).toggle();
    }

    function modificaEstado(id) {
        var botaoElemento = $("#modificaEstado" + id);
        var valor;
        const id_opcional = id.replace('opcional', '')

        if (botaoElemento.text() == 'ADICIONAR') {
            botaoElemento.text("REMOVER");
            botaoElemento.addClass("btn-outline-dark");
            botaoElemento.removeClass("btn-dark");
            $('#list_' + id).addClass('activated');
            valor = 'REMOVER';

            addOpcionalReserva(id_opcional, true)
        } else {
            botaoElemento.text("ADICIONAR");
            botaoElemento.removeClass("btn-outline-dark");
            botaoElemento.addClass("btn-dark");
            $('#list_' + id).removeClass('activated');
            valor = 'ADICIONAR';

            removeOpcionalReserva(id_opcional)
        }
        val = valor == 'ADICIONAR' ? false : true;


        //  modificaOptional(id, val, false)
atualizarMinhaReserva()


    }

    function getItemListaOpcionalReserva(opcional, valor) {
        const quantidade = opcional.tipo_cobranca == "por_dia" ? valor : 1;
        const valorOpcional = opcional.tipo_cobranca == "por_dia" ? quantidade * opcional.preco_por_dia : opcional
            .preco_integral;

        return `
                    <li id="li_card_reserva_opcional${opcional.id}" class="d-flex px-3" style="border-bottom: 1px solid #9d9c9c4d;">
                <div class="col d-flex align-items-center" style="justify-content:left">
                    <strong>${opcional.nome}</strong> <span class="badge badge-secondary"
                        style="padding:3px;font-size:8px;background:#7f8c8d">${quantidade}x</span>
                </div>
                <div class="col-md-6 d-flex align-items-center" style="justify-content:right">
                    € ${valorOpcional}</div>

            </li>
`;
    }

    function modificaOptional(code, valor, isQuantidade = true) {
        // Incrementa ou adiciona no input hide
        $('#' + code + '_input').val(valor)

        // Adiciona o item e o valor no card lateral
        // $('#minhaReserva_opcionals_ul').html('')

        const id = code.replace('opcional', '')
        const opcional = window.opcionaisGlobais.filter(opcional => opcional.id == parseInt(id))[0];

        adicionarOpcionalNaLista(opcional, valor)
        atualizaValorTotalReserva()
        // console.log(window.opcionaisSelecionados)
atualizarMinhaReserva()

    }
    function atualizaValorTotalReserva(){
        let valorTotal = 0;
        window.opcionaisSelecionados.forEach(opcional => {
            const opcionalSelecionado = window.opcionaisGlobais.filter(opcionalG => opcionalG.id == opcional.opcional_id)[0];
            const valorOpcional = opcionalSelecionado.tipo_cobranca == "por_dia" ? opcional.quantidade * opcionalSelecionado.preco_por_dia : opcionalSelecionado.preco_integral;
            valorTotal += valorOpcional;
        });

        $('#valor_total_reserva').text(valorTotal);
    }

    function adicionarOpcionalNaLista(opcional, valor) {
        // Verificar se existe algum elemento com o ID específico
        let existeElemento = $(`#li_card_reserva_opcional${opcional.id}`).length > 0;

        // Caso o elemento exista, remova-o
        if (existeElemento) {
            window.opcionaisSelecionados = window.opcionaisSelecionados.filter(opcionalSelecionado => opcionalSelecionado.opcional_id != opcional.id);
            $(`#li_card_reserva_opcional${opcional.id}`).remove();
        }

        if(valor == 0 || valor == false) {
            console.log("Removendo opcional", opcional.id)
            $(`#li_card_reserva_opcional${opcional.id}`).remove();
            window.opcionaisSelecionados = window.opcionaisSelecionados.filter(opcionalSelecionado => opcionalSelecionado.opcional_id != opcional.id);
            return;
        }
        console.log("Adicionando opcional", opcional)
        addOpcionalReserva(opcional.id, opcional.nome, valor);

        // Adicionar o novo elemento à lista
        $('#minhaReserva_opcionals_ul').append(getItemListaOpcionalReserva(opcional, valor));
    }

    function increment(id) {
        var valueElement = $('#value' + id);
        var incrementButton = $('#increment' + id);
        var decrementButton = $('#decrement' + id);
        var countMaxIncrement = $('#max_accepted_qty' + id);
        var value = parseInt(valueElement.text(), 10);
        novoValor = value + 1;
//        console.log("increment -> countMaxIncrement.val()" ,countMaxIncrement.val())

        incrementButton.removeClass('disabled');
        decrementButton.removeClass('disabled');

        if (countMaxIncrement.val() >= novoValor) {
            valueElement.text(novoValor);
            $('#list_' + id).addClass('activated');
            if (countMaxIncrement.val() == novoValor) {
                $('#list_' + id).addClass('activated');
                incrementButton.addClass('disabled');
            }
            if (novoValor === 0) {
                $('#list_' + id).removeClass('activated');
                decrementButton.removeClass('disabled');
            }
        }

        id_opcional = id.replace('opcional', '')
        addOpcionalReserva(id_opcional, novoValor)
        atualizarMinhaReserva()
      //  console.log(id, novoValor)
        // addOpcional(id, novoValor)

        // modificaOptional(id, novoValor)
    }

    function decrement(id) {
        var valueElement = $('#value' + id);
        var incrementButton = $('#increment' + id);
        var decrementButton = $('#decrement' + id);
        var value = parseInt(valueElement.text(), 10);

        incrementButton.removeClass('disabled');
        decrementButton.removeClass('disabled');
        novoValor = 0;
        if (value > 0) {
            novoValor = value - 1;
            valueElement.text(novoValor);
            incrementButton.removeClass('disabled');
            if (novoValor === 0) {
                $('#list_' + id).removeClass('activated');
                decrementButton.addClass('disabled');
            }
        }

        id_opcional = id.replace('opcional', '')
        removeOpcionalReserva(id_opcional)
        atualizarMinhaReserva()
        // modificaOptional(id, novoValor)
    }
</script>
