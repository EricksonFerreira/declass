# Lógica de planos (4 tiers) com mensal/anual — upgrade, downgrade e cancelamento

Este documento descreve uma lógica “padrão de mercado” para assinaturas com **4 planos** e cobrança **mensal** ou **anual**, cobrindo **upgrade**, **downgrade**, **troca de ciclo** (mensal ↔ anual) e **cancelamento**. A proposta é compatível com o comportamento observado em empresas grandes e médias (ex.: Notion, Slack, Zoom, Shopify, Atlassian, Dropbox, GitHub).

## 1) Conceitos que você precisa separar (isso evita 90% da confusão)

- **Plano (tier)**: Free/Basic/Pro/Enterprise (seus 4 planos). Define **recursos** e **limites** (entitlements).
- **Ciclo de cobrança**: mensal ou anual. Define **quando** e **quanto** cobrar.
- **Período vigente (term)**: intervalo atual já pago (ex.: 24/abr → 24/mai, ou 24/abr/2026 → 24/abr/2027).
- **Renovação automática**: ao final do term, cobra de novo e inicia novo term, a menos que cancele.
- **Entitlements efetivos vs. pendentes**:
  - **Entitlements efetivos**: o que o cliente pode usar **agora**.
  - **Mudança pendente**: o que vai valer **no próximo term** (muito comum para downgrade/cancelamento).

> Regra de ouro: **cobrança** e **acesso a recursos** precisam ser modelados como duas coisas relacionadas, mas não a mesma coisa.

## 2) O que o mercado faz (padrões mais comuns)

### Upgrade (subir de plano)
Padrão mais comum em SaaS:
- **Efeito imediato** (libera recursos na hora).
- **Cobrança proporcional (proração/credit)** do tempo restante do term.
- Em alguns produtos, **pode iniciar um novo term** no momento do upgrade (reinicia a “contagem” do período).

Exemplos:
- Notion: ao fazer upgrade no meio do período, **efeito imediato**, **cobra imediatamente** e **inicia novo período**, com valor reduzido por proração do período anterior. (fonte: `https://www.notion.com/help/upgrade-or-downgrade-your-plan`)
- Zoom: descreve créditos/cobranças proporcionais ao restante do período ao fazer upgrade/downgrade/alterações no meio do ciclo. (fonte: `https://support.zoom.com/hc/en/article?id=zm_kb&sysparm_article=KB0064536`)

### Downgrade (descer de plano)
Padrão mais comum (e o que você descreveu):
- **Não remove recursos imediatamente**.
- Agenda a mudança para **o fim do term atual** (“no próximo mês” se for mensal).
- No momento em que efetiva, **recursos e limites são reduzidos**.

Exemplos:
- Notion: downgrade ocorre **no fim do ciclo de cobrança**. (fonte: `https://www.notion.com/help/upgrade-or-downgrade-your-plan` e `https://www.notion.com/help/plan-downgrade`)
- Zoom: downgrade elegível pode **entrar em vigor no fim do período**; perda de recursos ocorre ao final do período vigente. (fonte: `https://support.zoom.com/hc/en/article?id=zm_kb&sysparm_article=KB0065952`)
- Dropbox: ao cancelar, a conta muda para o plano free **no fim do ciclo de cobrança**. (fonte: `https://help.dropbox.com/plans/downgrade-dropbox-individual-plans`)

### Troca mensal ↔ anual (billing cycle change)
Dois padrões aparecem muito:
- **Efeito no próximo ciclo** (você agenda e a troca acontece na renovação).
- **Troca imediata inicia novo ciclo** (muitas vezes quando a mudança é feita junto de uma alteração de plano).

Exemplos:
- Zoom: mudar de mensal para anual (ou vice-versa) “**entra em vigor no fim do term atual**”. (fonte: `https://support.zoom.com/hc/en/article?id=zm_kb&sysparm_article=KB0061653`)
- GitHub Copilot: mudança de mensal ↔ anual “**entra em vigor no início do próximo ciclo**”. (fonte: `https://docs.github.com/copilot/managing-copilot/managing-copilot-as-an-individual-subscriber/modifying-your-copilot-subscription-as-an-individual-user`)
- Shopify: se você mudar **o ciclo** durante a modificação do plano, pode haver **cobrança imediata** e início de **novo ciclo**. (fonte: `https://help.shopify.com/en/manual/your-account/manage-billing/managing-your-bills/billing-plan-changes`)

### Cancelamento
Padrão mais comum:
- Cancelar = **desliga a renovação automática**.
- O cliente **mantém acesso** aos recursos pagos **até o fim do term**.
- Ao final do term, volta para um plano base (ou fica “free/restrito”).

