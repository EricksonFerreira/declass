# Roteiro de Teste Manual Completo — Declass (E2E para go-live)

Objetivo: percorrer **todas as funcionalidades** do sistema antes de disponibilizar.
**A ordem importa**: dados criados numa fase alimentam as verificações seguintes.
Marque `[x]` ao concluir; anote falhas com **perfil + tela + erro exato**.

## Ambiente e credenciais

| Ambiente | URL |
|---|---|
| Produção | `https://declass.ericksondev.online` |
| Site público da escola | `https://vilanova.ericksondev.online` (ou `/rede/escola-demo`) |
| Matrícula pública | `/matricula/escola-demo/{slug-da-escola}` |
| Local (Laragon) | `http://declass.test` (MySQL precisa estar de pé) |

Todos os usuários demo: senha **`password`**

| Perfil | Login |
|---|---|
| Superadmin | `erickson@declass.com` |
| Diretor | `diretor@escolademo.test` |
| Coordenador | `coordenador@escolademo.test` |
| Secretaria | `secretaria@escolademo.test` |
| Professor | `prof1@escolademo.test` / `prof2@escolademo.test` |
| Financeiro | `financeiro@escolademo.test` |
| Bibliotecário | `bibliotecario@escolademo.test` |
| Aluno | `aluno@escolademo.test` |
| Responsável | `responsavel@escolademo.test` |

> Perfis sem login demo (criar na Fase 2 se quiser cobrir): `auxiliar_secretaria`, `professor_substituto`, `funcionario_administrativo`, `responsavel_transporte`, `responsavel_alimentacao`, `atendimento_especializado`, `responsavel_financeiro`, `membro`.

---

## Fase 0 — Público (sem login)

- [ ] **0.1** `/` — site institucional Declass carrega, sem erro/sem imagem quebrada
- [ ] **0.2** `/rede/escola-demo` (e `vilanova.ericksondev.online`) — logo, banner, galeria, unidades, contatos visíveis
- [ ] **0.3** Menu do site mostra só seções que têm conteúdo
- [ ] **0.4** CTA de matrícula → `/matricula/escola-demo/{escola}` → preencher e enviar → mensagem de sucesso (**guarde**: aprovada na Fase 4.6)
- [ ] **0.5** `/sitemap.xml` retorna XML válido
- [ ] **0.6** Login `/login` → erro com senha errada → sucesso com senha certa; "esqueci a senha" envia fluxo de reset

## Fase 1 — Superadmin (`erickson@declass.com`)

- [ ] **1.1** **Rede** (`/admin/rede`) — lista as empresas/tenants
- [ ] **1.2** Seletor de **empresa** (`empresa-contexto`) → entrar no tenant `escola-demo` → dados recarregam
- [ ] **1.3** **Empresas** (`/admin/empresa`) → listar, filtrar, abrir `show` da Vila Nova
- [ ] **1.4** Empresa → **Site** (`{empresa}/site`) → editar config e usar **preview** (`/site/preview`)
- [ ] **1.5** Empresa → **Integrações** (`/admin/empresa/integracoes`) — tela de gateway/Asaas abre (não precisa salvar chaves)
- [ ] **1.6** **Espelhamento** (`/admin/espelhamento`) → espelhar um usuário da escola → vê o painel como ele → **desfazer**
- [ ] **1.7** **Regras/Perfis** (`/admin/regra`) → abrir um perfil → matriz de permissões aparece
- [ ] **1.8** **Comandos** (`/admin/comandos`) — botões migrate/storage:link/seeders; **`*ResetSeeder` NÃO listado**
- [ ] **1.9** **Logs** (`/admin/logs`) — LogViewer abre e mostra arquivos
- [ ] **1.10** **Começar** (`/admin/comecar`) — wizard de nova empresa abre (não precisa concluir; se concluir, validar que cria tenant isolado)
- [ ] **1.11** **Menu** (`/admin/menu`) — gestão de seções do menu abre

## Fase 2 — Estrutura acadêmica (`secretaria@escolademo.test` ou `diretor@`)

Crie itens **novos** (sufixo "E2E"), sem reaproveitar massa demo:

