<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contrato de Aluguer</title>
    <style>
        {!! $data['css'] !!} body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .ipt {
            display: inline-block;
            margin-bottom: 1px;
            margin-top: 1px;
            background-color: #f7f7f7 !important;
            text-align: center;
            padding: 2px;
            text-align: center;
        }

        .bg-gray {
            background-color: #f7f7f7;
        }
    </style>
</head>

<body>
    <div class="content">
        <table style="width: 100%;">
            <thead>
                <tr>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                    <td style="width: 20px"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" rowspan="3">
                        <img style="width:40%;height:130px" src="{!! asset('assets/imgs/logo.png') !!}" alt="">
                    </td>
                    <td colspan="6"><br></td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                    <td colspan="3" class="border" style="text-align:center;background-color:#ccffcc">
                        <span><strong>{!! $data['contrato']['titulo'] !!}</span><br>
                        <span style="font-size:20px"><strong>{!! $data['contrato']['numero'] !!}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                </tr>
                <tr>
                    <td colspan="12">
                        <span style="font-size:13px"><strong>{!! $data['contrato']['subtitulo'] !!}</strong></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>
                            <strong>CLIENTE</strong>
                        </span>
                    </td>
                    <td></td>
                    <td><strong>Nº de Apólice:</strong></td>
                    <td>{!! $data['cliente']['apolice'] !!}</td>
                    <td colspan="2">
                        <span><strong>NIF:</strong> {!! $data['cliente']['nif'] !!}</span>
                    </td>
                    <td colspan="4">
                        <span><strong>Alugado em:</strong></span>
                    </td>
                    <td>
                        <span><strong>Data:</strong></span>
                    </td>
                    <td>
                        <span><strong>Hora:</strong></span>
                    </td>
                </tr>
                <tr style="margin-top:0px">
                    <td class="border bg-gray" colspan="5" rowspan="3">
                        <span><strong>{!! $data['cliente']['nome'] !!}</strong></span><br>
                        <span> {!! $data['cliente']['nome'] !!} </span><br>
                        <span> {!! $data['cliente']['endereco'] !!} </span><br>
                        <span> {!! $data['cliente']['pais'] !!} </span>
                    </td>
                    <td></td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 96%">{!! $data['reserva']['alugado_em']['local'] !!}</span>
                    </td>
                    <td>
                        <span class="border ipt" style="width: 90%">{!! $data['reserva']['alugado_em']['data'] !!}</span>
                    </td>
                    <td>
                        <span class="border ipt" style="width: 100%">{!! $data['reserva']['alugado_em']['hora'] !!}</span>
                    </td>
                </tr>
                <tr style="margin-top:0px">
                    <td></td>
                    <td colspan="4">
                        <span><strong>Alugado em:</strong></span>
                    </td>
                    <td>
                        <span><strong>Data:</strong></span>
                    </td>
                    <td>
                        <span><strong>Hora:</strong></span>
                    </td>
                </tr>
                <tr style="margin-top:0px">
                    <td></td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 96%">{!! $data['reserva']['devolver_em']['local'] !!}</span>
                    </td>
                    <td>
                        <span class="border ipt" style="width: 90%">{!! $data['reserva']['devolver_em']['data'] !!}</span>
                    </td>
                    <td>
                        <span class="border ipt" style="width: 100%">{!! $data['reserva']['devolver_em']['hora'] !!}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span><strong>E-mail:</strong></span>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Telemóvel :</strong></span>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Matrícula:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">{!! $data['veiculo']['matricula'] !!}</span>
                    </td>
                </tr>

                <tr>
                    <td colspan="3">
                        <span class="border ipt" style="width: 90%">{!! $data['cliente']['email'] !!}</span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 98%">{!! $data['cliente']['telefone'] !!}</span>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Marca/Modelo:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">{!! $data['veiculo']['marca'] !!} / {!! $data['veiculo']['modelo'] !!}
                        </span>
                    </td>
                </tr>

                <tr>
                    <td colspan="3">
                        <span><strong>Data Nascimento:</strong></span>
                    </td>
                    <td colspan="2">
                        <span><strong>Local Nascimento:</span>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Grupo:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">{!! $data['veiculo']['grupo'] !!}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <span class="border ipt" style="width: 90%">{!! $data['cliente']['data_nascimento'] !!}</span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 98%">{!! $data['cliente']['local_nascimento'] !!}</span>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Quilómetros:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">{{$data['veiculo']['quilometros']}}</span>
                    </td>
                </tr>
                <tr style="margin-top:0px">
                    <td colspan="2">
                        <span><strong>Por:</strong></span>
                    </td>
                    <td colspan="3">
                        <span><strong>Data Validade:</span>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Combustível:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">
                            {!! $data['veiculo']['combustivel'] !!}
                        </span>
                    </td>
                </tr>

                <tr style="margin-top:0px">
                    <td colspan="2">
                        <span class="border ipt" style="width: 90%">{!! $data['cliente']['carta_conducao']['pais'] !!}</span>
                    </td>
                    <td colspan="3">
                        <span class="border ipt" style="width: 98%">{!! $data['cliente']['carta_conducao']['validade'] !!}</span>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Franquia/Caução:</strong></span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 100%">{!! $data['veiculo']['taxas']['combustivel_em_falta'] !!}</span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 100%">{!! $data['veiculo']['taxas']['taxa_reabastecimento'] !!}</span>
                    </td>
                </tr>

                <tr style="margin-top:0px">
                    <td colspan="3">
                        <span><strong>Carta Condução:</strong></span>
                    </td>
                    <td colspan="2">
                        <span><strong>Emitida em:</span>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Kms Permitidos:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">ilimitado</span>
                    </td>
                </tr>

                <tr style="margin-top:0px">
                    <td colspan="3">
                        <span class="border ipt" style="width: 90%">{!! $data['cliente']['carta_conducao']['numero'] !!}</span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 98%">{!! $data['cliente']['carta_conducao']['data_emissao'] !!}</span>
                    </td>
                    <td></td>
                    <td colspan="6">
                        <span><strong>ESTADO DA VIATURA</strong></span>
                    </td>
                </tr>


                <tr>
                    <td colspan="6">
                        <span><strong>FATURAÇÃO:</strong></span>
                    </td>
                    <td colspan="6" rowspan="12" style="vertical-align: top;">
                        <div class="">
                            <img class="border" style="width:80px;height:50px" src="{!! asset('assets/imgs/carro/frente.jpg') !!}"
                                alt="frente">
                            <img class="border" style="width:80px;height:50px" src="{!! asset('assets/imgs/carro/atras.jpg') !!}"
                                alt="atras">
                            <img class="border" style="width:80px;height:50px" src="{!! asset('assets/imgs/carro/cima.jpg') !!}"
                                alt="cima">
                            <img class="border" style="width:80px;height:50px" src="{!! asset('assets/imgs/carro/esquerda.jpg') !!}"
                                alt="esquerda">
                            <img class="border" style="width:80px;height:50px" src="{!! asset('assets/imgs/carro/direita.jpg') !!}"
                                alt="direita">
                        </div>
                        @if( count($data['estado_viatura']['itens']) == 0 )
                            
                        @else
                        <div class="border" style=" vertical-align: top;text-align: left;padding: 5px;background: #fffcc7; margin-top: 3px;">
                            <table>
                                @foreach($data['estado_viatura']['itens'] as $item)
                                    <tr>
                                        <td><strong>{!! $item['local'] !!}</strong></td>
                                        <td>{!! $item['observacoes'] !!} {!! $item['tipo_dano']!!}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <span><strong>Dias/Unidades:</strong></span>
                    </td>
                    <td colspan="3">
                        <span class="border ipt" style="width: 100%">2 Dia(s)</span>
                    </td>
                </tr>

                <tr>
                    @if( count($data['opcionais']) == 0 )
                        <td colspan="5"></td>
                    @else
                    <td colspan="5" style="width:80%" style="border-right:1px solid #000" class="border bg-gray">
                        <table style="width: 100% ;  border-radius: 0.25rem; padding: 1rem;">
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td colspan="3"><strong>Opcionais</strong></td>
                                <td><strong>Preço Unitário</strong></td>
                                <td><strong>Valor Total</strong></td>
                            </tr>
                            @foreach ($data['opcionais'] as $opcional)
                            <tr>
                                <td colspan="3">{!! $opcional['quantidade'] !!}x {!! $opcional['nome'] !!}</td>
                                <td>{!! $opcional['preco_unitario'] !!} </td>
                                <td>{!! $opcional['valor_total'] !!} </td>
                            </tr>
                          
                            @endforeach
                        </table>
                    </td>
                    @endif
                </tr>

                <tr>
                    <td colspan="2">
                        <span><strong>IVA:</strong></span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 100%">{!! $data['faturacao']['iva_total'] !!}</span>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <span><strong>Total:</strong></span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 100%">{!! $data['faturacao']['valor_total'] !!}</span>
                    </td>
                </tr>

                <tr>
                    <td colspan="3">
                        <span><strong>Nome do(a) Funcionário(a):</strong></span>
                    </td>
                    <td colspan="2"></td>
                </tr>

                <tr>
                    <td colspan="3">
                        <span class="border ipt" style="width: 90%"><strong>Erickson Ferreira</strong></span>
                    </td>
                    <td colspan="3"></td>
                </tr>


                <tr>
                    <td colspan="3"></td>
                    <td colspan="3"></td>
                </tr>

                <tr>
                    <td colspan="6"></td>
                </tr>

                <tr>
                    <td colspan="6"></td>
                </tr>

                <tr>
                    <td colspan="6"></td>
                </tr>

                <tr>
                    <td colspan="6"></td>
                </tr>

                <tr>
                    <td colspan="6"></td>
                </tr>

                <tr>
                    <td colspan="6"></td>
                </tr>

                <tr>
                    <td colspan="6"></td>
                </tr>

                <tr>
                    <td colspan="6"></td>
                </tr>

                <br>
                <br>
                <br>
                <tr>
                    <td colspan="12">
                        <span style="font-size: 9px">DECLARO ACEITAR AS CONDIÇÕES PARTICULARES E AS CONDIÇÕES GERAIS
                            CONSTANTES NO ANEXO DESTE DOCUMENTO</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="12">
                        <p style="font-size:15px"><strong>ASSINATURA:</strong></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="12">
                        @if($data['assinatura'] != "<br>" && !is_null($data['assinatura']))
                            <img style="border-bottom:1px solid black;height: 90px; width:500px" src="{!! $data['assinatura'] !!}" alt="Assinatura do contrato">
                        @else
                            <strong>________________________________________________________________________________________________________________________</strong>
                        @endif
                    </td>
                </tr>
                <br><br>
                <tr>
                    <td colspan="12" style="text-align: center;font-size:8px">Este documento foi processado
                        informaticamente por ANYRENT licenciado a Xpand Cars Lda.</td>
                </tr>
            </tbody>
        </table>

</body>
</body>

</html>
