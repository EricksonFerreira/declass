# Central de Ajuda (FAQ)

# Planos e Assinaturas

## O que acontece se eu fizer downgrade?
O downgrade e agendado para a proxima renovacao. Voce mantem o plano atual ate o fim do periodo vigente e, na renovacao, os recursos do novo plano passam a valer.

## O que acontece se eu fizer upgrade?
O upgrade e imediato apos a confirmacao do pagamento. Em upgrades dentro do mesmo periodo, cobramos a diferenca proporcional pelos dias restantes.

## Posso trocar de mensal para anual?
Sim. A troca para anual funciona como upgrade (compromisso maior), com cobranca e ativacao imediata apos o processamento do pagamento.

## Posso trocar de anual para mensal?
Sim, mas a troca anual para mensal e tratada como reducao de compromisso e fica agendada para o fim do periodo de vigencia anual.

## Se eu cancelar, tem estorno?
O cancelamento apenas desativa na proxima renovacao (sem estorno automatico). Excecoes comerciais devem ser tratadas pelo superadmin.

## Como o sistema decide se e upgrade ou downgrade?
O sistema compara o valor total do periodo e as regras de limites. Mudancas que reduzem custos sao downgrades; mudancas que aumentam sao upgrades.

## Como alterar a forma de pagamento da minha assinatura?
Acesse a Central em Planos -> Assinaturas, selecione o plano ativo e clique em Alterar Forma de Pagamento para cadastrar novo cartao ou Pix.

## Onde faco o download da fatura da minha assinatura DeCars?
As faturas ficam arquivadas na aba Financas da Central de Assinaturas, onde e possivel baixar os comprovantes em PDF.

## Existe periodo de testes gratuito (trial)?
Sim. O DeCars oferece 7 dias de avaliacao gratuita nos planos iniciais. Apos esse prazo, a cobranca recorrente e iniciada.

## Como reativar uma assinatura suspensa?
Acesse o painel, quite as faturas em aberto via Pix ou atualize o cartao de credito recusado para restabelecer os acessos.

## Posso adicionar usuarios adicionais no meu plano?
Sim. Cada plano possui um limite de usuarios cadastrados. Upgrades de plano aumentam esse limite no painel administrativo.

## O que sao as cotas de frota nos planos?
E o limite maximo de veiculos que podem ser cadastrados e gerenciados de forma ativa no sistema simultaneamente.

## Como funciona o faturamento recorrente?
O sistema realiza a cobranca automatica no dia correspondente ao ciclo de faturamento (mensal ou anual) contratado.

## Posso alterar o CNPJ da minha assinatura?
Alteracoes de CNPJ contratante exigem suporte administrativo. Abra um chamado anexando o novo cartao CNPJ.

## Quais formas de pagamento sao aceitas?
Aceitamos Cartao de Credito (via Stripe), Pix e Boleto Bancario para assinaturas anuais.

## O sistema cobra alguma taxa sobre as reservas?
Nao. Cobramos apenas o valor fixo da mensalidade ou anuidade da sua assinatura do software.

## Como funciona a cobranca proporcional (proration)?
Ao fazer upgrade no meio do ciclo, voce paga apenas a diferenca dos dias restantes ate a proxima renovacao.

## O que e a Central de Assinaturas?
E a area administrativa de controle financeiro do seu contrato com o software DeCars.

## O cancelamento da assinatura exclui meus dados?
Nao. Seus dados ficam congelados por 90 dias caso queira reativar a conta. Apos esse periodo, sao excluidos por seguranca.

## Posso ter mais de uma empresa cadastrada sob a mesma assinatura?
Alguns planos multi-empresa permitem gerenciar filiais e multiplas empresas com faturamentos isolados.

## Como solicitar alteracao de plano personalizado?
Se sua frota ultrapassa os limites maximos dos planos padroes, entre em contato com nosso time comercial.

## Onde vejo meu consumo de recursos em tempo real?
Na Dashboard inicial do administrador do sistema, ha medidores de cota para frota, usuarios e faturamento.

## Como funciona o faturamento anual parcelado?
O plano anual e cobrado a vista com desconto. Parcelamentos dependem das condicoes da operadora do cartao.

## Se eu exceder o limite de veiculos, o sistema bloqueia?
O sistema impede o cadastro de novos veiculos ate que seja realizado o upgrade de cota ou inativacao de carros antigos.

