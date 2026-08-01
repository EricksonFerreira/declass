# Mapa do Projeto

Este documento apresenta a organização dos principais módulos do sistema e indica onde estão localizados seus Controllers, Actions, Form Requests, DTOs, Models, Services, Views e Rotas.

Não é necessário listar individualmente todos os arquivos do projeto. O objetivo é facilitar a localização das responsabilidades e a compreensão dos fluxos principais.

---

## Reservas

O módulo de reservas concentra o ciclo completo da locação, desde a criação da reserva até a retirada e a devolução do veículo.

### Estrutura

* Controllers:

  * `app/Http/Controllers/Reserva`

* Actions:

  * `app/Actions/Reserva`
  * `app/Actions/ReservaMotoristaApp`

* Form Requests:

  * `app/Http/Requests/Reserva`
  * `app/Http/Requests/MotoristaApp`

* DTOs:

  * `app/DTO/Reserva`

* Services principais:

  * `app/Services/ReservaService.php`
  * `app/Services/Reserva/ReservaResolver.php`
  * `app/Services/Reserva/ReservaIdentificadorService.php`
  * `app/Services/ReservaCodigoService.php`
  * `app/Services/StatusReservaService.php`

* Models principais:

  * `app/Models/Reserva.php`
  * `app/Models/ReservaMotoristaApp.php`
  * `app/Models/ReservaMotoristaAppPagamento.php`
  * `app/Models/ReservaCodigo.php`
  * `app/Models/ReservaLog.php`

* Views:

  * `resources/views/reserva`
  * `resources/views/plano_motorista_app`

* Rotas:

  * `routes/grupos/reserva.php`

### Fluxos principais

* criação e edição da reserva;
* exibição da reserva;
* pagamento;
* caução;
* retirada do veículo;
* devolução do veículo;
* renovação;
* cancelamento;
* contrato;
* cobrança de avarias.

### Organização das Actions

As Actions de reserva estão separadas por funcionalidade:

```text
app/Actions/Reserva
├── Avaria
├── Checkin
├── Checkout
├── Exibir
├── Pagamento
└── Renovacao
```

As Actions específicas das reservas do motorista de aplicativo ficam em:

```text
app/Actions/ReservaMotoristaApp
```

### Terminologia do sistema

No contexto da locação:

* **Check-in:** retirada ou entrega do veículo ao cliente.
* **Checkout:** devolução ou recebimento do veículo pela locadora.

---

## Financeiro

O módulo financeiro é responsável pelo fluxo de caixa, lançamentos, categorias, transações e integrações financeiras geradas por reservas, multas, cauções e outros módulos.

### Estrutura

* Controller principal:

  * `app/Http/Controllers/FinanceiroController.php`

* Controller de fluxo de caixa:

  * `app/Http/Controllers/FluxoCaixaController.php`

* Actions:

  * `app/Actions/Financeiro`

* Form Requests:

  * `app/Http/Requests/FluxoCaixa`

* Services principais:

  * `app/Services/SaldoService.php`
  * `app/Services/TipoTransacaoService.php`

* Models principais:

  * `app/Models/LancamentoFinanceiro.php`
  * `app/Models/CategoriaFinanceira.php`
  * `app/Models/Transacao.php`
  * `app/Models/TipoTransacao.php`
  * `app/Models/SaldoMovimentacao.php`

* Views:

  * `resources/views/financeiro`

* Rotas:

  * `routes/grupos/financeiro.php`

### Actions principais

* `CreateLancamentoAction.php`
* `UpdateLancamentoAction.php`
* `DeleteLancamentoAction.php`
* `RegistrarLancamentoMultaAction.php`
* `RegistrarReceitaPagamentoReservaAction.php`

### Integrações com outros módulos

O módulo financeiro pode receber lançamentos originados por:

* pagamento de reserva;
* caução;
* multa;
* avaria;
* ordem de serviço;
* documentação veicular;
* renovação de reserva;
* cancelamento e estorno.

### Regra de organização

As regras responsáveis por gerar, alterar, cancelar ou estornar lançamentos devem permanecer em Actions do módulo financeiro.

