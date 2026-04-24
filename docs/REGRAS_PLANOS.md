# Regras de Negócio — Planos e Assinaturas DeCars

## Visão Geral

Este documento define as regras de negócio para o sistema de planos do DeCars, baseadas nas melhores práticas de empresas SaaS como Stripe, HubSpot, Linear, Notion e outras.

O modelo adotado é **cobrança por período com crédito proporcional** (proration), que é o padrão da indústria e o mais justo para o cliente.

---

## 1. Conceitos Fundamentais

### 1.1 Período de Cobrança
Cada assinatura tem um **período de cobrança** com data de início e data de fim. O cliente paga por esse período, independentemente de trocas de plano que ocorram no meio.

- **Mensal**: 1 mês corrido a partir da data de contratação
- **Anual**: 12 meses corridos a partir da data de contratação

### 1.2 Data de Renovação
A renovação sempre ocorre no **mesmo dia do mês** da contratação original. Exemplo: contratou dia 15 de abril, renova dia 15 de maio, 15 de junho, etc.

### 1.3 Crédito Proporcional (Proration)
Quando o cliente troca de plano no meio do período, ele já pagou X dias do plano atual. Esse valor deve ser **descontado** do novo plano ou **creditado** na conta.

**Fórmula:**
```
Dias restantes = Data fim período - Data atual
Valor por dia do plano atual = Valor pago / Total de dias do período
Crédito = Dias restantes × Valor por dia
Valor a cobrar pelo novo plano = Valor proporcional do novo plano - Crédito
```

---

## 2. Cenários de Troca de Plano

### 2.1 UPGRADE no mesmo período (plano mais caro)

**Exemplo:** Contratou Gestão (R$247) mensal em 01/04. Em 15/04 quer ir para Crescimento (R$397).

**Regra adotada (padrão Stripe/HubSpot):**
- Calcula o crédito dos dias restantes do plano atual
- Cobra imediatamente a diferença proporcional pelo novo plano
- O período de cobrança **não se reinicia** — mantém o mesmo dia de vencimento
- O novo plano entra em vigor **imediatamente**

**Cálculo:**
```
Plano atual: R$247/30 dias = R$8,23/dia
Dias restantes: 15 dias
Crédito: 15 × R$8,23 = R$123,50

Novo plano: R$397/30 dias = R$13,23/dia
Valor proporcional pelos 15 dias restantes: 15 × R$13,23 = R$198,50

Valor a cobrar agora: R$198,50 - R$123,50 = R$75,00
```

Na renovação do próximo período, cobra o valor cheio do novo plano (R$397).

**Por que assim?** O cliente não perde nada do que pagou e tem acesso imediato ao plano superior.

---

### 2.2 DOWNGRADE no mesmo período (plano mais barato)

**Exemplo:** Contratou Crescimento (R$397) mensal em 01/04. Em 15/04 quer ir para Gestão (R$247).

**Regra adotada (padrão HubSpot/Linear):**
- O downgrade **NÃO entra em vigor imediatamente**
- O cliente mantém o plano atual até o fim do período já pago
- O downgrade é **agendado** para o próximo vencimento
- **Sem reembolso** pelo período já pago (o cliente usou o plano mais caro)

**Por que assim?**
- Evita abusos (contratar plano alto, usar funcionalidades premium, fazer downgrade no mesmo dia)
- O cliente já teve acesso ao recurso premium — o valor já foi consumido
- É o padrão do mercado (Netflix, Spotify, GitHub, todos funcionam assim)

**Exceção — Downgrade imediato com reembolso:**
Apenas se o cliente demonstrar que NÃO usou nenhum recurso exclusivo do plano superior. Na prática, isso é difícil de automatizar e não é recomendado implementar.

---

### 2.3 UPGRADE de Mensal para Anual

**Exemplo:** Está no Gestão mensal (R$247/mês) desde 01/04. Em 15/04 quer migrar para Gestão anual (R$197/mês × 12 = R$2.364 à vista).

**Regra adotada:**
- Calcula crédito proporcional dos dias restantes do plano mensal
- Cobra o valor anual **menos o crédito**
- A data de renovação passa a ser daqui a 12 meses

**Cálculo:**
```
Crédito: 15 dias × R$8,23/dia = R$123,50
Valor anual: R$2.364,00
Valor a cobrar: R$2.364,00 - R$123,50 = R$2.240,50
Novo vencimento: 15/04/2027
```

---

### 2.4 DOWNGRADE de Anual para Mensal

