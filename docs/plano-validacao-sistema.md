# Plano de validacao do sistema DECLOCK

Documento de homologacao para um usuario validar se o sistema esta funcionando de ponta a ponta.

Foco principal:

- validar o isolamento por empresa
- validar cadastros basicos
- validar permissao por estacao e por usuario
- validar o fluxo completo de reserva
- validar conflito de disponibilidade
- validar pagamento, cupom, movimentacao e pos-reserva

## Como usar este documento

1. Execute os cenarios na ordem.
2. Marque cada item como `OK`, `NOK` ou `OBS`.
3. Se algum caso falhar, anote a empresa, a estacao, o veiculo e a data usada.
4. Use sempre datas futuras e, de preferencia, crie uma reserva com dias consecutivos para testar bloqueios.

## Massa minima recomendada

1. Crie 2 empresas: `Empresa A` e `Empresa B`.
2. Na `Empresa A`, defina o plano mais completo que voce quiser validar.
3. Na `Empresa B`, defina um plano mais simples para validar isolamento.

- `Obs: Após clia-las deve replicar a base de DECLOCK o atributo:Marca + Modelo + Segmentos para essas empresas`

4. Na `Empresa A`, crie 2 estacoes:
    - `Estacao A1` com `checkin = S` e `checkout = S`
    - `Estacao A2` com `checkin = S` e `checkout = N`
5. Na `Empresa B`, crie 1 estacao para validar que os dados nao vazam entre empresas.
6. Crie 2 grupos de frota.
7. Crie 3 modelos de veiculo.
8. Crie pelo menos 4 veiculos:
    - `V1` livre
    - `V2` livre
    - `V3` em manutencao ou indisponivel
    - `V4` para teste de sobreposicao
9. Crie 2 usuarios internos:
    - 1 administrador/gerente
    - 1 atendente com acesso limitado
10. Crie 1 cliente para o site de reservas.
11. Crie 1 cupom valido e 1 cupom invalido/inativo.
12. Crie 1 movimentacao de transferencia entre estacoes.
13. Reserve `V1` em um periodo base, por exemplo:

- retirada em `D1`
- devolucao em `D3`
  Assim voce consegue testar sobreposicoes em `D2` e ao redor do intervalo.

## 1. Contexto da empresa e isolamento

1. `CTX-01` Alterar a empresa em visualizacao como admin e abrir listagens de estacoes, veiculos e reservas. Esperado: tudo deve refletir a empresa selecionada, nao a empresa padrao do usuario logado.
2. `CTX-02` Criar uma estacao com o mesmo nome em duas empresas diferentes. Esperado: permitido, pois a unicidade deve ser por empresa.
3. `CTX-03` Criar duas estacoes com o mesmo nome na mesma empresa. Esperado: bloqueado na segunda tentativa.
4. `CTX-04` Editar uma estacao sem alterar o nome. Esperado: a validacao nao deve acusar duplicidade do proprio registro.
5. `CTX-05` Abrir a listagem de qualquer recurso filtrado por empresa e trocar a empresa em visualizacao. Esperado: a listagem deve mudar junto com o contexto.
6. `CTX-06` Tentar forcar um `empresa_id` de outra empresa em um formulario. Esperado: o sistema deve ignorar ou bloquear essa tentativa.

## 2. Cadastro de base

1. `BASE-01` Criar e editar estacao com nome, contato, horarios e dias uteis. Esperado: salvar sem erro e manter os dados.
2. `BASE-02` Criar grupo de frota com nome e codigo repetindo o teste em outra empresa. Esperado: permitido entre empresas e bloqueado na mesma empresa.
3. `BASE-03` Criar modelo de veiculo vinculando marca e grupo. Esperado: salvar sem inconsistencias e aparecer no combo correto.
4. `BASE-04` Criar veiculo novo e conferir se ele entra na lista de disponibilidade. Esperado: veiculo ativo e livre deve aparecer.
5. `BASE-05` Marcar um veiculo como inativo ou indisponivel. Esperado: ele nao deve aparecer para reserva.
6. `BASE-06` Criar tipo, status e item de checklist. Esperado: validacao por empresa deve permitir nomes iguais entre empresas e bloquear repeticao na mesma empresa.
7. `BASE-07` Criar categoria de dano e dano relacionado. Esperado: salvar corretamente e respeitar o contexto da empresa.
8. `BASE-08` Criar cupom de desconto valido. Esperado: o cupom deve ser encontrado na tela de reserva e aplicado no calculo.
9. `BASE-09` Criar um cupom inativo ou inexistente. Esperado: o sistema nao deve aplicar desconto.
10. `BASE-10` Criar uma movimentacao de transferencia entre estacoes. Esperado: a movimentacao deve aparecer na lista e no historico do veiculo.

## 3. Usuarios e permissoes