- [ ] **2.1** **Ano letivo** → criar "2027" → **gerar períodos** → períodos criados
- [ ] **2.2** **Período letivo** → conferir/editar um período gerado
- [ ] **2.3** **Etapas** → criar "EF — E2E"
- [ ] **2.4** **Disciplinas** → criar "Matemática E2E"
- [ ] **2.5** **Turmas** → criar "1º Ano A — E2E" (etapa + ano 2027)
- [ ] **2.6** **Horário matriz** → montar grade da turma → **Horários** reflete
- [ ] **2.7** **Calendário** → dias letivos do ano visíveis
- [ ] **2.8** **Feriados** → criar feriado → aparece no calendário (não conta dia letivo)

## Fase 3 — Pessoas (`secretaria`/`diretor`)

- [ ] **3.1** **Usuários** → criar "Prof E2E" (perfil `professor`) e "Func E2E" (`funcionario_administrativo`) → listagem/filtro
- [ ] **3.2** Usuário → aba **permissões diretas** → negar 1 permissão → confere que bloqueou (desfazer depois)
- [ ] **3.3** **Atribuir** Prof E2E à disciplina E2E da turma E2E
- [ ] **3.4** **Alunos** → criar "Aluno E2E" completo → **show** abre (abas ok)
- [ ] **3.5** Aluno → aba **responsáveis** → criar/vincular responsável (gera login `responsavel_financeiro`/`responsavel_legal`)
- [ ] **3.6** Aluno → **documentos** → anexar arquivo → abrir/baixar → excluir
- [ ] **3.7** Aluno → **declaração** e **certificado** → PDFs geram
- [ ] **3.8** **Matrículas** → matricular Aluno E2E na turma E2E → `/admin/matriculas` lista; **histórico** do aluno registra
- [ ] **3.9** **Responsáveis** (`/admin/responsavel`) → listagem abre e filtra

## Fase 4 — Secretaria operacional

- [ ] **4.1** **Documentos solicitados** (`/admin/documentos-solicitados`) → solicitar "Histórico" ao Aluno E2E → fica pendente
- [ ] **4.2** **Tipos de solicitação** (`/admin/tipos-solicitacao`) → criar tipo novo → disponível para solicitar
- [ ] **4.3** **Solicitações** (`/admin/solicitacoes`) → abrir chamada → **responder** com mensagem + **anexo** → status muda
- [ ] **4.4** **Justificativas** → listagem abre (aprovação acontece na Fase 8)
- [ ] **4.5** **Agenda** (`/admin/agenda`) → criar **evento público** → aparece no site `/rede/escola-demo`
- [ ] **4.6** **Solicitações de matrícula** (`/admin/solicitacao-matricula`) → abrir a enviada na Fase 0.4 → **aprovar** → vira aluno + matrícula + responsável com acesso
- [ ] **4.7** **Avisos** → criar aviso **público** → aparece no site; criar aviso interno → aparece no portal (não no site)

### Uploads (bug corrigido em 11/11/2026 — regredir sempre que mexer em storage)

- [ ] **4.8** **Escola** → editar → anexar **logo** → aparece no show da escola
- [ ] **4.9** **Empresa → Site** → trocar **banner** + enviar 2 fotos na **galeria** → salvar → URLs `/storage/...` abrem 200
- [ ] **4.10** Ctrl+F5 em `vilanova.ericksondev.online` → banner/galeria novos visíveis
- [ ] **4.11** **Perfil** (`/admin/profile`) → trocar **foto** (`profile/foto`) → avatar atualiza
- [ ] **4.12** **Empresa** → editar → trocar **logo** → atualiza no site e no painel

## Fase 5 — Professor (`prof1@escolademo.test`)

- [ ] **5.1** **Meus horários** (`/admin/meus-horarios`) → só as aulas dele
- [ ] **5.2** **Diário** → abrir turma → **frequência** → chamada do dia (1 falta pro Aluno E2E) → salva
- [ ] **5.3** Diário → **registrar conteúdo** → salva e aparece no diário
- [ ] **5.4** **Avaliações** → criar "Prova 1 — E2E" → **lançar notas** (8.0 p/ Aluno E2E) → salva
- [ ] **5.5** **Agenda de avaliações** (`/admin/agenda-avaliacoes`) → "Prova 1" aparece; filtro funciona
- [ ] **5.6** **Tarefas** → criar tarefa p/ turma → ativa
- [ ] **5.7** **Ocorrências** → registrar p/ Aluno E2E → histórico do aluno mostra
- [ ] **5.8** Turma → **materiais** (`/admin/turma/{t}/materiais`) → anexar material → aluno/prof veem
- [ ] **5.9** Acessar `/admin/mensalidade` → **403** (não é do perfil)