## Como funciona a renovacao automatica?
Toda assinatura mensal ou anual e renovada automaticamente na data de vencimento, a menos que o cancelamento seja solicitado.

## Como remover um cartao de credito salvo?
Na aba de formas de pagamento da Central de Assinaturas, clique em remover, desde que haja outro metodo cadastrado.

## Posso migrar de um plano individual para multi-empresas?
Sim. A migracao pode ser feita a qualquer momento e o valor ja pago sera creditado proporcionalmente no novo plano.

## Como obter suporte para duvidas financeiras da minha assinatura?
Abra um chamado no menu Suporte selecionando o departamento Financeiro / Faturamento SaaS.

## O que acontece se eu cadastrar uma filial?
O sistema compartilha os limites do seu plano atual entre a matriz e as filiais cadastradas.

## Como aplicar um cupom de desconto na minha assinatura?
Durante o checkout do plano, insira o codigo no campo Cupom de Desconto para recalcular o valor com o abatimento.

# Módulo Fiscal e Reforma

## Como funciona o calculo de impostos do Modulo Fiscal na locacao de veiculos?
De acordo com a Reforma Tributaria (LC 214/2025), a locacao de bens moveis (codigo 99.04.01) sofre incidencia de IBS e CBS.

## Como configurar novas aliquotas do Modulo Fiscal?
As aliquotas sao configuraveis na tabela de regras tributarias com vigencia legal, evitando codificacao rigida (hardcoded).

## Como a NFS-e Nacional e emitida no fechamento da locacao?
Ao finalizar a locacao, o sistema gera a Operacao Fiscal, calcula tributos e agenda o envio via EmitirNfseJob em background.

## Como cancelar uma NFS-e Nacional emitida incorretamente?
Acesse a listagem de Operacoes Fiscais, selecione a nota desejada e clique em Cancelar para enviar a solicitacao ao Portal Nacional.

## O que acontece se uma empresa nao possuir inscricao estadual sob a reforma?
Mesmo sem IE tradicional, a locadora recolhe IBS/CBS de forma centralizada pelo Comite Gestor usando CNPJ.

## O que e a DPS na NFS-e Nacional?
E a Declaracao de Prestacao de Servico, documento enviado ao Portal Nacional que antecede e gera a NFS-e autorizada.

## Como o sistema lida com o imposto retido na fonte?
O motor tributario identifica regras de retencao configuradas e subtrai os valores do repasse liquido ao faturamento.

## Qual o codigo de tributacao nacional para locacao?
O codigo padrao da Reforma Tributaria para locacao de bens moveis e o 99.04.01.

## O que e a aliquota de teste de 2026?
Em 2026, vigora o periodo experimental com aliquotas de 0,9% para CBS e 0,1% para IBS.

## Como funciona a transicao tributaria ate 2033?
O motor do DeCars seleciona as aliquotas vigentes com base na data da operacao, respeitando o calendario de transicao legal.

## Como tratar locacao interestadual (IBS/CBS)?
A tributacao e destinada ao local de consumo do servico ou retirada do bem, calculado automaticamente com base no municipio do tomador.

## O que e a nao cumulatividade no faturamento?
Permite compensar os debitos gerados na locacao com os creditos das compras de insumos e frota.

## Como gerar uma nota complementar?
Crie um faturamento financeiro adicional na reserva e dispare uma nova operacao fiscal complementar.

## Onde visualizo o XML autorizado da NFS-e?
Na listagem de Operacoes Fiscais ou no detalhe da reserva, ha o botao de download do XML assinado.

## O que e o status de contingencia na transmissao?
Estado ativado quando o servidor do governo falha, acumulando as notas na fila para transmissao automatica posterior.

## Como corrigir erros cadastrais que rejeitam a nota?
Corrija o cadastro da empresa ou cliente e clique em Reenviar na tela de Operacoes Fiscais.

## Quem e o tomador do servico na nota fiscal?
E o cliente cadastrado na reserva (CPF ou CNPJ) para quem o veiculo foi alugado.

## O que e o Comite Gestor do IBS?
Orgao centralizador que arrecada e distribui a parcela do IBS para estados e municipios.

## Como funciona a imunidade tributaria de orgaos publicos?
Cadastre uma regra tributaria com aliquota zero e associada ao perfil de cliente orgao publico.

## Como faturar opcionais do tipo servico?
O sistema separa itens de servico puro e gera a correspondente nota municipal ISSQN correspondente.