1. `USR-01` Criar usuario com acesso a todas as estacoes. Esperado: ele deve enxergar todos os dados permitidos pela empresa.
2. `USR-02` Criar usuario com acesso apenas a 1 estacao. Esperado: a listagem e os combos devem limitar os dados a essa estacao.
3. `USR-03` Criar usuario gerente. Esperado: ele deve ter acesso mais amplo que um atendente.
4. `USR-04` Criar usuario cliente. Esperado: ele nao deve enxergar telas administrativas que sao internas.
5. `USR-05` Tentar editar email ou numero de identificacao para repetir um valor da mesma empresa. Esperado: bloquear duplicidade.
6. `USR-06` Abrir telas administrativas como admin e trocar o contexto da empresa antes de salvar. Esperado: o sistema deve respeitar a empresa em visualizacao.

## 4. Reserva online - configuracao inicial

1. `RES-01` Abrir a pagina publica de reserva com a empresa configurada. Esperado: carregar os passos da reserva e as estacoes habilitadas.
2. `RES-02` Selecionar estacao de retirada com `checkin = S`. Esperado: a estacao deve aparecer nos combos do portal.
3. `RES-03` Selecionar estacao sem `checkout = S` como devolucao. Esperado: o sistema deve bloquear ou nao considerar essa estacao para devolucao.
4. `RES-04` Informar datas anteriores a agora. Esperado: bloqueio com mensagem de validacao.
5. `RES-05` Informar data de devolucao menor ou igual a retirada. Esperado: bloqueio com mensagem de periodo invalido.
6. `RES-06` Selecionar um grupo sem veiculos livres. Esperado: a tela deve avisar indisponibilidade.
7. `RES-07` Selecionar um grupo com varios veiculos livres. Esperado: a tela deve listar os veiculos disponiveis.
8. `RES-08` Marcar a empresa para obrigar selecao de modelo. Esperado: o passo de veiculo/modelo deve exigir o modelo antes de continuar.

## 5. Reserva online - disponibilidade e conflito

1. `RES-09` Criar uma reserva base com `V1` de `D1` a `D3`. Esperado: a reserva deve ser criada com sucesso.
2. `RES-10` Tentar reservar o mesmo `V1` em periodo que cruza `D2`. Esperado: bloquear por sobreposicao.
3. `RES-11` Tentar reservar o mesmo `V1` com periodo totalmente dentro da reserva base. Esperado: bloquear.
4. `RES-12` Tentar reservar o mesmo `V1` com periodo que engloba a reserva base. Esperado: bloquear.
5. `RES-13` Tentar reservar o mesmo `V1` iniciando exatamente no dia final da reserva anterior, sem sobrepor hora. Esperado: permitir somente se o sistema considerar o periodo realmente sem conflito; caso haja sobreposicao de horario, deve bloquear.
6. `RES-14` Tentar reservar `V2` no mesmo periodo de `V1`, com `V2` livre. Esperado: permitir e oferecer `V2`.
7. `RES-15` Tentar reservar um periodo em que um dos veiculos do grupo esta ocupado, mas existe outro veiculo livre. Esperado: o sistema deve mostrar apenas o livre.
8. `RES-16` Tentar reservar um periodo em que todos os veiculos do grupo estao ocupados. Esperado: mostrar indisponibilidade total.
9. `RES-17` Reservar um veiculo que esta em manutencao ou com movimentacao aberta. Esperado: nao permitir a reserva.
10. `RES-18` Reservar um veiculo com uma reserva antiga ja cancelada. Esperado: o cancelamento nao deve continuar bloqueando o veiculo.
11. `RES-19` Reservar um periodo que cruza um feriado cadastrado na estacao. Esperado: o sistema deve respeitar os horarios do feriado e aplicar a regra configurada.
12. `RES-20` Reservar com um horario fora do expediente da estacao. Esperado: a taxa extra de coleta ou entrega deve aparecer no resumo.
13. `RES-21` Reservar com estacao de retirada que cobra taxa fora do expediente. Esperado: o valor adicional deve ser somado no total.
14. `RES-22` Reservar com estacao de devolucao que cobra taxa fora do expediente. Esperado: o valor adicional deve ser somado no total.

## 6. Reserva online - cupom, cliente e pagamento

1. `PAY-01` Aplicar cupom valido no passo de opcional/pagamento. Esperado: o desconto deve aparecer no resumo.
2. `PAY-02` Aplicar cupom invalido. Esperado: o sistema deve informar erro e nao aplicar desconto.
3. `PAY-03` Aplicar cupom inativo ou expirado. Esperado: o sistema nao deve aceitar.
4. `PAY-04` Finalizar uma reserva como usuario nao autenticado quando o sistema exigir login para pagamento. Esperado: redirecionar para login e voltar para a reserva depois.
5. `PAY-05` Finalizar pagamento de uma reserva que pertence a outro usuario. Esperado: bloquear o acesso.
6. `PAY-06` Finalizar pagamento de uma reserva com status que nao permite pagar. Esperado: bloquear a operacao.
7. `PAY-07` Concluir pagamento com sucesso. Esperado: a reserva deve mudar de status e abrir a pagina de sucesso.
8. `PAY-08` Voltar para a pagina de resumo apos pagamento concluido. Esperado: os dados devem permanecer coerentes.
9. `PAY-09` Criar reserva sem gateway configurado. Esperado: o sistema deve avisar ou limitar o passo de pagamento conforme a regra da empresa.
10. `PAY-10` Salvar satisfacao/avaliacao apos a reserva. Esperado: o registro deve ser salvo no contexto correto da empresa.

