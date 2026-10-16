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

## Deploy (Hostinger — declock.app)

- Auto-deploy Git ativo: push na `main` (repo `David-e-Erickson/declock`) clona no docroot `public_html` e roda `composer install` automaticamente.
- O docroot serve `public/` via o `.htaccess` da raiz (rastreado no git — nao remover).
- `.env` de producao vive no docroot e e preservado entre deploys; nunca commitar.
- O clone limpa arquivos nao rastreados: `vendor/`, `bootstrap/cache` e o symlink `public/storage` sao recriados pelo deploy (`composer install` + `post-autoload-dump`: `storage:link` e `migrate --force`).
- Cron obrigatorio (ja configurado): `* * * * * php .../public_html/artisan schedule:run`.
- Se precisar rodar comandos manualmente em producao: `/admin/comandos` (whitelist: migrate, optimize, storage:link, seeders etc.).
- Banco: MySQL `u694151234_declock` em `localhost` (host interno; `srv792.hstgr.io` e so para acesso externo).