Os módulos de origem, como Reserva ou Multa, devem acionar essas Actions em vez de cadastrar diretamente um `LancamentoFinanceiro`.

---

## Caução

A caução faz parte do fluxo da reserva, mas possui regras financeiras próprias.

### Estrutura

* Actions:

  * `app/Actions/Reserva/Pagamento/Calcao`

* Service:

  * `app/Services/CalcaoService.php`

* Models:

  * `app/Models/Calcao.php`
  * `app/Models/ConfiguracaoCalcao.php`

### Operações principais

* autorização da caução;
* captura da caução;
* registro de caução presencial;
* salvamento do cartão;
* liberação da caução;
* devolução da caução;
* consulta da caução no check-in.

### Relação com o financeiro

A caução somente deve gerar movimentação financeira quando houver entrada ou saída efetiva de dinheiro.

Uma simples pré-autorização no cartão não representa receita da locadora.

---

## Check-in da Reserva

O check-in representa a retirada do veículo pelo cliente.

### Estrutura

```text
app/Actions/Reserva/Checkin
├── Contrato
├── Diagnostico
├── Exibir
├── Finalizar
├── Realizar
├── Upgrade
└── Veiculos
```

### Responsabilidades

* verificar disponibilidade;
* selecionar o veículo;
* realizar o checklist de retirada;
* registrar quilometragem e combustível;
* gerar e assinar contrato;
* processar caução;
* finalizar a retirada;
* alterar o status da reserva e do veículo.

---

## Checkout da Reserva

O checkout representa a devolução do veículo para a locadora.

### Estrutura

```text
app/Actions/Reserva/Checkout
├── Contrato
├── Dano
├── Exibir
├── Finalizar
├── Realizar
└── TermoDevolucao
```

### Responsabilidades

* realizar checklist de devolução;
* registrar quilometragem e combustível;
* identificar novos danos;
* gerar cobranças adicionais;
* gerar e assinar termo de devolução;
* liberar ou utilizar a caução;
* finalizar a locação;
* disponibilizar novamente o veículo.

---

## Veículos e Frota

O módulo de frota concentra cadastro, classificação, disponibilidade e situação operacional dos veículos.

### Controllers

```text
app/Http/Controllers
```

Os Controllers relacionados devem ser identificados pelos nomes de Frota, Veículo, Grupo de Frota, Marca e Modelo.

### Services principais

* `app/Services/VeiculoService.php`
* `app/Services/FrotaService.php`
* `app/Services/GrupoFrotaService.php`
* `app/Services/MarcaService.php`
* `app/Services/ModeloService.php`
* `app/Services/DisponibilidadeVeiculoService.php`
* `app/Services/DisponibilidadeInicialVeiculoService.php`
* `app/Services/StatusVeiculoService.php`
* `app/Services/UpgradeVeiculoService.php`

### Models principais

* `app/Models/Veiculo.php`
* `app/Models/Frota.php`
* `app/Models/GrupoFrota.php`
* `app/Models/Marca.php`
* `app/Models/Modelo.php`
* `app/Models/StatusVeiculo.php`
* `app/Models/DisponibilidadeInicialVeiculo.php`

### Entendimento do módulo

* **Frota** organiza a estrutura cadastral do veículo: tipo de frota, grupo de frota, marca, modelo, segmento, características e status.
* **Disponibilidade** é calculada a partir da estação atual do veículo, da data/hora de disponibilidade e das movimentações/reservas que o bloqueiam.
* O veículo só deve aparecer como disponível quando o estado operacional permitir, mesmo que o cadastro da frota exista.
* A tela de disponibilidade mensal não substitui a regra de negócio da reserva, apenas a representa visualmente.

### Ponto de coordenação da regra

* `app/Services/VeiculoService.php`
  * consulta base de disponibilidade por período;
  * quantidade por grupo e estação;
  * modelos disponíveis por grupo;
  * estações com veículos disponíveis no período.
* `app/Services/DisponibilidadeVeiculoService.php`
  * monta a grade mensal;
  * transforma reservas e movimentações em faixas visuais;
  * exibe o calendário operacional do veículo.