## Como baixar o PDF da NFS-e Nacional?
No painel de Operacoes Fiscais, selecione a operacao autorizada e clique em Visualizar Nota / Baixar PDF.

## O que e o layout RTC da NFS-e?
E o layout de Regime de Tributacao Cooperativa utilizado na transmissao de notas fiscais nacionais.

## Como configurar o ambiente de homologacao fiscal?
Nas configuracoes da empresa, altere o status de ambiente para homologacao para enviar testes sem valor fiscal.

## Como cadastrar o certificado digital da empresa?
Acesse Configuracoes da Empresa -> Certificado Digital e faca o upload do arquivo PFX (A1) com a respectiva senha.

## O que e o intermediario do servico na nota?
Entidade que intermedia a venda (ex: portais de reserva). O envio desse campo e opcional no XML.

## Como funciona o faturamento para clientes estrangeiros?
O sistema permite emissao informando o passaporte no campo de documento, aplicando as isencoes de exportacao configuradas.

## Como verificar o status de transmissao do lote?
Na dashboard fiscal, ha contadores com o status atualizado de processamento de lotes pendentes.

## O que e o codigo CNAE da locadora?
Classificacao Nacional de Atividades Economicas, sendo 7711-0/00 o codigo padrao para locacao de automoveis.

## Qual a diferenca de IBS e CBS?
CBS e a contribuicao federal e IBS e o imposto sobre bens e servicos de competencia estadual/municipal.

## O motor fiscal calcula impostos sobre multas de transito?
Nao. Multas sao indenizatorias e nao sofrem incidencia fiscal de faturamento de servicos.

# Frota e Veículos

## Como vincular informacoes fiscais (NCM) ao veiculo?
No cadastro do veiculo, selecione a classificacao fiscal (NCM) adequada de acordo com o tipo e cilindrada do bem.

## Como registrar o documento fiscal e os creditos na compra (aquisicao) de um novo veiculo?
Acesse o modulo de Creditos Tributarios e registre a nota de aquisicao com os valores destacados de IBS/CBS.

## Placa e RENAVAM do veiculo sao obrigatorios na NFS-e Nacional da locacao?
Nao. O layout de locacao nacional exige apenas o NCM do bem, descricao e quantidade no XML.

## Como a gestao de frota se beneficia dos creditos tributarios na Reforma?
A compra de frota nova gera creditos de IBS/CBS destacados que amortizam os impostos devidos das diarias.

## Como inativar temporariamente um veiculo para manutencao sem afetar a cobranca do plano?
Altere o status do veiculo para Em Manutencao para retira-lo do painel de locacoes disponiveis.

## Como cadastrar um veiculo novo?
Acesse Cadastro -> Frota -> Novo Veiculo e insira dados de placa, chassi, RENAVAM, cor, modelo e classificacao fiscal.

## Como vincular acessorios permanentes ao veiculo?
Na tela de edicao do veiculo, acesse a aba Acessorios e marque os opcionais fisicamente instalados no carro.

## Como cadastrar uma marca de veiculo?
Acesse Cadastros -> Frota -> Marcas e clique em Nova Marca para salvar.

## Como cadastrar modelos e suas caracteristicas?
Acesse Cadastro -> Modelos, selecione a marca e configure motorizacao, combustivel, numero de portas e portas-malas.

## O que e a depreciacao da frota?
E a perda de valor contabil do veiculo ao longo do tempo. O DeCars gera relatorios de depreciacao linear.

## Como cadastrar a desmobilizacao (venda) de um carro?
No detalhe do veiculo, selecione Vender Veiculo, preencha o valor e a data de venda para gerar a Operacao Fiscal de saida.

## Como gerenciar o estoque de chaves e manuais?
No painel da frota, ha um check de controle para chaves reservas, CRLV fisico e manuais do proprietario.

## Como controlar o vencimento do IPVA dos carros?
O sistema monitora a placa e emite alertas financeiros de vencimento de IPVA na dashboard de frota.

## Como controlar o vencimento do Seguro Obrigatorio (DPVAT)?
Configure a data de vencimento anual no cadastro de seguros do veiculo para alertas no calendario.

## Como funciona a integracao com a tabela FIPE?
O sistema busca o valor medio de mercado do veiculo de forma automatica com base no ano-modelo e versao cadastrados.

## Como associar rastreadores ao veiculo?
No cadastro do carro, preencha o codigo identificador do rastreador e a empresa prestadora do servico.

