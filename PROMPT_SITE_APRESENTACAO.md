# Prompt: análise e sugestões — Site de apresentação das escolas (DeClass)

## Contexto

Eu tenho um sistema SaaS de gestão escolar multi-tenant chamado **DeClass** (Laravel 11, PHP 8.3, Blade, Bootstrap/AdminLTE, MySQL). Cada **empresa** (tenant) é uma rede de escolas com uma ou mais **unidades** (escolas). Cada empresa tem slug próprio e pode ter domínio/subdomínio personalizado — a ideia é cada escola/rede ter o seu site público no seu domínio.

Implementei uma funcionalidade de **"site de apresentação"**: cada empresa pode ligar um site institucional público gerado a partir dos dados já cadastrados no sistema — sem CMS separado, sem páginas editadas manualmente.

## Como funciona hoje

### Configuração (admin)

- Tela dedicada em `/admin/empresa/{id}/site` (botão globo na lista de empresas):
  - **Toggle "Site no ar?"** — Não: o domínio da empresa cai direto na tela de login com a marca dela. Sim: serve a apresentação. Esconder/mostrar o resto do form via JS.
  - **Modelo de layout** — 5 opções com card-radio + botão **"Pré-visualizar"** que abre modal com iframe (`/admin/empresa/{id}/site/preview?modelo=X`) renderizando o modelo com os dados reais da empresa, sem salvar e sem exigir site no ar.
  - **Textos** — `site_titulo` (tagline), `site_descricao` (sobre), `site_aviso` (faixa de aviso editável, ex.: "Matrículas 2027 abertas"), `site_depoimentos` (um por linha: `Nome | Texto`).
  - **Imagens** — `site_banner` (fundo do topo), `site_imagem_sobre`, `site_galeria` (até 12 fotos, upload múltiplo com remoção individual; logo vem do cadastro da empresa).
  - **Contato e módulos** — `site_whatsapp`, `site_instagram`, toggles `site_unidades`, `site_matricula`, `site_contato`.

### Entrega (público)

- Rotas: `/rede/{slug}` na plataforma e **raiz do domínio personalizado** da empresa (resolve por host). Site off → 404/redirect para login com a marca.
- Dispatcher `site/empresa.blade.php` inclui `site/modelos/{modelo}` com whitelist; modelo inválido cai no `classico`.
- 5 modelos: `classico` (gradiente + logo), `hero` (banner tela cheia), `minimal` (compacto), `escola` (moderno, baseado no machadodeassispe.com.br), `gandolfi` (tradicional, baseado no colegiogandolfi.com.br — topbar azul, nav sticky, hero split).
- Cada modelo define só `:root` com as cores da empresa (`cor_primaria`/`cor_secundaria`) + layout do topo; as **seções são partials Blade compartilhados** entre todos os modelos.

### Seções atuais (ordem; todas condicionais — só renderizam se houver dados)

1. Topbar/nav (telefone, e-mail, portais, links sociais)
2. Hero (título/tagline + banner ou logo + CTAs: matrícula, WhatsApp, contato)
3. **Faixa de aviso** (`site_aviso`)
4. **Segmentos** — etapas de ensino ativas da empresa (`Etapa`: nome, segmento, descrição) com link Matrícula
5. **Unidades** — escolas ativas (nome, cidade/UF, endereço, telefone, botão Matrícula → form público `/matricula/{empresa}/{escola}`)
6. Sobre (`site_descricao` + `site_imagem_sobre`)
7. **Galeria** — fotos enviadas (grid 4×3)
8. Contato — respeita toggle `site_contato`
9. **Agenda** — próximos `Evento` com `publico=todos`, não cancelados, data futura (badge dia/mês, hora, local, tipo)
10. **Comunicados** — `Aviso` com `destino=familias`, ativos, publicados (data, tipo, título, resumo) — funciona como "notícias/postagens"
11. **Depoimentos** — campo editável
12. **Instagram** — faixa "@perfil" com grade das fotos da galeria
13. **Footer institucional** — logo+nome, coluna "Acesso rápido" (Portal, Matrículas, Comunicados, Agenda, Fale conosco — respeitando toggles, + "Trabalhe conosco" mailto), coluna contato, barra legal `© ano · CNPJ · Plataforma DeClass`

### Dados que alimentam o site (zero conteúdo inventado)

- `Empresa`: nome, slug, cores, logo, CNPJ, telefone, e-mail, endereço, campos `site_*`
- `Escola` (unidades): nome, cidade, estado, endereço, telefone, e-mail, logo, slug
- `Etapa` (segmentos), `Aviso` (comunicados), `Evento` (agenda)
- Matrícula pública: form real em `/matricula/{empresa}/{escola}` (só anos letivos vigentes/planejamento)

### Pesquisa de referência já feita

Analisei sites reais: machadodeassispe.com.br, colegiogandolfi.com.br, Objetivo, Bernoulli, anchieta.br, mackenzie.br, colegiointegrado.com.br. Padrões observados e já incorporados: topbar de utilidade, nav institucional, hero com foto real, faixa "Matrículas abertas", segmentos como cards com CTA, galeria, agenda/calendário, comunicados datados, depoimentos, feed Instagram, footer institucional com CNPJ. Removidos os elementos com "cara de IA": gradientes chamativos, chips em excesso, "Role a tela", copy genérica.

## O que quero de você

1. **Avalie criticamente** essa arquitetura e o conjunto de seções — o que ainda denuncia "feito por IA" ou "template genérico"?
2. **Sugira melhorias priorizadas** (alto/médio/baixo impacto) pensando em:
   - Conversão (família pesquisando escola → agendar visita/matrícula)
   - Cara de site feito por empresa grande/agência (detalhes de acabamento, microcopy, estrutura)
   - SEO local e performance
   - O que sites de escola brasileiros têm que ainda falta aqui (ex.: página de matrícula/etapas do processo, FAQ, cardápio, lista de materiais, equipe, resultados/aprovações)
3. **Proponha elementos novos** que usem dados que o sistema já tem (calendário letivo, turmas, solicitações de matrícula, documentos) — sem inventar CMS genérico.
4. Se sugerir novos campos editáveis, diga o formato ideal (texto livre, lista estruturada, upload).
5. Aponte riscos: performance (imagens), consistência visual entre os 5 modelos, conteúdo vazio quebrando layout, acessibilidade.

Seja direto e prático — prefiro sugestões acionáveis a teoria.