## Fase 6 — Aluno e Responsável (portal)

`aluno@escolademo.test` e `responsavel@escolademo.test` → `/admin/portal`:

- [ ] **6.1** Home do portal → resumo carrega
- [ ] **6.2** **Boletim** → nota 8.0 da Prova 1 aparece; **PDF** (`portal/aluno/{id}/pdf`) gera
- [ ] **6.3** **Frequência** → falta da Fase 5.2 aparece
- [ ] **6.4** **Tarefas** → tarefa da Fase 5.6 listada → marcar **entrega**
- [ ] **6.5** **Horários / disciplinas / cardápio / avisos / calendário / agenda** → dados aparecem
- [ ] **6.6** **Documentos** (responsável) → enviar arquivo p/ solicitação da Fase 4.1 → status "enviado"
- [ ] **6.7** **Financeiro/mensalidades** (responsável) → parcelas listadas; **boleto** → PDF abre
- [ ] **6.8** **Justificativa** (responsável) → enviar justificativa de falta **com anexo** → fica pendente
- [ ] **6.9** **Solicitação** nova pelo portal (se houver abertura) → cria chamada na central da secretaria
- [ ] **6.10** **Pesquisas** (`/admin/portal/pesquisas`) → responder a criada na Fase 10.1 → salva
- [ ] **6.11** **Notificações** (sino) → notificação de evento/tarefa aparece → marcar lida → `/admin/notificacoes` lista

## Fase 7 — Financeiro (`financeiro@escolademo.test`)

- [ ] **7.1** **Plano de mensalidade** → criar plano → **gerar mensalidades** da matrícula Aluno E2E → parcelas criadas
- [ ] **7.2** **Mensalidades** → anexar **boleto PDF** numa parcela → portal do responsável mostra o boleto (6.7)
- [ ] **7.3** Baixar parcela como **recebida** → status pago → fluxo de caixa atualiza
- [ ] **7.4** **Contas a receber** → criar → **receber** → baixa ok; **cancelar** uma → cancelada
- [ ] **7.5** **Despesas** → criar → **pagar** → sai no fluxo e no DRE
- [ ] **7.6** **Fluxo de caixa** (`/admin/fluxo-caixa`) → entradas/saídas do período batem
- [ ] **7.7** **DRE** (`/admin/dre`) → demonstrativo abre com valores
- [ ] **7.8** **Visão geral financeira** (`/admin/financeiro`) → cards/gráficos carregam
- [ ] **7.9** **Relatório inadimplência** → parcela vencida não paga listada
- [ ] **7.10** Acessar `/admin/turma` → **403**

## Fase 8 — Aprovações e cruzamentos

- [ ] **8.1** (secretaria/diretor) **Justificativas** → aprovar a da Fase 6.8 → falta do aluno fica **justificada**
- [ ] **8.2** **Documentos solicitados** → aprovar envio da Fase 6.6 → concluído
- [ ] **8.3** **Agenda avaliações** → Prova 1 no calendário; avaliação com filtro por turma
- [ ] **8.4** **Boletim turma** (`/admin/boletim/turma/{id}`) → média do aluno reflete notas → PDF turma gera
- [ ] **8.5** **Relatório frequência** → falta/justificativa do aluno contabilizada
- [ ] **8.6** Aluno → **histórico escolar** → matrícula + notas consolidadas; **PDFs** geram

## Fase 9 — Biblioteca (`bibliotecario@escolademo.test`)

- [ ] **9.1** **Livros** → cadastrar "Livro E2E" → acervo atualiza
- [ ] **9.2** **Empréstimos** → emprestar p/ Aluno E2E → livro indisponível
- [ ] **9.3** Devolver → livro disponível; histórico do empréstimo

## Fase 10 — Pesquisas, estoque, transporte, saúde