Exemplos:
- Notion: após cancelar, mantém acesso até o fim do ciclo. (fonte: `https://www.notion.com/help/upgrade-or-downgrade-your-plan`)
- Dropbox: downgrade para Basic no fim do ciclo; não cobra novamente. (fonte: `https://help.dropbox.com/plans/downgrade-dropbox-individual-plans`)
- GitHub Copilot: cancelamento e mudanças de ciclo entram em vigor ao fim/início do ciclo, respectivamente. (fontes: `https://docs.github.com/en/copilot/managing-copilot/managing-copilot-as-an-individual-subscriber/managing-your-github-copilot-pro-subscription/about-billing-for-copilot-pro` e link do tópico “Viewing and changing…”)

## 3) Sua lógica está “correta”?

Sim: **downgrade para o próximo mês** com remoção de recursos **no começo do próximo ciclo** é um comportamento bem comum e costuma ser o mais “justo” (o cliente já pagou o term atual).

O ponto que normalmente falta para ficar “padrão mercado” é definir, de forma consistente:
- **quando upgrades entram em vigor** (imediato vs. próximo ciclo),
- **como cobrar a diferença** (proração, crédito, reinício do período),
- **como tratar troca de ciclo** (mensal ↔ anual),
- **como tratar cancelamento** (fim do ciclo vs. imediato),
- e **qual é a regra para anual** (compromisso/termo anual normalmente “segura” downgrade/cancelamento para o fim do term anual).

## 4) Lógica recomendada (consistente e fácil de explicar)

### 4.1 Regras de efetivação (entitlements)
- **Upgrade de plano**: efetivo **imediatamente**.
- **Downgrade de plano**: efetivo **no fim do term atual** (fica como “mudança pendente”).
- **Cancelamento**: efetivo **no fim do term atual** (fica como “cancelamento pendente”).
- **Mensal → Anual**:
  - Opção A (mais simples): **entra no próximo ciclo** (cliente já paga o anual na renovação).
  - Opção B (mais agressiva em receita): **entra imediatamente**, com crédito proporcional do mensal restante e início do term anual agora.
- **Anual → Mensal**: normalmente **só no fim do term anual** (não faz sentido “quebrar” o compromisso). Algumas empresas limitam essa troca e podem exigir suporte/condições. (ex.: práticas na Atlassian Cloud variam por contexto). (fonte: `https://www.atlassian.com/licensing/cloud`)

### 4.2 Regras de cobrança (billing)
Escolha um modelo e aplique sempre:
- **Modelo “reinicia term no upgrade”** (parecido com Notion em upgrade):
  - No upgrade, cobra agora e inicia novo term a partir de hoje.
  - Dá crédito (prorata) pelo tempo não usado do term anterior.
- **Modelo “não reinicia term, só ajusta diferença”** (comum em billing portals e B2B):
  - Mantém a mesma data de renovação.
  - Cobra (ou credita) apenas a diferença pelos dias restantes.

Em ambos, tenha uma regra clara para:
- **crédito vs. reembolso**: muitas empresas usam **crédito** para o futuro (não reembolsa em dinheiro) em mudanças no meio do ciclo.
- **mudanças com contrato anual**: downgrade/cancelamento tipicamente **não reduz o valor já pago**; o efeito é ao final do term.

## 5) Matriz de cenários (o que acontece em cada ação)

Assumindo que você adote: **upgrade imediato**, **downgrade/cancel no fim do term**, e **mensal→anual no próximo ciclo (Opção A)**.

### 5.1 Cliente em plano mensal
- **Mensal: Plano A → Plano B (upgrade)**: recursos liberados agora; cobrança proporcional da diferença até a próxima renovação (ou reinicia o term, se esse for seu modelo).
- **Mensal: Plano B → Plano A (downgrade)**: mantém recursos do plano atual até a data de renovação; no próximo ciclo aplica Plano A e remove recursos/limites.
- **Mensal: trocar para anual (mesmo plano)**: agenda; na próxima renovação cobra anual e inicia term anual.
- **Mensal: trocar para anual + upgrade de plano**: (recomendado) trata como “mudança pendente” para o próximo ciclo ou oferece escolha “agora” vs “na renovação”.
- **Mensal: cancelar**: cancela renovação; mantém acesso até a data de renovação; depois vai para Free/base.

