<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        {!! $css !!}
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