# Planos e Assinaturas

## O que acontece se eu fizer downgrade?
O downgrade e **agendado para a proxima renovacao**. Voce mantem o plano atual ate o fim do periodo vigente e, na renovacao, os recursos do novo plano passam a valer.

Se o novo plano tiver limite menor (ex.: limite de veiculos), pode ser necessario desativar veiculos antes de confirmar o downgrade.

## O que acontece se eu fizer upgrade?
O upgrade e **imediato apos a confirmacao do pagamento**. Em upgrades dentro do mesmo periodo, cobramos a diferenca proporcional pelos dias restantes (proration).

Em troca **mensal -> anual**, cobramos o valor anual (a vista) com credito proporcional do que ainda restava do mes.

## Posso trocar de mensal para anual?
Sim. A troca para anual funciona como upgrade (compromisso maior), com cobranca e ativacao apos pagamento.

## Posso trocar de anual para mensal?
Sim, mas a troca **anual -> mensal** e tratada como reducao de compromisso e por isso fica **agendada para o fim do periodo anual**.

## Se eu cancelar, tem estorno?
Por padrao SaaS, o cancelamento apenas **desativa na proxima renovacao** (sem estorno automatico). Se houver excecao (ex.: negociacao comercial), o superadmin pode ajustar manualmente.

## Como o sistema decide se e upgrade ou downgrade?
O sistema compara o **valor total do periodo** (mensal x anual) e as **regras de limite/recursos**. Mudancas que reduzem custo/limites tendem a ser downgrade (agendado). Mudancas que aumentam custo/compromisso tendem a ser upgrade (cobranca/ativacao imediata).

# Módulo Fiscal e Reforma

## Como funciona o calculo de impostos do Modulo Fiscal na locacao de veiculos?
De acordo com a **Reforma Tributaria (Lei Complementar 214/2025)**, a locacao de bens moveis (codigo `99.04.01`) passa a sofrer a incidencia do **IBS** e da **CBS**. O Modulo Fiscal de DeCars desacopla o calculo dos contratos e reservas, registrando o fato gerador (Operacao Fiscal) no fechamento da locacao e calculando os impostos aplicaveis de acordo com a vigencia legal.

## Como configurar novas aliquotas do Modulo Fiscal?
As aliquotas sao configuraveis na tabela de regras tributarias (`regras_tributarias`) e possuem datas de vigencia (inicio e fim). Em 2026, iniciamos com o periodo de testes trazendo 0,9% de CBS e 0,1% de IBS. A partir de 2027, novas aliquotas parametrizadas entram em vigor sem a necessidade de alterar o codigo do sistema.

## Como a NFS-e Nacional e emitida no fechamento da locacao?
Ao finalizar a locacao, o sistema gera a Operacao Fiscal, calcula os tributos e enfileira o envio da declaracao no `EmitirNfseJob`. O processamento ocorre em background com tentativas de reprocessamento em caso de indisponibilidade dos servidores do governo. O status do documento e atualizado no painel para **AUTORIZADA** ou **REJEITADA**.

# Frota e Veículos

## Como vincular informacoes fiscais (NCM) ao veiculo?
No cadastro ou edicao do veiculo, e possivel associar a **Classificacao Fiscal (NCM)** correspondente (ex: `87032210` para hatch flex ou `87032310` para SUVs). Essa associacao garante que quando o veiculo for alugado, a DPS/NFS-e seja emitida com os codigos fiscais corretos do bem movel.

## Como registrar o documento fiscal e os creditos na compra (aquisicao) de um novo veiculo?
Ao comprar um veiculo para a frota, a locadora deve acessar o painel de **Creditos Tributarios** e cadastrar a apropriação informando o valor original de IBS/CBS recolhido na aquisicao, a data e a origem (ex: *NF-e de Compra nº 12345*). Isso alimentara o saldo de creditos disponiveis para amortizar os debitos gerados pelas locacoes.

## Placa e RENAVAM do veiculo sao obrigatorios na NFS-e Nacional da locacao?
Nao. Conforme a especificacao oficial para o grupo de locacao de bens moveis (`gLocBensMoveis`), o layout nacional exige apenas o **NCM do bem**, **descricao fiscal** e **quantidade** vinculados ao codigo `99.04.01`. Placa, RENAVAM e chassi sao mantidos no cadastro interno do veiculo para controle da frota, mas nao sao enviados no XML da DPS nacional.

## Como a gestao de frota se beneficia dos creditos tributarios na Reforma?
Na aquisição de frota (compra de veículos novos), a locadora acumula créditos substanciais de IBS e CBS correspondentes ao imposto destacado na nota fiscal do fabricante. Esses créditos são apropriados no painel fiscal e amortizam diretamente os impostos devidos no faturamento diário da frota, otimizando de forma significativa os custos operacionais da empresa.