* `app/Services/MovimentacaoService.php`
  * atualiza `local_disponibilidade` e `data_hora_disponibilidade` quando a movimentação é concluída;
  * mantém o veículo coerente com o histórico operacional.

### Controllers mais relacionados

* `app/Http/Controllers/VeiculoController.php`
* `app/Http/Controllers/GrupoFrotaController.php`
* `app/Http/Controllers/FrotaController.php`
* `app/Http/Controllers/TipoFrotaController.php`
* `app/Http/Controllers/MarcaController.php`
* `app/Http/Controllers/ModeloController.php`
* `app/Http/Controllers/DisponibilidadeInicialVeiculoController.php`
* `app/Http/Controllers/Reserva/ReservaController.php`
* `app/Http/Controllers/SiteReservaController.php`

### Form Requests relacionados

* `app/Http/Requests/Veiculo`
* `app/Http/Requests/Frota`
* `app/Http/Requests/GrupoFrota`
* `app/Http/Requests/TipoFrota`
* `app/Http/Requests/Marca`
* `app/Http/Requests/Modelo`
* `app/Http/Requests/DisponibilidadeInicialVeiculo`

### Models complementares

* `app/Models/FrotaCaracteristica.php`
* `app/Models/CaracteristicaFrota.php`
* `app/Models/CaracteristicaGrupoFrota.php`
* `app/Models/CaracteristicaGrupo.php`
* `app/Models/TipoValorCaracteristicaFrota.php`
* `app/Models/Segmento.php`
* `app/Models/DisponibilidadeInicialVeiculo.php`
* `app/Models/Movimentacao.php`
* `app/Models/StatusMovimentacao.php`
* `app/Models/Reserva.php`
* `app/Models/StatusReserva.php`

### Views principais

* `resources/views/veiculo`
* `resources/views/frota`
* `resources/views/grupo-frota`
* `resources/views/tipo-frota`
* `resources/views/marca`
* `resources/views/modelo`
* `resources/views/disponibilidade-inicial-veiculo`

### Rotas principais

* `routes/grupos/veiculo.php`
* `routes/grupos/frota.php`
* `routes/grupos/reserva.php`

---

## Movimentações de Veículos

O módulo registra mudanças físicas ou operacionais do veículo.

### Services

* `app/Services/MovimentacaoService.php`
* `app/Services/TipoMovimentacaoService.php`
* `app/Services/StatusMovimentacaoService.php`

### Models

* `app/Models/Movimentacao.php`
* `app/Models/MovimentacaoHistorico.php`
* `app/Models/TipoMovimentacao.php`
* `app/Models/StatusMovimentacao.php`

### Exemplos de movimentação

* entrada inicial na estação;
* retirada para locação;
* devolução da locação;
* transferência entre estações;
* envio para manutenção;
* retorno da manutenção;
* indisponibilização;
* disponibilização.

---

## Manutenção

O módulo de manutenção controla ordens de serviço, manutenções preventivas e pendências dos veículos.

### Services principais

* `app/Services/OrdemServicoService.php`

### Models principais

* `app/Models/OrdemServico.php`
* `app/Models/OrdemServicoItem.php`
* `app/Models/OrdemServicoAnexo.php`
* `app/Models/OrdemServicoCampoValor.php`
* `app/Models/PlanoManutencaoPreventiva.php`
* `app/Models/PlanoManutencaoItem.php`
* `app/Models/ItemManutencaoPreventiva.php`
* `app/Models/ExecucaoManutencaoPreventiva.php`
* `app/Models/PendenciaManutencaoPreventiva.php`

---

## Documentação Veicular

O módulo controla documentos, obrigações e parcelas vinculadas aos veículos.

### Models

* `app/Models/DocumentacaoVeiculo.php`
* `app/Models/DocumentacaoVeiculoParcela.php`

### Tipos de documentação

* IPVA;
* licenciamento;
* CRLV;
* seguro.

---

## Multas

### Service

* `app/Services/MultaService.php`

### Models

