<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contrato de Aluguer</title>
    {{-- <link href="bootstrap.min.css" rel="stylesheet"> --}}
    <style>
        {{ $data['css'] }} body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .ipt{
            display: inline-block;
            margin-bottom:1px;
            margin-top:1px;
            background-color:#f7f7f7 !important;
            text-align:center;
            padding: 2px;
            text-align: center;
        }

        .bg-gray{
            background-color:#f7f7f7;
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
                        <img style="width:40%;height:130px" src="{{asset('assets/imgs/logo.png')}}" alt="">
                    </td>
                    <td colspan="6"><br></td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                    <td colspan="3" class="border" style="text-align:center;background-color:#ccffcc" >
                        <span><strong>CONTRATO DE ALUGUER</span><br>
                        <span style="font-size:20px"><strong>#00457</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                </tr>
                <tr>
                    <td colspan="12">
                        <span style="font-size:13px"><strong>CONDIÇÕES PARTICULARES DO CONTRATO DE ALUGUER</strong></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>
                            <strong>CLIENTE</strong>
                        </span>
                    </td>
                    <td colspan="2"></td>
                    <td colspan="2">
                        <span><strong>NIF:</strong> 123142329</s>
                    </td>
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
                    <td class="border bg-gray" colspan="5" rowspan="4">
                        <span><strong>Erickson Ferreira da Silva Nascimento</strong></span><br>
                        <span>saf</span><br>
                        <span>1213-123 sf</span><br>
                        <span>12 - PORTUGAL</span>
                    </td>
                    <td></td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 96%">Delivery Lisboa-Centro</s>
                    </td>
                    <td>
                        <span class="border ipt" style="width: 90%">2025-01-06</span>
                    </td>
                    <td>
                        <span class="border ipt" style="width: 100%">21:00</span>
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
                        <span class="border ipt" style="width: 96%">Delivery Lisboa-Centro</span>
                    </td>
                    <td>
                        <span class="border ipt" style="width: 90%">2025-01-06</span>
                    </td>
                    <td>
                        <span class="border ipt" style="width: 100%">21:00</span>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Matrícula:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">BF-57-VS</span>
                    </td>
                </tr>

                <tr>
                    <td colspan="3">
                        <span>
                            <strong>Nº de Apólice:</strong>
                        </span>
                    </td>
                    <td colspan="3">
                        <span>
                            <strong>Processo Nº:</strong>
                        </span>
                    </td>
                    <td colspan="2">
                        <span><strong>Marca/Modelo:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">Peugeot 208 Allure Pack 1.2 Puretech 100CV
                            CVM6    </span>
                    </td>
                </tr>

                <tr>
                    <td colspan="3">
                        <span class="border ipt" style="width: 90%">207198624</span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 98%">224</span>
                    </td>
                    <td colspan="1"></td>
                    <td colspan="2">
                        <span><strong>Grupo:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">4-5 Door Manual Unspecified Drive Petrol Air</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>
                            <strong>CONDUTOR</strong>
                        </span>
                    </td>
                    <td colspan="2"></td>
                    <td colspan="2">
                        <span><strong>NIF:</strong> 123142329</s>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Quilómetros:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">2367</span>
                    </td>
                </tr>
                <tr style="margin-top:0px">
                    <td class="border bg-gray" colspan="5" rowspan="2">
                        <span><strong>Erickson Ferreira da Silva Nascimento</strong></span><br>
                        <span>saf</span><br>
                        <span>1213-123 sf</span><br>
                        <span>12 - PORTUGAL</span>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Combustível:</strong></span>
                    </td>
                    <td colspan="4">
                        <span class="border ipt" style="width: 100%">Cheio (Gasolina 95)<br>
                            Combustível em Falta: €1.89 + IVA / litro<br>
                            Taxa Reabastecimento: €30.00 + IVA</span>
                    </td>
                </tr>

                <tr style="margin-top:0px">
                    <td></td>
                    <td colspan="2">
                        <span><strong>Franquia/Caução:</strong></span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 100%">€3,000.00</span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 100%">€3,000.00</span>
                    </td>
                </tr>
                <tr style="margin-top:0px">
                    <td colspan="2">
                        <span><strong>E-mail:</strong></span>
                    </td>
                    <td></td>
                    <td colspan="2">
                        <span><strong>Telemóvel :</strong></span>
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
                        <span class="border ipt" style="width: 90%">123@123.com</span>
                    </td>
                    <td colspan="2">
                        <span class="border ipt" style="width: 98%">+55 (81) 98765-4321</span>
                    </td>
                    <td></td>
                    <td colspan="6">
                        <span><strong>ESTADO DA VIATURA</strong></span>
                    </td>
                </tr>


                    <tr>
                        <td colspan="3">
                            <span><strong>ID/Passaporte:</strong></span>
                        </td>
                        <td >
                            <span><strong>Emitido em:</span>
                        </td>
                        <td >
                            <span><strong>Por:</span>
                        </td>
                        <td></td>
                        <td colspan="6" rowspan="6">
                            <div class="">
                                <img class="border" style="width:80px;height:50px" src="{{asset('assets/imgs/carro/frente.jpg')}}" alt="frente">
                                <img class="border" style="width:80px;height:50px" src="{{asset('assets/imgs/carro/atras.jpg')}}" alt="atras">
                                <img class="border" style="width:80px;height:50px" src="{{asset('assets/imgs/carro/cima.jpg')}}" alt="cima">
                                <img class="border" style="width:80px;height:50px" src="{{asset('assets/imgs/carro/esquerda.jpg')}}" alt="esquerda">
                                <img class="border" style="width:80px;height:50px" src="{{asset('assets/imgs/carro/direita.jpg')}}" alt="direita">
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3">
                            <span class="border ipt" style="width: 90%">1323213</span>
                        </td>
                        <td>
                            <span class="border ipt" style="width: 90%"><br></span>
                        </td>
                        <td>
                            <span class="border ipt" style="width: 98%">2313131</span>
                        </td>
                        <td></td>
                    </tr>

                    <tr>
                        <td colspan="3">
                            <span><strong>Data Nascimento:</strong></span>
                        </td>
                        <td colspan="2">
                            <span><strong>Local Nascimento:</span>
                        </td>
                        <td></td>
                    </tr>

                    <tr>
                        <td colspan="3">
                            <span class="border ipt" style="width: 90%">2025-01-24</span>
                        </td>
                        <td colspan="2">
                            <span class="border ipt" style="width: 98%">Olinda - PE</span>
                        </td>
                        <td></td>

                    </tr>
                    <tr>
                        <td colspan="2">
                            <span><strong>Carta Condução:</strong></span>
                        </td>
                        <td colspan="2">
                            <span><strong>Emitida em:</span>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span class="border ipt" style="width: 90%">3123231</span>
                        </td>
                        <td colspan="3">
                            <span class="border ipt" style="width: 98%">2025-01-24</span>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span><strong>Por:</strong></span>
                        </td>
                        <td colspan="3">
                            <span><strong>Data Validade:</span>
                        </td>
                        <td></td>
                        <td colspan="6" rowspan="8" class="border" style=" vertical-align: top;text-align: left;padding: 5px;background: #fffcc7">
                                <table>
                                    <tr>
                                        <td><strong>Direita:</strong></td>
                                        <td>O retrovisor está com vários arranhões.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Esquerda:</strong></td>
                                        <td>A porta tem um amassado.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Frente:</strong></td>
                                        <td>O para-choque está riscado.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Traseira:</strong></td>
                                        <td>A lanterna está quebrada.</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cima:</strong></td>
                                        <td>Há manchas de ferrugem.</td>
                                    </tr>
                                </table>
                        </td>

                    </tr>
                    <tr>
                        <td colspan="2">
                            <span class="border ipt" style="width: 90%">123123</span>
                        </td>
                        <td colspan="3">
                            <span class="border ipt" style="width: 98%">2051-02-17</span>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6">
                            <span><strong>FATURAÇÃO:</strong></span>
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
                        <td colspan="5" style="width:80%" style="border-right:1px solid #000" class="border bg-gray">
                            <table style="width: 100%">
                                <tr>
                                    <td  colspan="3">1 x Tarifa de Aluguer </td>
                                    <td>23.0% </td>
                                    <td>€90.00 </td>
                                </tr>
                                <tr>
                                    <td colspan="3">2 x Fora de Hora </td>
                                    <td>23.0% </td>
                                    <td>€100.00 </td>
                                </tr>
                                <tr>
                                    <td colspan="3"> 1 x Básico </td>
                                    <td>0% </td>
                                    <td>€0.00 </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span><strong>IVA:</strong></span>
                        </td>
                        <td colspan="3">
                            <span class="border ipt" style="width: 100%">2 Dia(s)</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span><strong>Total:</strong></span>
                        </td>
                        <td colspan="3">
                            <span class="border ipt" style="width: 100%">2 Dia(s)</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <span><strong>Nome do(a) Funcionário(a):</strong></span>
                        </td>
                        <td colspan="3">
                            <span><strong>Rubrica:</strong></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <span class="border ipt"  style="width: 90%"><strong>Erickson Ferreira</strong></span>
                        </td>
                        <td colspan="3">
                            <span><strong>_______________________________</strong></span>
                        </td>
                    </tr>
                    <br>
                    <br>
                    <br>
                    <tr>
                        <td colspan="12">
                            <span style="font-size: 9px">DECLARO ACEITAR AS CONDIÇÕES PARTICULARES E AS CONDIÇÕES GERAIS CONSTANTES NO ANEXO DESTE DOCUMENTO</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="12">
                            <p style="font-size:15px"><strong>ASSINATURA:</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="12"><strong>________________________________________________________________________________________________________________________</strong></td>
                    </tr>
                    <br><br>
                    <tr>
                        <td colspan="12" style="text-align: center;font-size:8px">Este documento foi processado informaticamente por ANYRENT licenciado a Xpand Cars Lda.</td>
                    </tr>
            </tbody>
        </table>

</body>
</body>

</html>
