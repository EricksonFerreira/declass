<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        {!! $css ?? '' !!}
        * { box-sizing:border-box; margin:0; padding:0; }
        @page { size:A4 portrait; margin:10mm; }
        body { font-family:Arial,sans-serif; font-size:10px; color:#1a2035; background:#fff; padding:20px; }
        .footer { width:100%; margin-top:20px; padding-top:8px; border-top:1px solid #ccc; font-size:9px; color:#666; }
        .footer-table { width:100%; }
        .document { width:100%; max-width:190mm; margin:0 auto; }
        .header { width:100%; margin-bottom:15px; border-bottom:2px solid #2563eb; padding-bottom:10px; }
        .header-table { width:100%; }
        .logo-area { width:40%; vertical-align:top; }
        .logo { max-height:60px; max-width:180px; }
        .company-name { font-size:14px; font-weight:700; color:#1e3a8a; }
        .company-meta { font-size:9px; color:#666; line-height:1.4; margin-top:4px; }
        .contract-title-area { width:60%; text-align:right; vertical-align:top; }
        .contract-title { font-size:16px; font-weight:900; color:#1e3a8a; letter-spacing:.05em; text-transform:uppercase; }
        .contract-subtitle { font-size:10px; color:#475569; margin-top:3px; }
        .contract-number { display:inline-block; margin-top:6px; background:#1e3a8a; color:#fff; font-weight:800; font-size:11px; padding:3px 14px; border-radius:20px; }
        .info-strip { width:100%; margin-bottom:12px; }
        .info-strip td { padding:8px 10px; background:#eff6ff; border:1px solid #bfdbfe; }
        .info-label { font-size:9px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:.05em; }
        .info-value { font-size:13px; font-weight:600; color:#1e3a8a; }
        .grid-2 { width:100%; }
        .grid-2 td { width:50%; vertical-align:top; padding:0 5px; }
        .section { margin-bottom:12px; }
        .card { border:1px solid #e2e8f0; border-radius:6px; overflow:hidden; }
        .card-highlight { border-left:3px solid #2563eb; }
        .section-title { font-size:10px; font-weight:800; color:#1e3a8a; padding:8px 10px; background:#f8fafc; border-bottom:1px solid #e2e8f0; text-transform:uppercase; letter-spacing:.08em; }
        .section-body { padding:8px 10px; }
        .fields-table { width:100%; }
        .fields-table td { padding:4px 5px; }
        .field-label { font-size:9px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.04em; }
        .field-value { font-size:11px; color:#0f172a; font-weight:500; border-bottom:1px solid #e2e8f0; padding-bottom:2px; min-height:16px; }
        .badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:9px; font-weight:700; }
        .badge-success { background:#dcfce7; color:#16a34a; }
        .refuel-info { margin-top:10px; }
        .refuel-info td { padding:4px 5px; }
        .refuel-label { font-size:9px; font-weight:700; color:#64748b; text-transform:uppercase; }
        .refuel-value { font-size:11px; color:#0f172a; font-weight:500; }
        .total-box { margin-top:10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:5px; padding:8px 12px; }
        .total-row { display:flex; justify-content:space-between; padding:3px 0; }
        .total-label { font-size:10px; color:#475569; }
        .total-value { font-size:11px; font-weight:700; color:#1e3a8a; text-align:right; }
        .opcionais-table { width:100%; border-collapse:collapse; margin-top:5px; }
        .opcionais-table th { background:#1e3a8a; color:#fff; font-size:9px; font-weight:700; padding:5px 8px; text-align:left; }
        .opcionais-table td { padding:4px 8px; font-size:10px; border-bottom:1px solid #e2e8f0; }
        .data-table { width:100%; border-collapse:collapse; margin-top:5px; }
        .data-table th { background:#e2e8f0; font-size:9px; font-weight:800; padding:5px 8px; text-align:left; border:1px solid #cbd5e1; }
        .data-table td { padding:4px 8px; font-size:10px; border:1px solid #e2e8f0; }
        .pulse { background:#fffbeb; border:1px solid #fde68a; border-radius:5px; padding:8px 12px; margin:8px 0; }
        .danger-box { background:#fef2f2; border:1px solid #fecaca; border-radius:5px; padding:8px 12px; margin:8px 0; }
        .danger-title { font-size:11px; font-weight:700; color:#991b1b; }
        .danger-text { font-size:10px; color:#7f1d1d; }
        .success-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:5px; padding:8px 12px; margin:8px 0; }
        .success-text { font-size:10px; color:#166534; }
        .signature-grid { width:100%; margin-top:20px; }
        .signature-grid td { width:50%; text-align:center; vertical-align:bottom; padding:0 15px; }
        .signature-line { border-bottom:2px solid #1e3a8a; height:60px; margin-bottom:5px; }
        .signature-line img { max-height:55px; max-width:200px; }
        .signature-name { font-size:11px; font-weight:700; color:#0f172a; }
        .signature-role { font-size:9px; color:#64748b; margin-top:2px; }
        .signature-doc { font-size:9px; color:#64748b; margin-top:1px; }
        .page-break { page-break-before:always; }
        .clause-box { font-size:10px; line-height:1.5; color:#334155; }
        .clause-box p { margin-bottom:8px; text-align:justify; }
    </style>
</head>
<body>

<div class="footer">
    <table class="footer-table">
        <tr>
            <td>
                Documento processado pelo sistema DEcars •
                {{ data_get($data, 'documento.empresa.nome', '-') }}
            </td>
            <td style="text-align: right;">
                Emitido em {{ data_get($data, 'documento.data_emissao', '-') }}
            </td>
        </tr>
    </table>
</div>

<div class="document">

    {{-- CABEÇALHO --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-area">
                    @if(data_get($data, 'documento.empresa.logo_url'))
                        <img class="logo" src="{{ data_get($data, 'documento.empresa.logo_url') }}">
                    @else
                        <div class="company-name">{{ data_get($data, 'documento.empresa.nome', 'Locadora') }}</div>
                    @endif

                    <div class="company-meta">
                        {{ data_get($data, 'documento.empresa.endereco', '-') }}<br>
                        {{ data_get($data, 'documento.empresa.site', '-') }}
                    </div>
                </td>

                <td class="contract-title-area">
                    <div class="contract-title">{{ data_get($data, 'contrato.titulo', 'Contrato de Aluguel') }}</div>
                    <div class="contract-subtitle">{{ data_get($data, 'contrato.subtitulo', 'Condições Particulares') }}</div>
                    <div class="contract-number">{{ data_get($data, 'contrato.numero', '-') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- FAIXA RESUMO --}}
    <table class="info-strip">
        <tr>
            <td>
                <div class="info-label">Cliente</div>
                <div class="info-value">{{ data_get($data, 'cliente.nome', '-') }}</div>
            </td>
            <td>
                <div class="info-label">Veículo</div>
                <div class="info-value">
                    {{ data_get($data, 'veiculo.marca', '-') }} {{ data_get($data, 'veiculo.modelo', '-') }}
                </div>
            </td>
            <td>
                <div class="info-label">Período</div>
                <div class="info-value">
                    {{ data_get($data, 'reserva.alugado_em.data', '-') }}
                    a
                    {{ data_get($data, 'reserva.devolver_em.data', '-') }}
                </div>
            </td>
            <td>
                <div class="info-label">Total</div>
                <div class="info-value">{{ data_get($data, 'faturacao.valor_total', '-') }}</div>
            </td>
        </tr>
    </table>

    {{-- CLIENTE + LOCAÇÃO --}}
    <table class="grid-2">
        <tr>
            <td>
                <div class="section card">
                    <div class="section-title">Dados do Cliente</div>
                    <div class="section-body">
                        <table class="fields-table">
                            <tr>
                                <td colspan="2">
                                    <div class="field-label">Nome completo</div>
                                    <div class="field-value">{{ data_get($data, 'cliente.nome', '-') }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="field-label">CPF / NIF</div>
                                    <div class="field-value">{{ data_get($data, 'cliente.nif', '-') }}</div>
                                </td>
                                <td>
                                    <div class="field-label">Passaporte</div>
                                    <div class="field-value">{{ data_get($data, 'cliente.passaporte', '-') }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="field-label">E-mail</div>
                                    <div class="field-value">{{ data_get($data, 'cliente.email', '-') }}</div>
                                </td>
                                <td>
                                    <div class="field-label">Telefone</div>
                                    <div class="field-value">{{ data_get($data, 'cliente.telefone', '-') }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="field-label">Data de nascimento</div>
                                    <div class="field-value">{{ data_get($data, 'cliente.data_nascimento', '-') }}</div>
                                </td>
                                <td>
                                    <div class="field-label">País</div>
                                    <div class="field-value">{{ data_get($data, 'cliente.pais', '-') }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="field-label">Endereço</div>
                                    <div class="field-value">{{ data_get($data, 'cliente.endereco', '-') }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="field-label">CNH / Carta de condução</div>
                                    <div class="field-value">{{ data_get($data, 'cliente.carta_conducao.numero', '-') }}</div>
                                </td>
                                <td>
                                    <div class="field-label">Validade</div>
                                    <div class="field-value">{{ data_get($data, 'cliente.carta_conducao.validade', '-') }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>

            <td>
                <div class="section card">
                    <div class="section-title">Dados da Locação</div>
                    <div class="section-body">
                        <table class="fields-table">
                            <tr>
                                <td>
                                    <div class="field-label">Local de retirada</div>
                                    <div class="field-value">{{ data_get($data, 'reserva.alugado_em.local', '-') }}</div>
                                </td>
                                <td>
                                    <div class="field-label">Data / Hora</div>
                                    <div class="field-value">
                                        {{ data_get($data, 'reserva.alugado_em.data', '-') }}
                                        {{ data_get($data, 'reserva.alugado_em.hora', '-') }}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="field-label">Local de devolução</div>
                                    <div class="field-value">{{ data_get($data, 'reserva.devolver_em.local', '-') }}</div>
                                </td>
                                <td>
                                    <div class="field-label">Data / Hora</div>
                                    <div class="field-value">
                                        {{ data_get($data, 'reserva.devolver_em.data', '-') }}
                                        {{ data_get($data, 'reserva.devolver_em.hora', '-') }}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="field-label">Status</div>
                                    <div class="field-value">
                                        <span class="badge badge-success">{{ data_get($data, 'reserva.status', '-') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="field-label">Pagamento</div>
                                    <div class="field-value">{{ data_get($data, 'reserva.pagamento_efetuado', '-') }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="field-label">Observações</div>
                                    <div class="field-value">{{ data_get($data, 'reserva.observacoes', '-') }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="section card card-highlight">
                    <div class="section-title">Veículo</div>
                    <div class="section-body">
                        <table class="vehicle-summary">
                            <tr>
                                <td colspan="3">
                                    <div class="vehicle-main">
                                        {{ data_get($data, 'veiculo.marca', '-') }}
                                        {{ data_get($data, 'veiculo.modelo', '-') }}
                                    </div>
                                    <div class="vehicle-sub">
                                        Grupo {{ data_get($data, 'veiculo.grupo', '-') }}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="field-label">Placa / Matrícula</div>
                                    <div class="field-value">{{ data_get($data, 'veiculo.matricula', '-') }}</div>
                                </td>
                                <td>
                                    <div class="field-label">Km</div>
                                    <div class="field-value">{{ data_get($data, 'veiculo.quilometros', '-') }}</div>
                                </td>
                                <td>
                                    <div class="field-label">Combustível</div>
                                    <div class="field-value">{{ data_get($data, 'veiculo.combustivel', '-') }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- VALORES --}}
    <div class="section card">
        <div class="section-title">Resumo Financeiro</div>
        <div class="section-body">
            <table class="data-table">
                <tr>
                    <th>Descrição</th>
                    <th class="text-right">Valor</th>
                </tr>
                <tr>
                    <td>Grupo reservado</td>
                    <td class="text-right">{{ data_get($data, 'financeiro_detalhado.grupo', '-') }}</td>
                </tr>
                <tr>
                    <td>Tarifa diária</td>
                    <td class="text-right money">{{ data_get($data, 'financeiro_detalhado.tarifa_diaria', '-') }}</td>
                </tr>
                <tr>
                    <td>{{ data_get($data, 'financeiro_detalhado.dias_label', 'Diárias') }}</td>
                    <td class="text-right money">{{ data_get($data, 'financeiro_detalhado.valor_tarifa_base', '-') }}</td>
                </tr>
                <tr>
                    <td>Opcionais</td>
                    <td class="text-right money">{{ data_get($data, 'financeiro_detalhado.opcionais', '-') }}</td>
                </tr>
                <tr>
                    <td>Retirada fora do expediente</td>
                    <td class="text-right money">{{ data_get($data, 'financeiro_detalhado.taxa_coleta', '-') }}</td>
                </tr>
                <tr>
                    <td>Devolução fora do expediente</td>
                    <td class="text-right money">{{ data_get($data, 'financeiro_detalhado.taxa_entrega', '-') }}</td>
                </tr>
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right money">{{ data_get($data, 'financeiro_detalhado.subtotal', '-') }}</td>
                </tr>
                <tr>
                    <td>Desconto {{ data_get($data, 'financeiro_detalhado.desconto_label', '') }}</td>
                    <td class="text-right money">- {{ data_get($data, 'financeiro_detalhado.desconto_valor', '-') }}</td>
                </tr>
                <tr>
                    <td><strong>Total do contrato</strong></td>
                    <td class="text-right money"><strong>{{ data_get($data, 'financeiro_detalhado.total', '-') }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- PAGAMENTOS --}}
    @if(!empty($data['pagamentos']))
        <div class="section card">
            <div class="section-title">Pagamentos e Transações</div>
            <div class="section-body">
                <table class="data-table">
                    <tr>
                        <th>Data</th>
                        <th>Valor</th>
                        <th>Método</th>
                        <th>Status</th>
                        <th>Confirmado em</th>
                        <th>Pago por</th>
                    </tr>

                    @foreach($data['pagamentos'] as $pagamento)
                        <tr>
                            <td>{{ $pagamento['data'] ?? '-' }}</td>
                            <td class="money">{{ $pagamento['valor'] ?? '-' }}</td>
                            <td>{{ $pagamento['metodo'] ?? '-' }}</td>
                            <td>{{ $pagamento['status'] ?? '-' }}</td>
                            <td>{{ $pagamento['confirmado_em'] ?? '-' }}</td>
                            <td>{{ $pagamento['pago_por'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif

    {{-- DANOS --}}
    @if(!empty($data['danos_detalhados']))
        <div class="section card">
            <div class="section-title">Danos e Avarias</div>
            <div class="section-body">
                <table class="data-table">
                    <tr>
                        <th>Local</th>
                        <th>Descrição</th>
                        <th>Tipo / Status</th>
                        <th>Custo</th>
                        <th>Correção</th>
                    </tr>

                    @foreach($data['danos_detalhados'] as $dano)
                        <tr>
                            <td>
                                <strong>{{ $dano['localizacao'] ?? '-' }}</strong><br>
                                {{ $dano['categoria'] ?? '-' }}<br>
                                <small>{{ $dano['registrado_em'] ?? '-' }}</small>
                            </td>
                            <td>
                                {{ $dano['localizacao_problema'] ?? '-' }}<br>
                                <small>KM: {{ $dano['quilometragem'] ?? '-' }}</small>
                            </td>
                            <td>
                                {{ $dano['tipo_dano'] ?? '-' }}<br>
                                @if(($dano['status'] ?? '') === 'Corrigido')
                                    <span class="badge badge-success">Corrigido</span>
                                @else
                                    <span class="badge badge-warning">Pendente</span>
                                @endif
                            </td>
                            <td class="money">{{ $dano['custo_estimado'] ?? '-' }}</td>
                            <td>{{ $dano['correcao'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif

    {{-- FATURAÇÃO FINAL + RESPONSÁVEL --}}
    <table class="grid-2">
        <tr>
            <td>
                <div class="section card">
                    <div class="section-title">Funcionário Responsável</div>
                    <div class="section-body">
                        <table class="fields-table">
                            <tr>
                                <td>
                                    <div class="field-label">Nome</div>
                                    <div class="field-value">{{ data_get($data, 'funcionario.nome', '-') }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="field-label">Data de emissão</div>
                                    <div class="field-value">{{ data_get($data, 'documento.data_emissao', '-') }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>

            <td>
                <div class="total-box">
                    <table class="total-row">
                        <tr>
                            <td class="total-label">Dias / Unidades</td>
                            <td class="total-value">{{ data_get($data, 'faturacao.dias_unidades', '-') }}</td>
                        </tr>
                        <tr>
                            <td class="total-label">Impostos / IVA</td>
                            <td class="total-value">{{ data_get($data, 'faturacao.iva_total', '-') }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td class="total-label">Total</td>
                            <td class="total-value">{{ data_get($data, 'faturacao.valor_total', '-') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="declaration">
        O LOCATÁRIO declara que recebeu o veículo nas condições descritas neste contrato e no laudo de vistoria,
        comprometendo-se a devolvê-lo no mesmo estado de conservação, ressalvado o desgaste natural pelo uso regular.
        Declara, ainda, ter lido, compreendido e aceitado as Condições Particulares, Condições Gerais, regras de caução,
        franquias, avarias, multas, combustível, quilometragem e demais encargos aplicáveis.
    </div>

    {{-- ASSINATURAS --}}
    <div class="signature-page">
        <table class="signature-grid">
            <tr>
                <td>
                    @if(!empty($data['assinatura']))
                        <img src="{{ $data['assinatura'] }}" style="max-height: 80px; max-width: 220px;">
                    @endif

                    <div class="signature-line">
                        {{ data_get($data, 'cliente.nome', '-') }}
                    </div>
                    <div class="signature-meta">
                        Locatário<br>
                        Documento: {{ data_get($data, 'cliente.nif', '-') }}
                    </div>
                </td>

                <td>
                    <div class="signature-line">
                        {{ data_get($data, 'funcionario.nome', '-') }}
                    </div>
                    <div class="signature-meta">
                        Atendente / Responsável<br>
                        Data: {{ data_get($data, 'documento.data_emissao', '-') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- CONDIÇÕES GERAIS --}}
    <div class="page-break terms">
        <div class="terms-title">Condições Gerais da Locação</div>

        <p><strong>1. Uso do veículo.</strong> O LOCATÁRIO deverá utilizar o veículo de forma regular, prudente e em conformidade com a legislação de trânsito vigente, sendo vedado o uso para fins ilícitos, competições, reboque, sublocação, transporte irregular, condução por pessoa não autorizada ou qualquer finalidade diversa daquela contratada.</p>

        <p><strong>2. Entrega e devolução.</strong> O veículo deverá ser devolvido na data, hora e local contratados, no mesmo estado em que foi entregue, ressalvado o desgaste natural decorrente do uso regular.</p>

        <p><strong>3. Atraso na devolução.</strong> A devolução após o prazo contratado poderá gerar cobrança de diárias adicionais, taxas operacionais, diferença tarifária e demais encargos previstos nas Condições Particulares.</p>

        <p><strong>4. Multas, pedágios e infrações.</strong> O LOCATÁRIO será integralmente responsável por multas, infrações de trânsito, pedágios, estacionamentos, remoções, apreensões, taxas administrativas e demais encargos ocorridos durante o período da locação.</p>

        <p><strong>5. Danos e avarias.</strong> Danos identificados na devolução e não registrados na vistoria de retirada poderão ser cobrados do LOCATÁRIO, conforme orçamento, tabela interna, avaliação técnica ou documento equivalente.</p>

        <p><strong>6. Combustível.</strong> O veículo deverá ser devolvido com o mesmo nível de combustível informado na retirada. Havendo diferença, poderão ser cobrados o combustível faltante e a taxa de reabastecimento aplicável.</p>

        <p><strong>7. Quilometragem.</strong> Quando houver limite de quilometragem, o LOCATÁRIO será responsável pelo pagamento dos quilômetros excedentes, conforme valor definido nas Condições Particulares.</p>

        <p><strong>8. Caução e autorização de cobrança.</strong> O LOCATÁRIO autoriza a LOCADORA a realizar bloqueio caução, pré-autorização ou retenção de valores, bem como a efetuar cobranças referentes a diárias, taxas, multas, avarias, combustível, limpeza, quilometragem excedente, franquias, coparticipações e demais encargos previstos neste contrato.</p>

        <p><strong>9. Acidente, roubo, furto ou pane.</strong> O LOCATÁRIO deverá comunicar imediatamente a LOCADORA em caso de acidente, roubo, furto, pane, apreensão ou qualquer ocorrência envolvendo o veículo, apresentando boletim de ocorrência quando aplicável.</p>

        <p><strong>10. Proteções e franquias.</strong> As proteções contratadas, franquias, coparticipações e limites de cobertura serão aqueles descritos nas Condições Particulares.</p>

        <p><strong>11. Limpeza e conservação.</strong> O veículo deverá ser devolvido em condições normais de limpeza e conservação. Sujeira excessiva, mau uso, odores, manchas, danos internos ou necessidade de higienização especial poderão gerar cobrança adicional.</p>

        <p><strong>12. Declaração final.</strong> Ao assinar este contrato, o LOCATÁRIO declara ter lido, compreendido e aceitado as Condições Particulares, as Condições Gerais, o laudo de vistoria, as regras de cobrança, as políticas de uso do veículo e demais documentos vinculados à locação.</p>
    </div>

</div>
</body>
</html>