## Como lançar quilometragem inicial do veiculo?
Ao cadastrar o carro, insira a quilometragem atual do hodometro, que servira de base para todos os checklists futuros.

## Como cadastrar pneus e acompanhar desgaste?
O sistema possui controle de rodizio de pneus vinculando o numero de serie do pneu ao respectivo eixo do veiculo.

## Como gerar relatorio de custos totais por veiculo (TCO)?
O modulo financeiro agrupa despesas de IPVA, seguro, manutencao e multas vinculados ao ID do carro.

## O que e o status Disponivel?
Indica que o veiculo passou pelas vistorias necessarias e esta apto para ser reservado e alugado.

## O que significa status Bloqueado?
Status que impede a locacao do carro (ex: por furto, sinistro grave, bloqueio judicial ou busca e apreensao).

## Como cadastrar locadoras parceiras que cedem frota?
No cadastro de fornecedores, registre a empresa parceira e marque-a como fornecedora de sublocacao de frota.

## Como importar frota em lote via planilha?
Na listagem de veiculos, clique em Importar Planilha, baixe o modelo XLS, preencha os dados e faca o upload.

## Como funciona o rodizio de placas em grandes cidades?
O sistema emite alertas no momento da reserva caso o carro tenha restricoes de circulacao no dia selecionado.

## Como anexar documentos digitais do carro?
Acesse a aba Arquivos no detalhe do veiculo e anexe fotos do CRLV-e, apolice de seguro e nota fiscal.

## O que e a classificacao por grupo de frota?
Metodo que organiza carros similares (ex: Grupo A - Hatch Economico) para facilitar a precificacao e reservas.

## Como cadastrar o consumo medio de combustivel?
Insira a media estimada no cadastro do modelo para que o sistema gere estimativas de despesas nas reservas.

## Como funciona o bloqueio por rastreador integrado?
Ao marcar o veiculo como bloqueado por atraso, o sistema pode enviar o comando de corte via API de rastreadores homologados.

## Onde vejo a taxa de ocupacao da frota?
Na dashboard de frota, ha graficos que mostram o percentual de carros alugados versus o total disponivel.

## Como vincular um veiculo a uma filial (estacao) especifica?
No detalhe do veiculo, selecione a filial de origem correspondente para fins de retirada fisica.

# Reservas e Contratos

## Quando ocorre o calculo de impostos da reserva no faturamento?
Os impostos sao calculados no fechamento/checkout da locacao no faturamento financeiro.

## Como a NFS-e emitida fica vinculada a locacao (reserva) correspondente?
Cada documento fiscal e operacao fiscal sao gravados com o locacao_id, visivel nos detalhes da reserva.

## Posso reabrir uma locacao ja finalizada e refaturar a nota fiscal?
Nao e recomendado. O correto e cancelar a nota fiscal original ou emitir nota corretiva e gerar novo lancamento.

## Como emitir uma nota fiscal complementar para cobrancas extras pos-devolucao?
Crie uma transacao financeira complementar e dispare uma nova operacao fiscal vinculada.

## Como cadastrar uma reserva no sistema?
Acesse Reservas -> Nova Reserva, selecione o cliente, grupo de veiculo, estacao de retirada e devolucao e o periodo.

## Como realizar o check-in (entrega do carro)?
No painel da reserva, clique em Check-in, selecione o veiculo especifico, preencha o hodometro, combustivel e faca o checklist.

## O que e o contrato de locacao?
E o documento juridico assinado pelo cliente que rege as condicoes de aluguel e responsabilidades do bem.

## Como funciona a assinatura digital do contrato?
O DeCars envia um link via SMS/E-mail para o cliente assinar o contrato digitalmente por meio de tokens de seguranca.

## Como alterar as datas de uma reserva ativa?
No painel da reserva, selecione Alterar Periodo, ajuste as datas e recalcule a tarifa proporcional do contrato.

## Como funciona a cobranca de taxa de no-show?
Caso o cliente nao apareca para retirar o carro no horario agendado, o sistema cobra a taxa parametrizada nas regras.

## Posso aplicar cupons de desconto na reserva?
Sim. Insira o cupom valido no campo correspondente no momento da criacao da reserva para reduzir a tarifa.

## Como funciona o overbooking de grupos?
Caso nao haja carro no grupo reservado, o sistema alerta e permite fazer o upgrade gratuito para grupo superior.

