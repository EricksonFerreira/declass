<li class="list-group-item py-1 d-flex flex-column " id="list_opcional{{$opcional->id}}">
    <div class="row">

        <div class="col-md-3 Titulo">
            <span class="titulo_opcional">{{$opcional->nome}}</span>
        </div>
        <div class="d-flex justify-content-around col">
            <div class="valor_diario" style="font-size: 12px">
                € {{$opcional->preco_integral}} <span style="font-size: 10px">/por dia</span>
            </div>
            <div class="valor_total" class="titulo_opcional">
                € {{$opcional->preco_integral * $quantidade_dias}}
            </div>
            <div class="detalhes d-flex align-items-center texto_detalhe">
                <a onclick="toggleDescCard('opcional{{$opcional->id}}')">
                    Detalhes <i class="	fa fa-arrow-circle-down"></i>
                </a>
            </div>
        </div>
        <div class="botoes col-md-2" style="text-align:center">
            <a class="btn btn-xs btn-dark" id="modificaEstadoopcional{{$opcional->id}}" style="width: 70%"
                onclick="modificaEstado('opcional{{$opcional->id}}')">ADICIONAR</a>

        </div>
    </div>
    <div id="desc_card_opcional{{$opcional->id}}" class="desc_card" style="font-size: 10px;display:none">
        <hr style="opacity: 0.1; margin: 4px 0px 0px 0px;">
        <b>Descrição: <br></b>
        {{$opcional->descricao}}
    </div>
</li>
