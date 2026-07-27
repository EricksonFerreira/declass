# Mapa do Projeto

## Reservas

- Controllers:
  - `app/Http/Controllers/Reserva`
- Actions:
  - `app/Actions/Reserva`
  - `app/Actions/ReservaMotoristaApp`
- Form Requests:
  - `app/Http/Requests/Reserva`
  - `app/Http/Requests/MotoristaApp`
- DTOs:
  - `app/DTO/Reserva`
- Models:
  - `app/Models/Reserva.php`
  - `app/Models/ReservaMotoristaApp.php`
  - `app/Models/ReservaMotoristaAppPagamento.php`
  - `app/Models/ReservaCodigo.php`
  - `app/Models/ReservaLog.php`
- Views:
  - `resources/views/reserva`
  - `resources/views/plano_motorista_app`
- Rotas:
  - `routes/grupos/reserva.php`

## Financeiro

- Controller principal:
  - `app/Http/Controllers/FinanceiroController.php`
- Controller de fluxo de caixa:
  - `app/Http/Controllers/FluxoCaixaController.php`
- Actions:
  - `app/Actions/Financeiro`
- Form Requests:
  - `app/Http/Requests/FluxoCaixa`
- Models:
  - `app/Models/LancamentoFinanceiro.php`
  - `app/Models/CategoriaFinanceira.php`
  - `app/Models/Transacao.php`
  - `app/Models/TipoTransacao.php`
- Views:
  - `resources/views/financeiro`
- Rotas:
  - `routes/grupos/financeiro.php`