## O que e o upgrade de veiculo na entrega?
Entregar um carro de categoria superior a reservada pelo cliente sem cobrar taxas adicionais.

## Como funciona o faturamento faturado (para empresas)?
Permite faturar as reservas consolidadas do mes em lote para faturamento corporativo PJ via boleto.

## Como registrar condutor adicional na reserva?
Na aba Condutores, adicione nome, CNH e documento do motorista autorizado a guiar o veiculo.

## Como o sistema calcula diarias de 24 horas?
A diaria e contada em ciclos de 24h. Atrasos que ultrapassam a tolerancia geram a cobranca de hora extra.

## Como configurar a taxa de retorno de veiculo em outra estacao?
Defina na tabela de tarifas o valor por km de distancia entre as filiais para cobranca de taxa de retorno.

## Como funciona a devolucao rapida?
Modulo express que faz check-out simplificado e enfileira a vistoria de checklist detalhada posterior.

## O que acontece com reservas canceladas pelo cliente?
O sistema cancela a reserva e estorna ou gera credito financeiro de acordo com a politica de cancelamento.

## Como configurar a politica de cancelamento?
Defina nas configuracoes do sistema o prazo limite para cancelamento gratuito e o percentual de multa cobrado.

## Como funciona a locacao corporativa?
Modulo especifico para empresas, onde os funcionarios alugam e a fatura e consolidada no CNPJ da empresa.

## Como gerar o PDF do contrato de locacao?
Na tela da reserva, clique em Imprimir Contrato ou Baixar PDF para salvar ou imprimir o documento assinado.

## O que e a tolerancia de atraso na devolucao?
Tempo de carencia (geralmente 1h) concedido para devolucao sem incidencia de nova diaria.

## Como funciona a cobranca de hora extra?
Cobrada como fracao da diaria (ex: 1/6 da diaria por hora de atraso) ate o limite de uma nova diária completa.

## Como funciona a pre-reserva pelo site?
O cliente faz a solicitacao via portal e o operador aprova no painel administrativo do DeCars.

## Como verificar o historico de modificacoes da reserva?
Na aba Auditoria da reserva, ha logs com data, hora e usuario que realizou alteracoes nos dados.

## Como configurar tarifas sazonais?
Defina faixas de datas (ex: alta temporada/feriados) e configure acrescimos percentuais na tabela de precos.

## Como emitir fatura corporativa consolidada?
No modulo financeiro PJ, selecione as reservas finalizadas do cliente corporativo e clique em Faturar em Lote.

## Como registrar a quilometragem na devolucao?
No check-out da reserva, insira a quilometragem final para que o sistema calcule a quilometragem rodada.

## Como funciona a cobranca de km excedente?
Caso o plano contratado tenha limite de km, o sistema multiplica a diferenca excedente pelo valor de km cadastrado.

# Opcionais e Acessórios

## Quais valores da reserva sao tributados sob o regime de IBS/CBS?
As diarias de locacao do carro sao a base tributavel de IBS/CBS. Opcionais de servico puro seguem regras especificas.

## Como sao tributados os opcionais de locacao (seguros, acessorios, motorista)?
Itens integrados (GPS/cadeirinha) compoem a locacao sob IBS/CBS. Servicos puros (motorista) sofrem incidencia municipal ISSQN.

## Posso cadastrar regras de aliquotas diferentes para opcionais e acessorios?
Sim. As regras tributarias permitem separar aliquotas por tipo de item para garantir tributacao adequada.

## Existe incidencia de IBS/CBS sobre o valor de franquia de coparticipacao em sinistros?
Nao. Valores de ressarcimento de danos sao indenizatorios e isentos de IBS/CBS, sendo emitidos por recibo.

## Como cadastrar um opcional no sistema?
Acesse Configuração -> Opcionais -> Novo Opcional, preencha o nome, tipo, valor diario ou fixo e codigo fiscal.

## Como cobrar opcionais de forma diaria?
Marque a opcao Cobrança Diária no cadastro do opcional para multiplicar o custo pelo numero de diarias da reserva.

## Como cobrar opcionais de forma fixa por contrato?
Marque Cobrança Única no cadastro para cobrar um valor fixo independente da duracao do aluguel.

## O que sao as protecoes de seguro (CDW, LDW)?
Opcionais que limitam a responsabilidade financeira do cliente em casos de colisoes, roubos ou danos.

