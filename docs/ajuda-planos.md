# Ajuda - Planos e Assinaturas

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

## Como funciona o calculo de impostos do Modulo Fiscal na locacao de veiculos?
De acordo com a **Reforma Tributaria (Lei Complementar 214/2025)**, a locacao de bens moveis (codigo `99.04.01`) passa a sofrer a incidencia do **IBS** e da **CBS**. O Modulo Fiscal de DeCars desacopla o calculo dos contratos e reservas, registrando o fato gerador (Operacao Fiscal) no fechamento da locacao e calculando os impostos aplicaveis de acordo com a vigencia legal.

## Como sao gerados os creditos tributarios de IBS/CBS?
Pelo principio da nao cumulatividade, os impostos recolhidos nas aquisicoes de veiculos para frota, pecas e servicos de manutencao geram creditos tributarios. O sistema permite o lancamento desses creditos para compensar automaticamente os debitos acumulados no faturamento das locacoes.

## Como configurar novas aliquotas do Modulo Fiscal?
As aliquotas sao configuraveis na tabela de regras tributarias (`regras_tributarias`) e possuem datas de vigencia (inicio e fim). Em 2026, iniciamos com o periodo de testes trazendo 0,9% de CBS e 0,1% de IBS. A partir de 2027, novas aliquotas parametrizadas entram em vigor sem a necessidade de alterar o codigo do sistema.

## Como a NFS-e Nacional e emitida no fechamento da locacao?
Ao finalizar a locacao, o sistema gera a Operacao Fiscal, calcula os tributos e enfileira o envio da declaracao no `EmitirNfseJob`. O processamento ocorre em background com tentativas de reprocessamento em caso de indisponibilidade dos servidores do governo. O status do documento e atualizado no painel para **AUTORIZADA** ou **REJEITADA**.