### 5.2 Cliente em plano anual
- **Anual: Plano A → Plano B (upgrade)**: efetivo agora; cobrança proporcional (diferença pelo restante do ano) ou cobrança imediata + ajuste (depende do modelo).
- **Anual: Plano B → Plano A (downgrade)**: agenda para o fim do term anual; recursos só caem na renovação anual.
- **Anual: trocar para mensal**: agenda para o fim do term anual (e, se você quiser ser “mais rígido”, só permite perto do fim do contrato/termo).
- **Anual: cancelar**: cancela renovação; mantém acesso até o fim do term anual; depois vai para Free/base.

## 6) O “momento” em que você remove recursos (e o que fazer com dados)

Quando um downgrade/cancelamento efetiva, o padrão que evita dor de cabeça é:
- **remover capacidade de criar/ativar** recursos acima do novo limite,
- **manter dados existentes**, mas em estado “read-only”/bloqueado até o cliente adequar (ex.: acima do limite de usuários, acima de storage, acima de integrações),
- definir um comportamento por tipo de recurso:
  - **limites quantitativos** (ex.: X itens): bloquear criação quando excede, não deletar automaticamente.
  - **recursos premium** (ex.: automações): desativar execução; manter histórico.
  - **acessos/permissões**: revogar permissões premium, mas preservar conteúdos.

Isso combina com o que empresas como Dropbox comunicam: ao cair para o free, você não “perde” os arquivos, mas passa a ter limitações e pode ter impacto de sincronização quando excede quota. (fonte: `https://help.dropbox.com/plans/downgrade-dropbox-individual-plans`)

## 7) Estados que simplificam sua implementação e comunicação

Mesmo sem falar de código, pense nesses estados como “verdades do negócio”:
- **active**: assinando e com renovação ligada.
- **active_pending_change**: tem mudança de plano/ciclo agendada para a renovação.
- **cancel_at_period_end**: cancelado, mas ainda ativo até o fim do term.
- **past_due / restricted** (opcional): pagamento falhou; acesso limitado até regularizar.
- **expired / free**: terminou e voltou para Free/base.

## 8) Como explicar isso no seu site (texto claro e “padrão SaaS”)

Sugestão de mensagens (curtas e objetivas):
- “**Upgrades são imediatos**. Ajustamos a cobrança proporcionalmente.”
- “**Downgrades e cancelamentos valem na próxima renovação**. Você mantém os recursos até lá.”
- “**Troca mensal/anual**: passa a valer na próxima renovação (ou ‘agora’, se você optar por oferecer isso).”
- “Se você fizer downgrade, **alguns recursos serão desativados** e **limites podem reduzir** no início do próximo período.”

## 9) Empresas para você se inspirar (grandes e médias)

Use como referência de comunicação e comportamento (políticas podem mudar; confira as páginas oficiais):
- Notion (upgrade imediato, downgrade no fim do ciclo; fala explicitamente em proração e efeitos): `https://www.notion.com/help/upgrade-or-downgrade-your-plan`
- Notion (impacto do downgrade em recursos): `https://www.notion.com/help/plan-downgrade`
- Slack (billing e créditos proporcionais por mudanças de membros; boa referência de “fair billing”): `https://slack.com/help/articles/218915077-Slacks-fair-billing-policy-Slacks-fair-billing-policy`
- Zoom (troca mensal/anual no fim do term; proração e downgrade no fim do período): `https://support.zoom.com/hc/en/article?id=zm_kb&sysparm_article=KB0061653` e `https://support.zoom.com/hc/en/article?id=zm_kb&sysparm_article=KB0065952`
- Shopify (mudança de plano/ciclo com possibilidade de cobrança imediata e reinício de ciclo, dependendo do caso): `https://help.shopify.com/en/manual/your-account/manage-billing/managing-your-bills/billing-plan-changes`
- Atlassian Cloud (práticas de termo anual e troca para mensal; pode ter janelas/regras): `https://www.atlassian.com/licensing/cloud`
- Dropbox (cancelar/downgrade no fim do ciclo; impacto por quota): `https://help.dropbox.com/plans/downgrade-dropbox-individual-plans`
- GitHub Copilot (mudança de ciclo no próximo ciclo; cancelamento no fim do ciclo; exemplos de data de cobrança): `https://docs.github.com/copilot/managing-copilot/managing-copilot-as-an-individual-subscriber/modifying-your-copilot-subscription-as-an-individual-user`

---

### Próximo passo (para validar a sua lógica)
Se você me disser quais são os seus 4 planos (nome + principais recursos/limites) e qual regra você prefere para **mensal→anual** (imediato vs. próxima renovação) e para **proração** (crédito vs. reembolso; reinicia ou não o term), eu ajusto esta matriz para o seu caso e deixo “fechadinha” para você implementar.
