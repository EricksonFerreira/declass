# Projeto Laravel

## Stack

- Laravel 11
- PHP 8.3
- Blade
- jQuery/Ajax
- MySQL
- Bootstrap/AdminLTE

## Arquitetura

- Controllers devem ser pequenos.
- Regras de negocio ficam em Actions.
- Actions possuem metodo publico `handle()`.
- Validacao fica em Form Requests quando possivel.
- Consultas reutilizaveis devem usar Services, Queries ou Scopes.
- Nao colocar regra de negocio complexa em Blade.
- Preservar o padrao existente antes de criar novas abstracoes.

## Fluxo de trabalho do agente

1. Localizar o modulo envolvido.
2. Ler apenas os arquivos diretamente relacionados.
3. Identificar padroes semelhantes no projeto.
4. Apresentar uma explicacao curta da causa.
5. Alterar somente os arquivos necessarios.
6. Rodar testes ou verificacoes focadas.
7. Mostrar os arquivos alterados e possiveis riscos.

## Restricoes

- Nao alterar migrations executadas em producao.
- Nao executar `migrate:fresh`.
- **PROIBICAO ABSOLUTA: deploy/setup NUNCA pode resetar dados de producao.** `?fresh` e `?reset` nao sao aceitos pela rota `setup-producao` (migrate:fresh derruba todas as tabelas; DemoEscolarResetSeeder apaga a massa academica do tenant escola-demo). Os flags existem so no artisan e sao bloqueados quando `APP_ENV=production` salvo `DECLASS_SETUP_PERMITIR_RESET=true` no .env (uso emergencial — remover na sequencia). `db:seed` e idempotente: so preenche campos vazios (site da escola-demo so configura quando `site_titulo` esta vazio; `dominio` so faz backfill). Seeders `*ResetSeeder` ficam fora da central `/admin/comandos`.
- Nao mudar contratos publicos sem necessidade.
- Nao modificar arquivos fora do escopo solicitado.
- Nao criar classes genericas sem beneficio concreto.
- Nao ler `vendor`, `storage`, `node_modules` ou logs completos.

## Comandos preferenciais

```bash
php artisan test --filter=NomeDoTeste
php artisan route:list --path=reserva
php artisan tinker
./vendor/bin/pint --test
```

## Deploy (Hostinger)

### Producao atual — declass.ericksondev.online (sistema escolar)

- Site criado em 2026-11-06; docroot `/home/u735272302/domains/declass.ericksondev.online/public_html`.
- Banco: `u735272302_declass` (user igual), host interno `127.0.0.1:3306`.
- Deploy e manual via `hosting_deploy` com pacote local: o docroot e o root do projeto; `.env` e `vendor/` vao DENTRO do pacote (API nao edita .env; nao ha SSH/composer remoto — `system/exec` desabilitados).
- `vendor/` de producao = `composer install --no-dev` + `fakerphp/faker` (seeders usam `fake()`); limpar `vendor/almasaeed2010/adminlte` de docs/plugins/build/src/pages antes de empacotar (~95MB a menos).
- EXCLUIR do pacote: `public/storage` (e symlink local; virou pasta real com lixo de dev — o deploy apaga e `storage:link` recria), `storage/framework/views/*` (views compiladas em lote podem sair corrompidas), `storage/logs/*`, `bootstrap/cache/*.php`, `.git`, `node_modules`.
- Blade: nao misturar `@php(expr)` inline com `@endphp` mais adiante no mesmo arquivo — o regex `@php(.*?)@endphp` engole o meio como bloco cru (parse error + corrompe o contador de `@forelse` no `view:cache`, gerando `$__empty_-N` em views seguintes). Usar sempre `@php expr; @endphp`.
- Sessao/cache em `file` no .env de producao (drivers `database` exigem tabelas que nao existem antes do primeiro migrate).
- Setup remoto: `GET /migrations/setup-producao?token={APP_MIGRATION_ROUTE_TOKEN}&demo=1` roda optimize:clear + storage:link + migrate --force + db:seed (+DemoEscolarSeeder com `demo=1`) + tenant/superadmins. Idempotente e SEM opções destrutivas — ver proibicao em Restricoes.
- Cron `schedule:run` criado via API (uid em `.hostinger/site.json`).
- Metadados do deploy em `.hostinger/site.json`.

### Legado — declock.ericksondev.online (sistema antigo de locacao)

- Auto-deploy Git ativo: push na `main` (repo `David-e-Erickson/declock`) clona no docroot `public_html` e roda `composer install` automaticamente.
- O docroot serve `public/` via o `.htaccess` da raiz (rastreado no git — nao remover).
- `.env` de producao vive no docroot e e preservado entre deploys; nunca commitar.
- O clone limpa arquivos nao rastreados: `vendor/`, `bootstrap/cache` e o symlink `public/storage` sao recriados pelo deploy (`composer install` + `post-autoload-dump`: `storage:link` e `migrate --force`).
- Cron obrigatorio (ja configurado): `* * * * * php .../public_html/artisan schedule:run`.
- Se precisar rodar comandos manualmente em producao: `/admin/comandos` (whitelist: migrate, optimize, storage:link, seeders etc.).
- Banco: MySQL `u694151234_declock` em `localhost` (host interno; `srv792.hstgr.io` e so para acesso externo).

## Notas operacionais (sessao UX/permissoes)

- **Permissoes**: `PerfisEscolaresSeeder` NAO re-sincroniza roles ja existentes — apos editar padroes de um perfil, aplicar direto em `role_has_permissions` (roles sao por empresa — ha um role por perfil por tenant). Permissoes diretas por usuario: concessao em `model_has_permissions`, negacao em `user_permission_denies` (superadmin gerencia em `usuario.show`).
- **Views padrao de listagem**: `partials/ux-index-styles` + `partials/ux-view-toggle` (cards/tabela com localStorage `*_index_view`) + `partials/entity-picker` (modal de busca para entidades grandes).
- **Checkboxes**: `icheck-bootstrap` global via `config/adminlte.php` + `public/js/app-custom.js` (auto-aplica `icheck-primary` em `.form-check` com label).
- **Compilar blades**: `php artisan view:cache && php artisan view:clear` (dev) para validar sintaxe.
- **Mobile**: grid `repeat(7,1fr)` de calendarios vira marcador colorido em <=576px; tabelas sempre em `table-responsive`; `col-6` bare e o padrao para stats 2-por-linha no celular.