## 7. Movimentacoes

1. `MOV-01` Criar movimentacao de cadastro do veiculo. Esperado: a movimentacao deve ser registrada no historico.
2. `MOV-02` Criar movimentacao de transferencia entre estacoes com origem e destino validos. Esperado: salvar e refletir no status do veiculo.
3. `MOV-03` Tentar transferir um veiculo que ja esta reservado no periodo. Esperado: bloquear ou avisar indisponibilidade.
4. `MOV-04` Tentar concluir uma movimentacao pendente. Esperado: o sistema deve atualizar o status e liberar o veiculo quando aplicavel.
5. `MOV-05` Abrir a listagem de movimentacoes como usuario limitado. Esperado: mostrar somente os registros das estacoes permitidas.
6. `MOV-06` Verificar o historico de um veiculo depois de uma transferencia e uma reserva. Esperado: o historico deve mostrar a sequencia correta.

## 8. Checklist, danos e cobrancas

1. `CHK-01` Criar tipo de checklist, status de item e item de checklist com nomes repetidos em outra empresa. Esperado: permitido entre empresas e bloqueado na mesma empresa.
2. `CHK-02` Editar tipo, status ou item sem alterar o nome. Esperado: a validacao nao deve acusar duplicidade do proprio registro.
3. `CHK-03` Configurar checklist de retirada e devolucao para uma reserva. Esperado: os itens devem aparecer no fluxo correto.
4. `CHK-04` Registrar dano na vistoria de check-in/check-out. Esperado: o dano deve ser salvo e ligado ao veiculo/reserva.
5. `CHK-05` Abrir uma cobranca por avaria depois do checkout. Esperado: o sistema deve permitir a criacao da cobranca no contexto da reserva.
6. `CHK-06` Tentar criar dano, categoria ou checklist em empresa diferente da selecionada. Esperado: respeitar a empresa em visualizacao.

## 9. Site institucional e apresentacao

1. `SITE-01` Abrir a home publica e validar o destaque do site de vendas. Esperado: o diferencial deve aparecer de forma clara.
2. `SITE-02` Abrir a pagina `Sobre`. Esperado: carregar normalmente e mostrar a assinatura da Sonyd Tech.
3. `SITE-03` Alterar cores e textos do site de apresentacao da empresa. Esperado: o site publico deve refletir as mudancas.
4. `SITE-04` Alterar a imagem principal do site de vendas. Esperado: a nova imagem deve aparecer no portal.
5. `SITE-05` Validar o link de WhatsApp no botao principal. Esperado: abrir conversa com mensagem pre-preenchida.
6. `SITE-06` Abrir a pagina publica em mobile. Esperado: os passos e os cards devem continuar legiveis e utilizaveis.

## 10. Smoke test final

1. `SMK-01` Criar uma empresa, cadastrar estacao, veiculo, usuario, checklist e reserva completa na mesma sessao. Esperado: o fluxo inteiro deve fechar sem erro.
2. `SMK-02` Repetir o mesmo fluxo em outra empresa com os mesmos nomes de cadastro. Esperado: o isolamento por empresa deve impedir conflito entre os registros.
3. `SMK-03` Fazer uma reserva com veiculo livre, depois tentar reservar por cima do mesmo intervalo. Esperado: o segundo teste deve ser bloqueado.
4. `SMK-04` Fazer uma transferencia de veiculo entre estacoes e depois verificar a disponibilidade. Esperado: a disponibilidade deve mudar conforme a movimentacao.
5. `SMK-05` Fazer login como usuario limitado e como admin na mesma empresa. Esperado: as permissoes devem mudar de acordo com o perfil.

## Resultado esperado geral

- A validacao de nomes iguais deve respeitar a empresa selecionada.
- O admin deve trabalhar com a empresa em visualizacao, nao com a empresa antiga do cadastro dele.
- A reserva deve bloquear qualquer sobreposicao de periodo com veiculo ocupado.
- Se houver varios veiculos, o sistema deve oferecer apenas os realmente livres.
- A movimentacao nao pode deixar o veiculo disponivel se ele estiver preso em outro fluxo.
- O cupom e os valores extras devem entrar corretamente no total.
- O site de vendas deve refletir a configuracao da empresa e continuar responsivo.

## Observacao para o validador

Se algum caso falhar, anote:

- a empresa usada
- a estacao usada
- o veiculo usado
- o periodo da reserva
- o usuario logado
- a mensagem exibida na tela

# testar a rotina de reset de senha de usuarios ou de login