## Como associar opcionais a um grupo de veiculos especifico?
Na edicao do opcional, selecione quais grupos de frota podem contratar aquele item (ex: bagageiro apenas para SUVs).

## Como gerenciar o estoque fisico de opcionais?
O sistema possui controle de inventario de itens como cadeirinhas de bebe para evitar overbooking de opcionais.

## Como cadastrar protecao para terceiros (ALI)?
Opcional de cobertura contra danos materiais e corporais causados a terceiros durante a locacao.

## Como funciona a isencao de opcionais em planos mensais?
Voce pode parametrizar descontos de ate 100% em opcionais para locacoes de longa duracao.

## Como faturar opcionais de forma isolada?
Crie um lancamento financeiro de venda avulsa e emita o documento fiscal correspondente.

## Opcionais geram comissao para atendentes?
Sim. O sistema permite configurar percentual de comissao de vendas de opcionais para funcionarios do balcao.

## Como vincular um motorista como opcional de servico?
Cadastre o servico Motorista Particular no menu de Opcionais e configure a aliquota municipal ISSQN.

## Como funciona a cobranca de taxa de higienizacao do carro?
Lançada no check-out caso o carro retorne excessivamente sujo, podendo seguir a aliquota municipal de lavagem.

## Opcionais podem ter valores diferenciados por estacao?
Sim. E possivel criar tabelas de precos de opcionais especificas para cada filial da locadora.

## Como funciona o seguro de assistencia 24h?
Opcional que garante guincho e chaveiro ilimitados, faturado de forma integrada com a diaria de locacao.

## Como gerenciar danos causados a opcionais alugados?
O checklist de devolucao inclui a inspecao do estado de itens como GPS e cadeirinhas de bebe.

## Opcionais podem ser obrigatorios por lei?
Sim. Voce pode marcar opcionais como obrigatorios para determinados grupos (ex: cadeirinha obrigatoria para motoristas com criancas).

## Como o motor tributario calcula IBS/CBS sobre protecoes?
Protecoes integradas a locacao de bens moveis entram na mesma base de calculo de saida (IBS/CBS).

## Como aplicar descontos apenas nos opcionais?
Na edicao da reserva, aplique o desconto percentual diretamente no item opcional selecionado.

## Como rastrear o uso de opcionais especificos?
O DeCars gera relatorios de lucratividade e frequencia de contratacao de cada opcional da locadora.

## Como cadastrar opcionais de conveniencia (ex: ConectCar/Sem Parar)?
Cadastre o TAG de pedagio como opcional fixo e repasse o consumo de uso posteriormente.

## Opcionais de pedagio sofrem incidencia fiscal?
O repasse de pedagio e reembolso de custos nao tributavel. A taxa de servico de administracao do TAG sofre ISSQN.

## Como lidar com a quebra de um acessorio (ex: quebra do GPS)?
 Lance a avaria no checklist de devolucao utilizando a tabela de ressarcimento de acessorios.

## Posso restringir opcionais por perfil de cliente?
Sim. E possivel ocultar opcionais de motorista particular para perfis de locacao executiva corporativa.

## Como funciona a entrega e coleta de veiculos a domicilio (Delivery)?
Cadastre o servico de Delivery como opcional de valor unico para cobranca da taxa de frete/servico.

## Como o sistema emite nota fiscal sobre o delivery?
O delivery e caracterizado como servico de transporte municipal, sofrendo incidencia de ISS municipal.

## Onde configuro as comissoes de opcionais?
Acesse Configuração -> Comissões de Vendas de Opcionais e defina as metas por atendente de balcao.

# Finanças e Faturamento

## Como sao gerados os creditos tributarios de IBS/CBS?
Os impostos recolhidos nas compras de frota, pecas e servicos sao creditados no painel fiscal para deducao futura.

## Como funciona o Split Payment (pagamento dividido) da Reforma Tributária?
O imposto e retido no pagamento eletrônico e enviado aos cofres publicos. O DeCars provisiona o saldo liquido correspondente.

## Como os impostos calculados afetam o Fluxo de Caixa da locadora?
Os impostos calculados sao provisionados como despesas fiscais futuras, otimizando o planejamento financeiro mensal.

## Como e feita a reconciliacao dos debitos e creditos fiscais no painel financeiro?
O painel calcula o Saldo Estimado subtraindo creditos acumulados dos debitos gerados.

