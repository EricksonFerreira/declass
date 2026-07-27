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