* `app/Models/Multa.php`
* `app/Models/MultaHistorico.php`

### Integração financeira

Quando uma multa é registrada, o sistema pode gerar uma despesa para a empresa.

Caso seja confirmado que a responsabilidade é do cliente, o módulo deve controlar a cobrança e registrar a receita somente após o pagamento.

---

## Avarias e Danos

### Services

* `app/Services/AvariaCobrancaService.php`
* `app/Services/DanoService.php`
* `app/Services/VeiculoDanoService.php`

### Models

* `app/Models/AvariaCobranca.php`
* `app/Models/Dano.php`
* `app/Models/CategoriaDano.php`
* `app/Models/VeiculoDanos.php`
* `app/Models/VeiculoDanosImagem.php`

### Integração com reservas

A identificação de novos danos acontece principalmente durante o checkout da reserva.

Quando o dano for responsabilidade do cliente, pode ser criada uma cobrança vinculada à reserva.

---

## Pagamentos

### Services

```text
app/Services/Pagamentos
├── GatewayEmpresaConfigService.php
├── GerenciadorDePagamentos.php
├── IPagamento.php
├── MercadoLivreService.php
├── PayPalService.php
└── StripeGatewayService.php
```

### Outros Services relacionados

* `app/Services/ClienteStripeService.php`
* `app/Services/StripePixService.php`

### Responsabilidades

* selecionar o gateway configurado pela empresa;
* criar cobranças;
* confirmar pagamentos;
* consultar pagamentos;
* processar cancelamentos;
* processar estornos;
* registrar os resultados financeiros.

---

## NFSe

### Estrutura

```text
app/Services/Nfse
└── Providers
    ├── NfephpProvider.php
    ├── NfseProviderInterface.php
    └── NuvemFiscalProvider.php
```

### Service principal

* `app/Services/NfseService.php`

### Model

* `app/Models/Nfse.php`

---

## Permissões

### Services principais

* `app/Services/PermissaoService.php`
* `app/Services/PermissaoSyncService.php`
* `app/Services/PlanoPermissaoService.php`
* `app/Services/RegraPermissaoService.php`
* `app/Services/RegraService.php`

### Models principais

* `app/Models/Permissao.php`
* `app/Models/Regra.php`
* `app/Models/TipoUsuario.php`

---

## Estrutura Geral

Para arquivos que ainda não possuem uma seção específica neste documento:

* Controllers:

  * `app/Http/Controllers`

* Actions:

  * `app/Actions`

* Form Requests:

  * `app/Http/Requests`

* DTOs:

  * `app/DTO`

* Models:

  * `app/Models`

* Services:

  * `app/Services`

* Views:

  * `resources/views`

* Rotas:

  * `routes`

---

## Convenções Arquiteturais

### Controllers

Os Controllers devem:

* receber a requisição;
* acionar Form Requests;
* chamar Actions ou Services;
* retornar responses, redirects ou views.

Controllers não devem concentrar regras extensas de negócio.

### Actions

As Actions devem representar casos de uso específicos.

Exemplos:

* realizar check-in;
* finalizar checkout;
* confirmar pagamento;
* registrar lançamento financeiro;
* renovar reserva.

### Services

Os Services devem concentrar regras reutilizáveis, integrações ou operações compartilhadas entre diferentes casos de uso.

### Form Requests

Os Form Requests devem concentrar:

* validação;
* autorização da requisição;
* normalização simples dos dados recebidos.

### DTOs

Os DTOs devem transportar dados estruturados entre Controllers, Actions e Services, evitando o envio de arrays sem contrato definido.

### Models

Os Models devem representar as entidades e seus relacionamentos.

Regras extensas de fluxo não devem ser concentradas diretamente nos Models.

---

## Como manter este documento

Ao criar um módulo novo:

1. adicionar uma seção para o domínio;
2. informar os diretórios principais;
3. listar somente os arquivos centrais;
4. explicar os fluxos e responsabilidades;
5. registrar as integrações com outros módulos.

Não é necessário adicionar todos os arquivos individualmente. A estrutura interna já pode ser consultada diretamente no projeto.