# Reservas e Contratos

## Quando ocorre o calculo de impostos da reserva no faturamento?
Os impostos sao calculados no exato momento da finalização do checkout da locação (no fechamento financeiro do contrato). Nesse instante, o sistema dispara a acao de calculo de tributos com base na data do encerramento da locacao e agenda o job de emissao da NFS-e Nacional.

## Como a NFS-e emitida fica vinculada a locacao (reserva) correspondente?
Cada `OperacaoFiscal` e `DocumentoFiscal` gerados sao automaticamente vinculados a chave `locacao_id` correspondente. Atraves da tela de detalhes da reserva no painel administrativo, o operador consegue ver o status da NFS-e (Autorizada/Pendente), visualizar os impostos IBS/CBS calculados e baixar o XML ou PDF da nota fiscal emitida.

# Opcionais e Acessórios

## Quais valores da reserva sao tributados sob o regime de IBS/CBS?
A base de calculo do imposto e composta pelo valor total da locacao (diarias contratadas). Opcionais de locacao (como seguro de protecao, cadeirinha de bebe, etc.) e tarifas de sinistros ou cobrancas de higienizacao podem sofrer incidencia tributaria diferenciada (como ISSQN municipal ou isencoes especificas) dependendo de como as regras tributarias estao configuradas no sistema.

## Como sao tributados os opcionais de locacao (seguros, acessorios, motorista)?
Os opcionais podem seguir dois fluxos dependendo da sua natureza:
1. **Locacao Integrada (Acessorios/Protecoes)**: Itens como cadeirinha de bebe, GPS ou protecoes do veiculo (CDW/LDW) costumam compor a receita de locacao de bens moveis, entrando no calculo do IBS/CBS sobre a alíquota padrão da locação.
2. **Servicos Puros (Motorista/Servico Adicional)**: Itens que caracterizam uma prestacao de servico puro (como motorista contratado) sofrem a incidencia do ISSQN municipal classico (ou nova tributacao de servicos da reforma). O sistema separa esses itens para gerar a nota municipal classica ou tributar sob regras especificas.

# Finanças e Faturamento

## Como sao gerados os creditos tributarios de IBS/CBS?
Pelo principio da nao cumulatividade, os impostos recolhidos nas aquisicoes de veiculos para frota, pecas e servicos de manutencao geram creditos tributarios. O sistema permite o lancamento desses creditos para compensar automaticamente os debitos acumulados no faturamento das locacoes.

## Como funciona o Split Payment (pagamento dividido) da Reforma Tributária?
A regulamentacao da Reforma Tributaria preve o **Split Payment**, onde o IBS e a CBS devidos sao retidos na liquidacao financeira da transacao (ex: PIX ou cartao de credito) e repassados diretamente aos cofres publicos. O DeCars registra no modulo financeiro o valor bruto recebido e separa as parcelas de impostos correspondentes com base nos registros de calculo da Operacao Fiscal, garantindo o correto provisionamento.

## Como os impostos calculados afetam o Fluxo de Caixa da locadora?
Ao finalizar um faturamento, o sistema registra os debitos de IBS/CBS como provisoes de impostos a pagar. No painel de Fluxo de Caixa, essas despesas sao consolidadas para que o gestor financeiro planeje os desembolsos periodicos de apuracao do imposto, evitando surpresas de caixa.

## Como e feita a reconciliacao dos debitos e creditos fiscais no painel financeiro?
No painel do Módulo Fiscal, o sistema apresenta o **Saldo Estimado a Pagar**. Esse calculo subtrai os creditos disponiveis (acumulados nas compras de veiculos e manutencoes) da soma dos debitos de IBS e CBS gerados nas locacoes. Essa visao de saldo liquido permite o provisionamento real da obrigacao tributaria no fechamento mensal.

# Suporte e Resolução de Problemas

## Como proceder em caso de erro ou rejeicao na emissao da NFS-e Nacional?
Caso a NFS-e retorne com status de **REJEITADA** (por erros de dados cadastrais da locadora, do cliente ou indisponibilidade da SEFAZ/Portal Nacional):
1. Acesse o menu de **Operacoes Fiscais** e verifique a mensagem de erro retornada no status da nota.
2. Corrija os dados inconsistentes apontados (ex: CNPJ invalido ou NCM incorreto).
3. O job em background tentará retransmitir a nota automaticamente.

## Como abrir um chamado de suporte técnico para o Modulo Fiscal?
Se encontrar instabilidades ou inconsistências de calculo no motor tributário, acesse o menu **Suporte -> Criar chamado** na barra lateral. Descreva a situação, anexe o ID da reserva ou da Operacao Fiscal com problema e envie. Nossa equipe técnica de suporte tributário fará a análise do log XML/JSON e entrará em contato.