- [ ] **10.1** **Pesquisas** (`/admin/pesquisa`) → criar pesquisa ativa → portal responde (Fase 6.10) → resultado/aba contabiliza
- [ ] **10.2** **Materiais/estoque** (`/admin/material`) → criar item → **movimentar** (entrada/saída) → **movimentações** lista → saldo bate
- [ ] **10.3** **Veículos** → criar van → **Rotas de transporte** → criar rota → **Transporte aluno** → vincular Aluno E2E
- [ ] **10.4** **Relatório transporte** → aluno/rota listados
- [ ] **10.5** **Cardápio** → cadastrar semana → portal mostra (Fase 6.5)
- [ ] **10.6** **Saúde** → ficha/registro do Aluno E2E → aparece na ficha
- [ ] **10.7** **Plano de atendimento** → criar p/ Aluno E2E → registrado

## Fase 11 — Casa e conta do usuário

- [ ] **11.1** **Dashboard** (`/admin/dashboard`) e **home** (`/admin/home`) → cards/gráficos sem erro
- [ ] **11.2** **Perfil** → editar dados → salvar; **senha** → trocar → logout → login com senha nova → voltar `password`
- [ ] **11.3** **Notificações** → sino da navbar badge correto → marcar todas lidas
- [ ] **11.4** Listagens em geral → **filtro** → **remover filtro** → **per-page** persiste → toggle **cards/tabela** persiste
- [ ] **11.5** **Dark mode** toggle → persiste após reload
- [ ] **11.6** Mobile (≤576px) → menu colapsa, tabelas em scroll horizontal, calendário vira marcadores

## Fase 12 — Multi-tenant e segurança (critério go/no-go)

- [ ] **12.1** Trocar de **escola** no seletor (`escola-contexto`) → dados isolados por unidade
- [ ] **12.2** Tenant `declass` vs `escola-demo` → alunos/matrículas/financeiro **não vazam** entre empresas
- [ ] **12.3** Usuário com permissão negada direta (Fase 3.2) → rota bloqueada
- [ ] **12.4** Cada perfil acessa **só** seu menu: professor ≠ financeiro, financeiro ≠ acadêmico, bibliotecário ≠ usuários, aluno/responsável só portal
- [ ] **12.5** URLs diretas sem permissão → **403** (não 500)
- [ ] **12.6** Logout → `/admin/*` redireciona para login
- [ ] **12.7** `/migrations/setup-producao` sem token → **403**; token errado → **403**

## Fase 13 — Regressão de storage/uploads (go/no-go)

- [ ] **13.1** Qualquer imagem `/storage/...` recém-enviada abre **200** (logo, banner, galeria, boleto, foto, documento)
- [ ] **13.2** `public/storage` no servidor é **symlink** (não pasta) — conferir no hPanel/API
- [ ] **13.3** Arquivo apagado/re-substituído atualiza a imagem (sem cache fantasma)

---

## Critério de liberação (go/no-go)

- [ ] Todas as fases 0–13 sem erro 500 e sem imagem quebrada
- [ ] Matrícula pública → aprovação → acesso do responsável funcionando ponta a ponta
- [ ] Nota → boletim → portal → PDF consistente
- [ ] Falta → justificativa → aprovação → frequência consistente
- [ ] Mensalidade → boleto → portal → baixa → fluxo de caixa consistente
- [ ] Isolamento entre empresas e entre escolas confirmado
- [ ] Permissões por perfil conferidas nas rotas críticas (financeiro, usuários, regras, comandos)

## Diagnóstico rápido

| Sintoma | Causa provável | Ação |
|---|---|---|
| `<img>` nem é renderizado | arquivo fora do disk `public` | `UploadStorage::putFile(..., 'public')` |
| `<img>` renderizado mas 404 | `public/storage` virou pasta real | rodar `setup-producao`; fallback `/storage/{path}` cobre |
| 403 inesperado | permissão faltando na matriz do perfil | `/admin/comandos` → `permissoes:aplicar-matriz` |
| 500 genérico | deploy pela metade / view compilada | aguardar, rodar `setup-producao` (optimize:clear) |
| Dado de outra empresa aparecendo | escopo `empresa` ausente na query | bug — abrir chamado |