**Exemplo:** Contratou Crescimento anual (R$317/mês × 12 = R$3.804) em 01/01. Em 01/03 (2 meses depois) quer ir para Gestão mensal.

**Regra adotada:**
- O downgrade é **agendado** para o fim do período anual (31/12)
- Mantém acesso ao plano anual até o vencimento
- **Sem reembolso** pelos meses restantes do anual

**Exceção com reembolso parcial (política opcional):**
Se quiser oferecer reembolso, calcula assim:
```
Valor pago: R$3.804,00
Meses utilizados: 2
Valor mensal equivalente (sem desconto): R$397/mês
Valor "consumido" a preço cheio: 2 × R$397 = R$794
Reembolso: R$3.804 - R$794 = R$3.010

(Penaliza o desconto anual que o cliente aproveitou durante 2 meses)
```

**Recomendação para DeCars:** Não oferecer reembolso do anual. Oferecer apenas o agendamento do downgrade para o vencimento. Isso é o que a maioria das empresas faz.

---

### 2.5 Troca para plano IGUAL (mesmo plano, mesma periodicidade)

Não faz sentido. Bloquear na interface.

---

### 2.6 Troca para plano IGUAL com periodicidade diferente

Tratar como upgrade/downgrade conforme o valor resultante.

---

## 3. Regras de Limite de Recursos

### 3.1 Veículos

| Situação | Regra |
|---|---|
| Upgrade: novo plano tem limite maior | Libera imediatamente |
| Upgrade: novo plano é ilimitado | Libera imediatamente |
| Downgrade: excede o novo limite | Bloqueia até o cliente desativar veículos excedentes |
| Downgrade agendado | Avisa o cliente antes do vencimento que precisará desativar |

**Veículos excedentes nunca são desativados automaticamente pelo sistema.** O cliente sempre escolhe quais desativar. O sistema apenas bloqueia o cadastro de novos veículos acima do limite.

### 3.2 Módulos (multas, NFS-e, site de vendas)

| Situação | Regra |
|---|---|
| Upgrade: módulo disponível no novo plano | Libera imediatamente |
| Downgrade: módulo não disponível no novo plano | Desabilita no vencimento do período (se downgrade agendado) |
| Downgrade imediato (admin) | Desabilita imediatamente, mantém dados |

**Dados nunca são apagados.** O módulo é desabilitado (não aparece na interface), mas os registros existentes são preservados. Se o cliente fizer upgrade no futuro, os dados voltam a aparecer.

---

## 4. Política de Reembolso

| Cenário | Reembolso |
|---|---|
| Cancelamento no mesmo dia da contratação | Sim, 100% |
| Cancelamento dentro de 7 dias (trial ou garantia) | Sim, opcional por política comercial |
| Downgrade no meio do período | Não (crédito para próxima cobrança, nunca dinheiro de volta) |
| Cancelamento após 7 dias | Não (acesso até o fim do período pago) |
| Cobrança indevida (erro do sistema) | Sim, 100% + notificação |

**Recomendação:** Implementar um "período de garantia" de 7 dias para o primeiro pagamento. Se o cliente cancelar dentro de 7 dias da primeira contratação, reembolsa 100%.

---

## 5. Cancelamento

### 5.1 Cancelamento pelo cliente

- O cancelamento é **imediato** como intenção, mas o acesso continua até o fim do período pago
- O sistema marca a assinatura como `cancelamento_agendado` + data de cancelamento
- Na data de cancelamento, muda para `cancelado`
- Não gera nova cobrança

### 5.2 Reativação após cancelamento

- Se o cliente reativar antes do vencimento: remove o agendamento de cancelamento, continua normalmente
- Se o cliente reativar após o vencimento: nova contratação do zero

### 5.3 Inadimplência

- Cobrança não paga até o vencimento: status muda para `inadimplente`
- Sistema envia notificações: dia 0 (vencimento), dia +3, dia +7, dia +15
- Após 30 dias sem pagamento: acesso suspenso (não cancelado — os dados são mantidos)
- Após 90 dias sem pagamento: cancelamento definitivo

---

## 6. Trial (Período de Avaliação)

### Regras do Trial

- Duração: 14 dias (recomendado)
- Sem necessidade de cartão para iniciar (opcional por política)
- Plano trial tem **todos os recursos do plano mais alto** (melhor experiência)
- No fim do trial, o cliente escolhe o plano e efetua o pagamento
- Se não pagar, vai para `trial_expirado` com acesso bloqueado

### Trial após upgrade/downgrade

- Não se aplica. Trial é apenas para novos clientes.

---

## 7. Fluxo Completo de Decisão — Troca de Plano

