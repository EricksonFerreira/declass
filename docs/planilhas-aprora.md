# Planilhas APRORA → onde ver no DECLOCK

Mapeamento das abas das planilhas `APRORA-Gestão.xlsx` e `APRORA-PDCA.xlsx`
para as telas/funcionalidades equivalentes no sistema.

---

## APRORA-Gestão.xlsx — consolidação e gestão

| Aba | O que é | Onde ver no DECLOCK |
|-----|---------|---------------------|
| **A01** — Portfólio | Todos os projetos com ANO, Número, **CC**, Status e HH Consumida/Estimada/% por função + Compilado | **Relatórios → Consumo × Estimado** (`/admin/relatorios/consumo`). Mesma grade: linhas = projetos, colunas = funções × (Consumida/Estimada/%), coluna Compilado, filtro por status/cliente/ano. O "CC" é o campo **Código** do projeto |
| **A02** — Detalhe do projeto | Colaboradores de um projeto: função, fator (jornada), HH Consumida/Estimada/% por membro | **Relatórios → Consumo × Estimado → clicar no projeto** (`/admin/relatorios/consumo/{id}`). Tabela "Horas por colaborador" (função, HH, custo/h, custo) + "Consumo por função" |
| **A03** — Evolução temporal | HH acumulada por data, por projeto (curva S) | **Detalhe do Consumo × Estimado** → card **"Evolução do consumo"**: linha = HH acumulada, barras = HH por dia, tracejada = estimativa |
| **B00** — Matriz bruta | Colaborador × projeto → HH consumida | **Relatórios → Matriz de Horas** (`/admin/relatorios/matriz`). Filtro por projeto e período; totais por linha e coluna |
| **B01** — Matriz com função/fator | Igual B00 + função e jornada (1.0 / 0.5 estagiário) por colaborador | Mesma **Matriz de Horas** — a função aparece sob o nome do colaborador. O "fator" virou o campo **Jornada diária** no cadastro do usuário |
| **B02** — Orçamento | HH estimada por **função × projeto** + consumido por função | **Detalhe do Consumo × Estimado** → tabela "Consumo por função" + formulário "Adicionar estimativa" (grava em `clockify_projeto_estimativas`). O fator "k" (taxa média R$/h) = `custo_hora` da função/usuário |

## APRORA-PDCA.xlsx — apontamento diário

| Aba | O que é | Onde ver no DECLOCK |
|-----|---------|---------------------|
| **Main** | Cadastro de colaboradores (ID, nome, e-mail) | **Gerenciar → Usuários** (`/admin/usuario`) |
| **02** | Grade de slots de 15 minutos | Implícito — o apontamento usa início/fim livres, não slots fixos |
| **Abas numeradas** (uma por colaborador) | Matriz slot-15min × dia marcada com o projeto | **Rastreador** (`/admin/tracker`) e **Planilha de Horas** (`/admin/timesheet`) — mesmos dados (`clockify_time_trackers`), UX diferente. Abas ocultas de ex-colaboradores = `users.ativo = 0` |

## Campos-chave

| Planilha | DECLOCK |
|----------|---------|
| CC (centro de custo, ex. `440-158.01`) | `clockify_projetos.codigo` — editável no cadastro do projeto; aparece na listagem, detalhes e relatórios; busca/filtro cobrem o código |
| Fator / Obs2 (1.0 = integral, 0.5 = meio período) | `users.jornada_diaria` (padrão 8h) — usada pelo comando `clockify:lembrete-horas` para avisar quando o dia está incompleto |
| Função (Coord. Equip., Eng. Aux., Técnico…) | `funcoes` + `users.funcao_id` — gerenciável em **Gerenciar → Funções** |
| Status do projeto (Concluído etc.) | `clockify_projetos.status` — alterável no detalhe do Consumo × Estimado |
| HH estimada por função | `clockify_projeto_estimativas` — configurada no detalhe do Consumo × Estimado |
| HH consumida | `clockify_time_trackers` — lançamentos do rastreador/timesheet |

## O que não foi migrado (por decisão)

- **Grade 15min × dia** (abas PDCA): UX substituída por rastreador + planilha semanal; os dados são equivalentes
- **Snapshots diários** (A03): calculados sob demanda a partir dos lançamentos
- **1 aba por colaborador / abas ocultas**: substituído por `user_id` e `users.ativo`