## Como emitir um recibo/fatura de locacao de bens moveis (fatura de locacao) em vez de NFS-e?
Se a legislacao municipal nao exigir nota, o DeCars permite gerar a Fatura de Locacao direto pelo painel.

## O que e a conciliacao bancaria de cartoes?
Fluxo que compara os lancamentos financeiros no DeCars com o extrato de repasses das adquirentes.

## Como estornar um pagamento Pix?
No financeiro da transacao, clique em Estornar Pix para enviar a ordem de devolucao via gateway.

## Como cadastrar contas bancarias?
Acesse Financeiro -> Contas Bancarias -> Nova Conta e cadastre os dados da conta corrente.

## Como funciona o fluxo de contas a pagar?
Modulo para registrar despesas operacionais da locadora (aluguel de filiais, folha de pagamento, energia).

## Como funciona o fluxo de contas a receber?
Controla faturas corporativas pendentes e cartoes de credito a compensar.

## Como lidar com a inadimplencia de clientes?
O sistema envia cobrancas automaticas via WhatsApp e e-mail e bloqueia novas locacoes do cliente.

## O que e o plano de contas?
Estrutura de categorias de receitas e despesas que organiza o DRE financeiro da empresa.

## Como gerar o Demonstrativo de Resultados (DRE)?
Acesse Financeiro -> Relatorios -> DRE para visualizar receitas, despesas, impostos e o lucro liquido.

## Como funciona o fechamento de caixa diario?
O atendente do balcao fecha o turno e o sistema consolida dinheiro, cheques, cartoes e Pix recebidos.

## Como cadastrar as taxas das maquininhas de cartao?
Acesse Financeiro -> Gateways -> Taxas e insira os percentuais cobrados de debito, credito e parcelamento.

## Como cadastrar pagamentos parcelados?
O sistema desdobra o recebimento em parcelas mensais de acordo com as datas de vencimento da operadora.

## Como importar o extrato bancario (OFX)?
No painel de conciliacao, faca o upload do arquivo OFX do banco para conciliar as transacoes automaticamente.

## O que sao as taxas de cobranca bancaria?
Custos cobrados pelos bancos para emissao de boletos, parametrizados no modulo financeiro.

## Como cadastrar descontos financeiros?
Abatimentos concedidos por pagamentos antecipados, lancados como descontos no contas a receber.

## Como funciona a cobranca de juros e multa por atraso?
Configure os percentuais no sistema para recalcular boletos vencidos automaticamente.

## Onde vejo a DFC (Demonstracao do Fluxo de Caixa)?
Acesse Financeiro -> Fluxo de Caixa para ver as entradas e saidas reais no periodo.

## Como lidar com o chargeback de cartoes?
Registre o chargeback na transacao para estornar o contas a receber e marcar o cliente como inadimplente.

## Como gerar relatorio de inadimplencia?
Acesse Financeiro -> Relatorios -> Clientes Inadimplentes com saldos vencidos ha mais de X dias.

## Como funciona a integracao com o gateway Stripe?
O DeCars envia as ordens de pagamento via API Stripe para processamento seguro de cartoes de credito e Pix.

## Como configurar recebimento via Pix automatico?
Insira as credenciais do seu gateway Pix homologado nas configuracoes de integracao financeira.

## Como funciona a emissao de boletos registrados?
O sistema gera o boleto com registro imediato no banco do cliente para permitir o pagamento no mesmo instante.

## Como tratar comissoes de intermediarios (parceiros)?
Lance a comissao como despesa de intermediacao associada a respectiva reserva no financeiro.

## Como provisionar custos futuros de manutencao?
Cadastre provisoes no contas a pagar estimando os custos de revisao programada da frota.

## O que e a retencao de impostos federais (PIS/COFINS/CSLL)?
Ocorre em faturamentos PJ corporativos onde o cliente retem na fonte os impostos federais.

## Como conciliar taxas de antecipacao de cartao?
Lance a diferenca da taxa de antecipacao como despesa financeira na conciliacao do extrato da adquirente.

# Suporte e Resolução de Problemas

## Como proceder em caso de erro ou rejeicao na emissao da NFS-e Nacional?
Verifique a mensagem de erro nas Operacoes Fiscais, corrija os dados cadastrais e clique em Reenviar.

## Como abrir um chamado de suporte técnico para o Modulo Fiscal?
Acesse Suporte -> Criar chamado, relate o erro e anexe a ID da reserva ou operacao fiscal correspondente.

