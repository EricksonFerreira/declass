<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Aluguel</title>
    <style>
        {!! $data['css'] ?? '' !!}

        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: A4 portrait;
            margin: 12mm 14mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9.5px;
            color: #1a2035;
            background: #fff;
        }

        /* ── Page wrapper (conteúdo alinhado ao A4) ── */
        .page {
            width: 100%;
            max-width: 190mm;
            min-height: 250mm;
            margin: 0 auto;
            padding: 0;
        }

        .section-title,
        .doc-header,
        .two-col,
        .dano-box {
            page-break-inside: avoid;
        }

        /* ── Header ── */
        .doc-header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .doc-header__logo { display: table-cell; width: 38%; vertical-align: middle; }
        .doc-header__logo img { max-height: 64px; max-width: 200px; }
        .doc-header__logo .sistema-logo { max-height: 48px; }
        .doc-header__info { display: table-cell; width: 62%; vertical-align: middle; text-align: right; }
        .doc-header__title {
            font-size: 14px;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .doc-header__subtitle {
            font-size: 8px;
            color: #475569;
            margin-top: 3px;
            letter-spacing: 0.04em;
        }
        .doc-header__num {
            display: inline-block;
            margin-top: 5px;
            background: #1e3a8a;
            color: #fff;
            font-weight: 800;
            font-size: 10px;
            padding: 3px 12px;
            border-radius: 20px;
            letter-spacing: 0.06em;
        }

        /* ── Accent stripe ── */
        .accent-stripe {
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 50%, #60a5fa 100%);
            border-radius: 2px;
            margin-bottom: 12px;
        }

        /* ── Section title ── */
        .section-title {
            font-size: 8px;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #1e3a8a;
            border-left: 3px solid #1e3a8a;
            padding-left: 6px;
            margin-bottom: 6px;
            margin-top: 10px;
        }

        /* ── Info table ── */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 3px 5px; vertical-align: top; }

        .field-label {
            font-size: 7.5px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .field-value {
            font-size: 9px;
            color: #0f172a;
            font-weight: 500;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 1px;
            min-width: 60px;
        }

        /* ── Sections grid: 2 cols ── */
        .two-col { display: table; width: 100%; margin-top: 6px; }
        .col-left  { display: table-cell; width: 55%; padding-right: 10px; vertical-align: top; }
        .col-right { display: table-cell; width: 45%; vertical-align: top; }

        /* ── Vehicle images ── */
        .viatura-images { margin-bottom: 6px; }
        .viatura-images img {
            width: 60px;
            height: 38px;
            object-fit: cover;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin: 2px;
        }

        /* ── Damage table ── */
        .dano-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 5px;
            padding: 5px 7px;
            margin-top: 5px;
        }
        .dano-box table { width: 100%; border-collapse: collapse; }
        .dano-box td { padding: 2px 3px; font-size: 8.5px; }
        .dano-box td:first-child { font-weight: 700; color: #92400e; white-space: nowrap; width: 30%; }

        /* ── Opcionais table ── */
        .opcionais-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .opcionais-table th {
            background: #1e3a8a;
            color: #fff;
            font-size: 7.5px;
            font-weight: 700;
            padding: 4px 5px;
            text-align: left;
        }
        .opcionais-table td {
            padding: 3px 5px;
            font-size: 8.5px;
            border-bottom: 1px solid #e2e8f0;
        }
        .opcionais-table tr:nth-child(even) td { background: #f8fafc; }

        .detail-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .detail-table th {
            background: #e2e8f0;
            color: #0f172a;
            font-size: 7.5px;
            font-weight: 800;
            padding: 4px 5px;
            text-align: left;
            border: 1px solid #cbd5e1;
        }
        .detail-table td {
            padding: 4px 5px;
            font-size: 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .detail-table tr:nth-child(even) td { background: #f8fafc; }
        .detail-table .money { text-align: right; white-space: nowrap; font-weight: 700; }
        .detail-table .muted { color: #64748b; font-size: 7.5px; }
        .thumb {
            width: 38px;
            height: 30px;
            object-fit: cover;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            margin-right: 2px;
        }

        /* ── Financial highlight ── */
        .finance-box {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f2ff 100%);
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 7px 10px;
            margin-top: 8px;
        }
        .finance-row { display: table; width: 100%; padding: 2px 0; }
        .finance-row .lbl { display: table-cell; font-size: 8px; font-weight: 700; color: #475569; }
        .finance-row .val { display: table-cell; text-align: right; font-size: 8.5px; font-weight: 700; color: #1e3a8a; }
        .finance-row.total .lbl,
        .finance-row.total .val { font-size: 10px; color: #1e40af; }

        /* ── Divider ── */
        .divider { border: none; border-top: 1px dashed #cbd5e1; margin: 10px 0; }

        /* ── Declaration ── */
        .declaration {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 7px 10px;
            font-size: 8px;
            color: #475569;
            text-align: center;
            margin-top: 10px;
            line-height: 1.5;
        }

        /* ── Signature ── */
        .signature-section { margin-top: 12px; }
        .signature-label { font-size: 9px; font-weight: 700; color: #1e3a8a; margin-bottom: 6px; }
        .signature-area {
            border-bottom: 2px solid #1e3a8a;
            min-height: 80px;
            margin-top: 4px;
            text-align: center;
            position: relative;
        }
        .signature-area img { max-height: 80px; max-width: 500px; }

        /* ── Footer ── */
        .doc-footer {
            margin-top: 14px;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }

        /* ── Badge ── */
        .badge-blue {
            display: inline-block;
            background: #dbeafe;
            color: #1e3a8a;
            font-size: 7.5px;
            font-weight: 700;
            border-radius: 4px;
            padding: 1px 6px;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ════ HEADER ════ --}}
    <div class="doc-header">
        <div class="doc-header__logo">
            @if(!empty($data['documento']['empresa']['logo_url']))
                <img src="{{ $data['documento']['empresa']['logo_url'] }}" alt="Logo da empresa">
            @else
                <img class="sistema-logo" src="{!! asset('assets/imgs/logo.png') !!}" alt="XpandCars">
            @endif
        </div>
        <div class="doc-header__info">
            <div class="doc-header__title">Contrato de Aluguel</div>
            <div class="doc-header__subtitle">{{ $data['documento']['empresa']['nome'] ?? 'Locadora' }} &bull; Condições Particulares</div>
            <div class="doc-header__num">{{ $data['contrato']['numero'] }}</div>
        </div>
    </div>

    <div class="accent-stripe"></div>

    {{-- ════ CLIENTE + VEÍCULO ════ --}}
    <div class="two-col">
        <div class="col-left">
            <div class="section-title">Dados do Cliente</div>
            <table class="info-table">
                <tr>
                    <td style="width:50%">
                        <div class="field-label">Nome Completo</div>
                        <div class="field-value">{!! $data['cliente']['nome'] !!}</div>
                    </td>
                    <td style="width:25%">
                        <div class="field-label">NIF / Doc.</div>
                        <div class="field-value">{!! $data['cliente']['nif'] !!}</div>
                    </td>
                    <td style="width:25%">
                        <div class="field-label">Passaporte</div>
                        <div class="field-value">{!! $data['cliente']['passaporte'] !!}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">E-mail</div>
                        <div class="field-value">{!! $data['cliente']['email'] !!}</div>
                    </td>
                    <td>
                        <div class="field-label">Telefone</div>
                        <div class="field-value">{!! $data['cliente']['telefone'] !!}</div>
                    </td>
                    <td>
                        <div class="field-label">Data Nascimento</div>
                        <div class="field-value">{!! $data['cliente']['data_nascimento'] !!}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="field-label">Endereço</div>
                        <div class="field-value">{!! $data['cliente']['endereco'] !!}</div>
                    </td>
                    <td>
                        <div class="field-label">País</div>
                        <div class="field-value">{!! $data['cliente']['pais'] !!}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">Carta de Condução Nº</div>
                        <div class="field-value">{!! $data['cliente']['carta_conducao']['numero'] !!}</div>
                    </td>
                    <td>
                        <div class="field-label">Emitida em</div>
                        <div class="field-value">{!! $data['cliente']['carta_conducao']['data_emissao'] !!}</div>
                    </td>
                    <td>
                        <div class="field-label">Válida até</div>
                        <div class="field-value">{!! $data['cliente']['carta_conducao']['validade'] !!}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="col-right">
            <div class="section-title">Dados da Locação</div>
            <table class="info-table">
                <tr>
                    <td style="width:50%">
                        <div class="field-label">Local Retirada</div>
                        <div class="field-value">{!! $data['reserva']['alugado_em']['local'] !!}</div>
                    </td>
                    <td style="width:25%">
                        <div class="field-label">Data</div>
                        <div class="field-value">{!! $data['reserva']['alugado_em']['data'] !!}</div>
                    </td>
                    <td style="width:25%">
                        <div class="field-label">Hora</div>
                        <div class="field-value">{!! $data['reserva']['alugado_em']['hora'] !!}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">Local Devolução</div>
                        <div class="field-value">{!! $data['reserva']['devolver_em']['local'] !!}</div>
                    </td>
                    <td>
                        <div class="field-label">Data</div>
                        <div class="field-value">{!! $data['reserva']['devolver_em']['data'] !!}</div>
                    </td>
                    <td>
                        <div class="field-label">Hora</div>
                        <div class="field-value">{!! $data['reserva']['devolver_em']['hora'] !!}</div>
                    </td>
                </tr>
            </table>

            <div class="section-title" style="margin-top:8px">Veículo</div>
            <table class="info-table">
                <tr>
                    <td style="width:40%">
                        <div class="field-label">Marca / Modelo</div>
                        <div class="field-value">{!! $data['veiculo']['marca'] !!} / {!! $data['veiculo']['modelo'] !!}</div>
                    </td>
                    <td style="width:30%">
                        <div class="field-label">Matrícula / ID</div>
                        <div class="field-value">{!! $data['veiculo']['matricula'] !!}</div>
                    </td>
                    <td style="width:30%">
                        <div class="field-label">Grupo</div>
                        <div class="field-value">{!! $data['veiculo']['grupo'] !!}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">Quilometragem</div>
                        <div class="field-value">{{ $data['veiculo']['quilometros'] }} km</div>
                    </td>
                    <td>
                        <div class="field-label">Combustível</div>
                        <div class="field-value">{!! $data['veiculo']['combustivel'] !!}</div>
                    </td>
                    <td>
                        <div class="field-label">Kms Permitidos</div>
                        <div class="field-value">{!! $data['veiculo']['kms_permitidos'] !!}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <hr class="divider">

    <div class="section-title">Resumo Detalhado da Reserva</div>
    <table class="detail-table">
        <tbody>
            <tr>
                <td><strong>Status</strong><br>{{ $data['reserva']['status'] ?? '-' }}</td>
                <td><strong>Origem</strong><br>{{ $data['reserva']['origem'] ?? '-' }}</td>
                <td><strong>Pagamento efetuado</strong><br>{{ $data['reserva']['pagamento_efetuado'] ?? '-' }}</td>
                <td><strong>Criada em</strong><br>{{ $data['reserva']['criada_em'] ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Grupo reservado</strong><br>{{ $data['reserva']['grupo_reservado'] ?? '-' }}</td>
                <td><strong>Modelo reservado</strong><br>{{ $data['reserva']['modelo_reservado'] ?? '-' }}</td>
                <td colspan="2"><strong>Observacoes</strong><br>{{ $data['reserva']['observacoes'] ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Valores Detalhados</div>
    @php($fin = $data['financeiro_detalhado'] ?? [])
    <table class="detail-table">
        <tbody>
            <tr><td>Grupo</td><td class="money">{{ $fin['grupo'] ?? '-' }}</td></tr>
            <tr><td>Tarifa diaria</td><td class="money">{{ $fin['tarifa_diaria'] ?? '-' }}</td></tr>
            <tr><td>{{ $fin['dias_label'] ?? 'Dias x tarifa' }}</td><td class="money">{{ $fin['valor_tarifa_base'] ?? '-' }}</td></tr>
            <tr><td>Opcionais</td><td class="money">{{ $fin['opcionais'] ?? '-' }}</td></tr>
            <tr><td>Retirada fora do expediente</td><td class="money">{{ $fin['taxa_coleta'] ?? '-' }}</td></tr>
            <tr><td>Devolucao fora do expediente</td><td class="money">{{ $fin['taxa_entrega'] ?? '-' }}</td></tr>
            <tr><td>Subtotal</td><td class="money">{{ $fin['subtotal'] ?? '-' }}</td></tr>
            <tr><td>Desconto {{ $fin['desconto_label'] ?? '' }}</td><td class="money">- {{ $fin['desconto_valor'] ?? '-' }}</td></tr>
            <tr><td><strong>Total</strong></td><td class="money"><strong>{{ $fin['total'] ?? ($data['faturacao']['valor_total'] ?? '-') }}</strong></td></tr>
        </tbody>
    </table>

    @if(!empty($data['pagamentos']))
        <div class="section-title">Pagamentos e Transacoes</div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Metodo</th>
                    <th>Status</th>
                    <th>Confirmado em</th>
                    <th>Pago por</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['pagamentos'] as $pagamento)
                    <tr>
                        <td>{{ $pagamento['data'] }}</td>
                        <td class="money">{{ $pagamento['valor'] }}</td>
                        <td>{{ $pagamento['metodo'] }}</td>
                        <td>{{ $pagamento['status'] }}</td>
                        <td>{{ $pagamento['confirmado_em'] }}</td>
                        <td>{{ $pagamento['pago_por'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!empty($data['calcao']) && is_array($data['calcao']))
        <div class="section-title">Calcao</div>
        <table class="detail-table">
            <tbody>
                <tr>
                    <td><strong>Status</strong><br>{{ $data['calcao']['status'] }}</td>
                    <td><strong>Metodo</strong><br>{{ $data['calcao']['metodo'] }}</td>
                    <td><strong>Valor calculado</strong><br>{{ $data['calcao']['valor_calculado'] }}</td>
                    <td><strong>Valor autorizado</strong><br>{{ $data['calcao']['valor_autorizado'] }}</td>
                    <td><strong>Valor capturado</strong><br>{{ $data['calcao']['valor_capturado'] }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="section-title">Danos e Avarias</div>
    @if(!empty($data['danos_detalhados']))
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Local</th>
                    <th>Descricao</th>
                    <th>Tipo / Status</th>
                    <th>Custo</th>
                    <th>Correcao</th>
                    <th>Imagens</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['danos_detalhados'] as $dano)
                    <tr>
                        <td>
                            <strong>{{ $dano['localizacao'] }}</strong><br>
                            <span class="muted">{{ $dano['categoria'] }} - {{ $dano['localizacao_problema'] }}</span><br>
                            <span class="muted">{{ $dano['registrado_em'] }}</span>
                        </td>
                        <td>
                            {{ $dano['observacoes'] }}<br>
                            <span class="muted">KM: {{ $dano['quilometragem'] }}</span>
                        </td>
                        <td>{{ $dano['tipo_dano'] }}<br><strong>{{ $dano['status'] }}</strong></td>
                        <td class="money">{{ $dano['custo_estimado'] }}</td>
                        <td>
                            {{ $dano['correcao'] }}<br>
                            <span class="muted">{{ $dano['correcao_data'] }} - {{ $dano['correcao_usuario'] }}</span>
                        </td>
                        <td>
                            @forelse($dano['imagens'] as $img)
                                <img class="thumb" src="{{ $img }}" alt="Dano">
                            @empty
                                <span class="muted">Sem imagem</span>
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="font-size:8px;color:#64748b;font-style:italic;margin-top:4px">Nenhuma avaria registrada nesta reserva.</div>
    @endif

    @if(!empty($data['cobrancas_avaria']))
        <div class="section-title">Cobrancas de Avarias</div>
        <table class="detail-table">
            <thead>
                <tr><th>Data</th><th>Descricao</th><th>Status</th><th>Valor</th></tr>
            </thead>
            <tbody>
                @foreach($data['cobrancas_avaria'] as $cobranca)
                    <tr>
                        <td>{{ $cobranca['data'] }}</td>
                        <td>{{ $cobranca['descricao'] }}</td>
                        <td>{{ $cobranca['status'] }}</td>
                        <td class="money">{{ $cobranca['valor'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <hr class="divider">

    {{-- ════ ESTADO DA VIATURA + FATURAÇÃO ════ --}}
    <div class="two-col">
        <div class="col-left">
            <div class="section-title">Estado da Viatura</div>
            <div class="viatura-images">
                <img src="{!! asset('assets/imgs/carro/frente.jpg') !!}" alt="Frente">
                <img src="{!! asset('assets/imgs/carro/atras.jpg') !!}" alt="Trás">
                <img src="{!! asset('assets/imgs/carro/cima.jpg') !!}" alt="Cima">
                <img src="{!! asset('assets/imgs/carro/esquerda.jpg') !!}" alt="Esquerda">
                <img src="{!! asset('assets/imgs/carro/direita.jpg') !!}" alt="Direita">
            </div>

            @if(count($data['estado_viatura']['itens']) > 0)
                <div class="dano-box">
                    <table>
                        @foreach($data['estado_viatura']['itens'] as $item)
                            <tr>
                                <td><strong>{!! $item['local'] !!}</strong></td>
                                <td>
                                    <div>{!! $item['localizacao'] ?? '' !!} — {!! $item['observacoes'] ?? '' !!} {!! $item['tipo_dano'] ?? '' !!}</div>
                                    @if(!empty($item['correcao']))
                                        <div style="margin-top:3px;font-size:8px;color:#047857;"><strong>Correção:</strong> {!! $item['correcao'] !!}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @else
                <div style="font-size:8px;color:#64748b;font-style:italic;margin-top:4px">Sem danos registados.</div>
            @endif

            <div class="section-title" style="margin-top:8px">Funcionário Responsável</div>
            <table class="info-table">
                <tr>
                    <td>
                        <div class="field-label">Nome</div>
                        <div class="field-value">{{ $data['funcionario']['nome'] }}</div>
                    </td>
                    <td>
                        <div class="field-label">Data Emissão</div>
                        <div class="field-value">{{ $data['documento']['data_emissao'] }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="col-right">
            <div class="section-title">Faturação</div>

            @if(count($data['opcionais']) > 0)
                <table class="opcionais-table">
                    <thead>
                        <tr>
                            <th>Opcional</th>
                            <th style="text-align:center">Qtd</th>
                            <th style="text-align:right">Unit.</th>
                            <th style="text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['opcionais'] as $opcional)
                            <tr>
                                <td>{!! $opcional['nome'] !!}</td>
                                <td style="text-align:center">{{ $opcional['quantidade'] }}</td>
                                <td style="text-align:right">{!! $opcional['preco_unitario'] !!}</td>
                                <td style="text-align:right">{!! $opcional['valor_total'] !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="finance-box">
                <div class="finance-row">
                    <span class="lbl">Dias / Unidades</span>
                    <span class="val">{{ $data['faturacao']['dias_unidades'] }}</span>
                </div>
                <div class="finance-row">
                    <span class="lbl">IVA</span>
                    <span class="val">{!! $data['faturacao']['iva_total'] !!}</span>
                </div>
                <hr style="border:none;border-top:1px solid #bfdbfe;margin:4px 0">
                <div class="finance-row total">
                    <span class="lbl">TOTAL</span>
                    <span class="val">{!! $data['faturacao']['valor_total'] !!}</span>
                </div>
            </div>

            <div style="margin-top:8px">
                <table class="info-table">
                    <tr>
                        <td>
                            <div class="field-label">Combustível em Falta</div>
                            <div class="field-value">{!! $data['veiculo']['taxas']['combustivel_em_falta'] !!}</div>
                        </td>
                        <td>
                            <div class="field-label">Taxa Reabastecimento</div>
                            <div class="field-value">{!! $data['veiculo']['taxas']['taxa_reabastecimento'] !!}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <hr class="divider">

    {{-- ════ DECLARAÇÃO + ASSINATURA ════ --}}
    <div class="declaration">
        DECLARO ACEITAR AS CONDIÇÕES PARTICULARES E AS CONDIÇÕES GERAIS CONSTANTES NO ANEXO DESTE DOCUMENTO.
        O LOCATÁRIO CONFIRMA QUE O VEÍCULO FOI ENTREGUE NAS CONDIÇÕES DESCRITAS NESTE CONTRATO.
    </div>

    <div class="signature-section">
        <div class="signature-label">Assinatura do Locatário:</div>
        <div class="signature-area">
            @if(!empty($data['assinatura']) && $data['assinatura'] !== '<br>' && !is_null($data['assinatura']))
                <img src="{!! $data['assinatura'] !!}" alt="Assinatura">
            @endif
        </div>
    </div>

    {{-- ════ FOOTER ════ --}}
    <div class="doc-footer">
        Documento processado pelo sistema XpandCars &bull; {{ $data['documento']['empresa']['nome'] ?? '' }} &bull; {{ now()->format('d/m/Y') }}
        @if(!empty($data['documento']['empresa']['site']))
            &bull; {{ $data['documento']['empresa']['site'] }}
        @endif
    </div>

</div>
</body>
</html>
