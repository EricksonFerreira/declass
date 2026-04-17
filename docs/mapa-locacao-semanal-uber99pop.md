# Mapa de locacao semanal para Uber/99/Pop

Este documento descreve como o DeCars pode evoluir para atender locacao semanal voltada a motoristas de aplicativo, sem quebrar o fluxo atual de locacao tradicional.

## Resumo executivo

O sistema atual ja cobre bem:
- reserva por periodo
- disponibilidade por veiculo
- check-in e checkout
- danos e multas
- pagamento e cobranca
- regras por empresa e por estacao

O que ainda falta para uma operacao semanal de app:
- pacote semanal com preco fixo
- renovacao de contrato por semana
- controle de km
- regras de uso para motorista de app
- vistoria de saida e retorno com odometro
- fluxo de troca de veiculo
- alertas de vencimento e manutencao

## 1. O que pode ser reaproveitado

### 1.1 Reserva
- Estrutura de `data_inicio` e `data_fim`.
- Calculo de dias.
- Validacao de conflito de periodo.
- Selecao de veiculo disponivel.
- Controle por empresa e por estacao.

### 1.2 Operacao
- Check-in digital.
- Checkout digital.
- Registro de danos.
- Cobrancas de avaria.
- Multas.
- Movimentacao entre estacoes.

### 1.3 Financeiro
- Pagamento da reserva.
- Cupom de desconto.
- Taxas adicionais.
- Calculo do valor final.

## 2. O que precisa ser criado

### 2.1 Tipo de locacao
Criar um conceito novo para definir a modalidade do contrato:
- diaria
- semanal
- quinzenal
- mensal

Sugestao:
- adicionar um campo como `tipo_locacao` na reserva ou em uma tabela de configuracao.

### 2.2 Plano semanal
Criar um pacote proprio para:
- valor fixo por semana
- quantidade minima de dias
- valor de renovacao
- regras de caucao
- regras de franquia

### 2.3 Controle de km
Criar:
- km inicial
- km final
- km previsto por semana
- km excedente
- valor por km excedente

### 2.4 Motorista de app
Criar cadastro especifico para o condutor:
- nome
- CPF/CNPJ
- CNH
- categoria
- validade
- comprovante de residencia
- comprovante de atividade

### 2.5 Contrato semanal
Criar contrato com:
- data de inicio
- data de fim
- renovacao automatica ou manual
- veiculo substituto
- regras de uso
- multa por atraso

## 3. Modulos que precisam crescer

### 3.1 Reserva
Adicionar suporte a:
- preco semanal
- renovacao de periodo
- troca de veiculo na renovacao
- status de contrato semanal

### 3.2 Check-in / checkout
Adicionar campos e telas para:
- km do veiculo
- combustivel
- estado dos pneus
- fotos obrigatorias
- assinatura do condutor

### 3.3 Financeiro
Adicionar:
- cobranca recorrente semanal
- multa por atraso
- reajuste por excesso de km
- cobranca por avaria no retorno

### 3.4 Frota
Adicionar:
- veiculo apto para app
- veiculo em reserva semanal
- veiculo reserva de emergencia
- veiculo em manutencao

## 4. Estrutura recomendada

### 4.1 Nivel de empresa
Marcar a empresa com uma modalidade habilitada:
- locacao tradicional
- locacao semanal
- as duas juntas

### 4.2 Nivel de contrato
Cada reserva deve saber:
- tipo de locacao
- periodicidade
- valor base
- km incluso
- km excedente
- caucao

### 4.3 Nivel de veiculo
Cada veiculo deve saber:
- disponivel
- reservado
- em revisao
- em troca
- liberado para app

## 5. Ordem de implementacao

### Fase 1
- Criar o tipo de locacao.
- Marcar empresa como habilitada para locacao semanal.
- Permitir selecionar reserva semanal no admin e no portal.

### Fase 2
- Criar preco semanal.
- Ajustar calculo de valores.
- Permitir renovacao de contrato.

### Fase 3
- Criar controle de km.
- Ajustar check-in e checkout.
- Amarrar km com cobranca adicional.

### Fase 4
- Criar fluxo de motorista de app.
- Criar contrato semanal.
- Criar alertas de vencimento.

### Fase 5
- Criar troca de veiculo.
- Criar manutencao programada.
- Criar relatorios especificos.

## 6. Impacto na tela de reservas

A tela de reserva atual precisaria de:
- um seletor de modalidade
- exibicao do valor por semana
- resumo de km incluso
- aviso de renovacao
- campo de observacoes do contrato

## 7. Impacto na operacao diaria

A operacao precisaria de:
- vistoria mais rigorosa na saida
- vistoria no retorno
- controle de tanque e km
- fluxo de renovacao semanal
- fluxo de cancelamento e troca

## 8. Regras de negocio recomendadas

- Se o veiculo ultrapassar km previsto, cobrar excedente.
- Se o contrato vencer sem renovacao, bloquear nova saida.
- Se houver dano na devolucao, gerar cobranca automaticamente.
- Se o motorista for de app, exigir validacao documental.
- Se a empresa nao tiver modalidade semanal habilitada, esconder o fluxo.

## 9. O que nao misturar

- Nao misturar plano da empresa com permissao do usuario.
- Nao misturar reserva diaria com contrato semanal sem um tipo de locacao claro.
- Nao misturar controle de km com valor diario padrao sem regra de conversao.

## 10. Conclusao

O DeCars pode atender locacao semanal para Uber/99/Pop, mas hoje isso deve ser tratado como uma extensao de produto, nao como algo pronto de ponta a ponta.

O melhor caminho e:
- reaproveitar reserva, pagamento, check-in, checkout e danos
- criar uma camada especifica de modalidade semanal
- adicionar controle de km e renovacao
- criar contrato e regras para motorista de app