## Quais sao os principais erros de rejeicao da NFS-e Nacional e como resolvê-los?
Erros comuns incluem CPF/CNPJ de tomador invalido (Corrija o cadastro) e NCM inadequado (Ajuste o NCM do carro).

## Qual e o tempo médio de resposta para chamados de suporte técnico fiscal?
Nosso suporte tecnico especializado responde chamados urgentes em ate 2 horas úteis.

## Como abrir um chamado geral de suporte?
Acesse o menu Suporte -> Criar chamado, selecione a categoria desejada e preencha a descricao do problema.

## Onde vejo o status dos meus chamados?
Na tela Suporte -> Meus chamados, ha a lista com todos os chamados abertos e o historico de interacoes.

## Como funciona o atendimento em tempo real (Chat)?
Alguns planos corporativos incluem suporte via chat em tempo real direto pelo painel de controle.

## O que e o log de erros do sistema?
Registro interno de falhas tecnicas. O suporte do DeCars usa esses logs para identificar bugs no servidor.

## Como exportar logs de erro de transmissao fiscal?
No detalhe da nota com erro, clique em Ver Log Completo para copiar ou baixar o JSON de resposta da API.

## Como resolver lentidao no carregamento das telas?
Limpe o cache do seu navegador e verifique a qualidade da sua conexao com a internet.

## O sistema possui modo de homologacao para treinamento?
Sim. Voce pode solicitar um ambiente Sandbox com dados ficticios para treinar novos atendentes.

## Onde encontro os manuais e videos de treinamento?
Na Central de Ajuda, ha links para a base de conhecimento com artigos e videoaulas explicativas.

## Como redefinir a senha de um usuario do sistema?
O administrador pode acessar Configuracoes -> Usuarios, selecionar o perfil e clicar em Resetar Senha.

## O que fazer se o sistema estiver fora do ar?
Consulte nossa pagina de status (status.decars.com) para verificar se ha instabilidades em nossos servidores.

## Como reportar um bug no sistema?
Abra um chamado selecionando a categoria Reportar Bug, descrevendo o passo a passo para reproduzir a falha.

## Como solicitar novas funcionalidades no software?
Sugestoes podem ser enviadas via chamado na categoria Sugestao de Melhoria para avaliacao do time de produto.

## Como funciona o backup dos meus dados?
O DeCars realiza backups automaticos diarios e criptografados de todas as bases de dados dos clientes.

## Meus dados fiscais estao seguros de acordo com a LGPD?
Sim. O sistema criptografa dados sensiveis e segue as diretrizes da Lei Geral de Protecao de Dados.

## Como funciona o suporte nos fins de semana?
O suporte funciona em regime de plantao de sobreaviso aos finais de semana e feriados para chamados de alta prioridade.

## O que sao chamados criticos (Bloqueantes)?
Chamados que impedem a operacao do sistema (ex: incapacidade de fazer check-in ou checkout).

## Como resolver erros de permissao de usuario?
O superadmin deve acessar as configuracoes de perfil do usuario e conceder a permissao de acesso desejada.

## O sistema funciona em dispositivos moveis (smartphones)?
Sim. O painel e responsivo e otimizado para rodar em celulares e tablets Android ou iOS.

## Como resolver erros de integracao com a Stripe?
Verifique se as chaves de API estao ativas nas configuracoes de integracao financeira do painel.

## O que fazer se o Pix nao for confirmado automaticamente?
Acesse o financeiro da reserva, verifique o status no gateway e clique em Confirmar Manualmente se necessario.

## Como alterar os dados cadastrais da minha locadora?
Acesse Configuracoes -> Minha Empresa para atualizar telefone, e-mail e endereco comercial.

## Como resolver o erro XML invalido na NFS-e?
Verifique se a descricao do servico ou dados cadastrais nao possuem caracteres especiais nao suportados.

## Como atualizar a versao do sistema?
As atualizacoes sao aplicadas automaticamente em background pelo time de engenharia da plataforma.

## Onde vejo o historico de atualizacoes (Changelog)?
Na aba novidades da Dashboard, listamos os novos recursos e correcoes aplicadas a cada versao.

## Como exportar todos os dados da minha locadora?
Administradores podem solicitar a exportacao completa do banco de dados em formato SQL/CSV via chamado.

## Como entrar em contato com a ouvidoria do DeCars?
Caso tenha reclamacoes sobre o atendimento, envie e-mail diretamente para ouvidoria@decars.com.