```
Cliente quer trocar de plano
         |
         v
Tem plano ativo?
    |          |
   NÃO        SIM
    |           |
Contratar      É o mesmo plano?
novo            |          |
               SIM        NÃO
                |           |
            Bloquear      É upgrade ou downgrade?
                              |               |
                          UPGRADE         DOWNGRADE
                              |               |
                    Calcular proration    Está no período anual?
                    Cobrar diferença          |           |
                    Ativar imediato          SIM         NÃO
                                             |           |
                                      Agendar para   Agendar para
                                      vencimento     próximo mês
                                      do anual       (mantém acesso
                                                     até lá)
```

---

## 8. Implementação Recomendada para DeCars

### Fase 1 (atual — simplificado)
- Upgrade imediato sem proration (cobra valor cheio do novo plano)
- Downgrade agendado para o próximo vencimento
- Sem reembolso automático
- Admin pode fazer qualquer operação manualmente

### Fase 2 (próxima evolução)
- Implementar proration no upgrade
- Cobrar a diferença via Stripe automaticamente
- Notificações automáticas de vencimento por e-mail

### Fase 3 (completo)
- Webhook Stripe para confirmar pagamentos automáticos
- Portal do cliente para autoatendimento (trocar plano, cancelar, ver faturas)
- Integração com NFS-e para emitir nota fiscal automaticamente na confirmação do pagamento

---

## 9. Status da Assinatura — Máquina de Estados

```
[trial] ──pagamento confirmado──> [ativo]
[ativo] ──vencimento sem pagamento──> [inadimplente]
[ativo] ──cliente solicita cancelamento──> [cancelamento_agendado]
[cancelamento_agendado] ──data de cancelamento──> [cancelado]
[cancelamento_agendado] ──cliente reativa──> [ativo]
[inadimplente] ──pagamento realizado──> [ativo]
[inadimplente] ──30 dias──> [suspenso]
[suspenso] ──pagamento realizado──> [ativo]
[suspenso] ──90 dias──> [cancelado]
[aguardando_pagamento] ──pagamento confirmado──> [ativo]
[aguardando_pagamento] ──7 dias sem pagar──> [inadimplente]
```

---

## 10. Perguntas e Respostas Objetivas

**P: Cliente contratou Gestão para abril. Pode contratar outro plano em abril?**
R: Sim, pode — mas o tipo de operação muda:
- Se for upgrade: paga a diferença proporcional e tem acesso imediato.
- Se for downgrade: o downgrade só entra em maio. Ele mantém o Gestão até 30/04.

**P: Contratou Gestão anual em janeiro. Em março quer o Crescimento mensal. Como fica?**
R: É um upgrade de plano com downgrade de periodicidade ao mesmo tempo.
- Recomendado: cobrar a diferença proporcional do Crescimento mensal pelos meses restantes do anual (ou simplesmente agendar para o vencimento e manter o anual ativo).
- Mais simples (Fase 1): agendar para o vencimento do anual (dezembro). Mantém o benefício do desconto anual que ele já pagou.

**P: Cliente quer cancelar o anual depois de 2 meses. Recebe reembolso?**
R: Não (política padrão). Mantém acesso até dezembro. Se quiser ser mais flexível, pode oferecer crédito (não dinheiro) para usar em novo plano.

**P: E se o cliente tiver 25 veículos e quiser ir para um plano de 20?**
R: O downgrade é agendado. Antes do vencimento, o sistema avisa que ele precisa desativar 5 veículos. Se chegar no vencimento sem desativar, o sistema bloqueia o cadastro de novos veículos mas não desativa os existentes — só o admin pode forçar isso.

**P: O que acontece com os dados se o plano for cancelado?**
R: Nada é apagado por até 90 dias após o cancelamento. Depois de 90 dias, pode-se aplicar política de retenção (arquivar ou apagar conforme LGPD).

---

## 11. Resumo da Recomendação para DeCars (Fase 1)

| Operação | Comportamento |
|---|---|
| Novo cliente | Contratar qualquer plano, pagar, ativar |
| Upgrade no período | Ativa imediatamente, cobra valor cheio do novo plano no próximo vencimento |
| Downgrade no período | Agenda para o próximo vencimento, mantém plano atual |
| Upgrade anual → mensal | Não permitir (seria downgrade de valor, agendar para vencimento) |
| Downgrade anual → outro anual | Agendar para vencimento do anual |
| Cancelamento | Acesso até fim do período, sem reembolso |
| Inadimplência | 30 dias de carência antes de suspender |
| Dados após cancelamento | Mantidos por 90 dias |
