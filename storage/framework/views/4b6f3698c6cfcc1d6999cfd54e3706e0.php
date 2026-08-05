<?php $__env->startSection('title', $titulo ?? ('Pagamento da Reserva #' . $reserva->id)); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => $breadcrumb] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Breadcrumb::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $attributes = $__attributesOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__attributesOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $component = $__componentOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__componentOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $moeda   = strtoupper((string) ($moedaPadrao ?? 'BRL'));
    $simbolo = $moeda === 'USD' ? '$' : ($moeda === 'EUR' ? 'EUR' : 'R$');

    $dias    = (int) ($reserva->quantidade_dias ?? 0);
    $codigoExibicao = '#' . $reserva->id;
    $clientePagamento = $reserva->usuario;

    $tarifaBase      = (float) ($reserva->tarifa_base ?? 0);
    $valorTarifaBase = $tarifaBase * $dias;

    $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);
    if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
        $valorOpcionais = (float) $reserva->opcionais->sum(fn ($o) => (float) ($o->pivot->preco_total ?? 0));
    }

    $taxaColeta         = (float) ($taxaColeta  ?? 0);
    $taxaEntrega        = (float) ($taxaEntrega ?? 0);
    $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
    $cupomCodigo        = $reserva->cupomDesconto?->codigo ?? null;

    $subtotal      = $valorTarifaBase + $valorOpcionais + $taxaColeta + $taxaEntrega;
    $valorDesconto = $subtotal * ($percentualDesconto / 100);
    $valorTotal    = max(0, $subtotal - $valorDesconto);
    $statusPagamentoAprovado = ['succeeded', 'approved', 'paid', 'completed', 'complete', 'confirmed', 'concluido'];
    $totalPagoAprovado = (float) ($totalPagoAprovado ?? ($reserva->transacoes ?? collect())
        ->filter(fn ($item) => (float) ($item->valor ?? 0)
            && in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoAprovado, true))
        ->sum('valor'));
    $saldoPendente = (float) ($saldoPendente ?? max(0, $valorTotal - $totalPagoAprovado));
    $valorCobranca = (float) ($valorCobranca ?? $saldoPendente);
    $manualPaymentAction = route('reserva.confirmarPagamentoDinheiro', $reserva->id);
    $cardPaymentAction = route('reserva.processarPagamento', $reserva->id);
    $backRoute = route('reserva.show', $reserva->id);
    $adicionarMetodoPagamentoUrl = route('usuario.adicionarMetodoPagamento', $reserva->user_id);
    $estacaoInicioNome = $reserva->estacaoRetirada?->nome ?? 'Local não informado';
    $estacaoFimNome = $reserva->estacaoDevolucao?->nome ?? 'Local não informado';
    $dataInicioPagamento = $reserva->data_inicio;
    $dataFimPagamento = $reserva->data_fim;
    $resumoPeriodoTexto = $reserva->grupoFrota?->nome ?? 'Grupo não informado';
    if ($reserva->modelos?->nome) {
        $resumoPeriodoTexto .= ' · ' . $reserva->modelos->nome;
    }

    $gateway = $gatewayPadrao ?? 'stripe';
    $temMetodosPagamento = !empty($metodosPagamento) && count($metodosPagamento) > 0;
    $tooltipSemMetodoPagamento = 'Adicione um método de pagamento salvo para habilitar o pagamento.';
?>

<div class="ep-page">

    
    <div class="ep-hero mb-4">
        <div class="ep-hero__left">
            <div class="ep-hero__icon"><i class="fas fa-credit-card"></i></div>
            <div>
                <div class="ep-hero__eyebrow">RESERVA <?php echo e($codigoExibicao); ?></div>
                <h1 class="ep-hero__title">Faturamento e Pagamento</h1>
                <p class="ep-hero__sub">Confirme os valores e processe o pagamento da reserva.</p>
            </div>
        </div>
        <div class="ep-hero__right">
            <div class="ep-hero__kpis">
                <div class="ep-kpi">
                    <div class="ep-kpi__label">VALOR A COBRAR</div>
                    <div class="ep-kpi__val"><?php echo e($simbolo); ?> <?php echo e(number_format($valorCobranca, 2, ',', '.')); ?></div>
                </div>
                <div class="ep-kpi ep-kpi--mid">
                    <div class="ep-kpi__label">DURAÇÃO</div>
                    <div class="ep-kpi__val"><?php echo e($dias); ?> <?php echo e($dias === 1 ? 'dia' : 'dias'); ?></div>
                </div>
                <?php if($percentualDesconto > 0): ?>
                <div class="ep-kpi ep-kpi--gold">
                    <div class="ep-kpi__label">DESCONTO</div>
                    <div class="ep-kpi__val ep-kpi__val--gold"><?php echo e(number_format($percentualDesconto, 0)); ?>%</div>
                </div>
                <?php endif; ?>
            </div>
            <a href="<?php echo e($backRoute); ?>" class="ep-hero__back">
                <i class="fas fa-arrow-left mr-1"></i> Voltar à Reserva
            </a>
        </div>
    </div>

    
    <?php $__currentLoopData = ['success','warning','error']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(session($type)): ?>
            <div class="alert alert-<?php echo e($type === 'error' ? 'danger' : $type); ?> alert-dismissible fade show mb-3">
                <?php echo e(session($type)); ?>

                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger mb-3">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($error); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div class="row">

        
        <div class="col-lg-7 mb-4">

            
            <div class="ep-card mb-3">
                <div class="ep-card__header ep-card__header--blue">
                    <div class="ep-card__icon"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="ep-card__title">Cliente</div>
                        <div class="ep-card__sub">Dados do locatário nesta reserva</div>
                    </div>
                </div>
                <div class="ep-card__body">
                    <div class="ep-client">
                        <div class="ep-client__avatar">
                            <?php echo e(strtoupper(substr($clientePagamento?->nome ?? $clientePagamento?->name ?? 'C', 0, 1))); ?>

                        </div>
                        <div>
                            <div class="ep-client__name"><?php echo e($clientePagamento?->nome ?? $clientePagamento?->name ?? '—'); ?></div>
                            <div class="ep-client__info"><?php echo e($clientePagamento?->email ?? '—'); ?></div>
                            <?php if(!empty($clientePagamento?->telefone)): ?>
                                <div class="ep-client__info"><?php echo e($clientePagamento->telefone); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="ep-card mb-3">
                <div class="ep-card__header ep-card__header--gray">
                    <div class="ep-card__icon ep-card__icon--gray"><i class="fas fa-calendar-alt"></i></div>
                    <div>
                        <div class="ep-card__title">Período da Locação</div>
                        <div class="ep-card__sub">Retirada e devolução</div>
                    </div>
                </div>
                <div class="ep-card__body">
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <div class="ep-period__label">RETIRADA</div>
                            <div class="ep-period__station">
                                <i class="fas fa-map-marker-alt text-success mr-1"></i>
                                <?php echo e($estacaoInicioNome); ?>

                            </div>
                            <div class="ep-period__date"><?php echo e(\Carbon\Carbon::parse($dataInicioPagamento)->format('d/m/Y H:i')); ?></div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <div class="ep-period__label">DEVOLUÇÃO</div>
                            <div class="ep-period__station">
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                <?php echo e($estacaoFimNome); ?>

                            </div>
                            <div class="ep-period__date"><?php echo e(\Carbon\Carbon::parse($dataFimPagamento)->format('d/m/Y H:i')); ?></div>
                        </div>
                    </div>
                    <div class="ep-period__dias">
                        <i class="fas fa-moon mr-1 text-primary"></i>
                        <strong><?php echo e($dias); ?></strong> <?php echo e($dias === 1 ? 'diária' : 'diárias'); ?>

                        &nbsp;·&nbsp; <?php echo e($resumoPeriodoTexto); ?>

                    </div>
                </div>
            </div>

            <div class="ep-card mb-3">
                <div class="ep-card__header ep-card__header--orange">
                    <div class="ep-card__icon ep-card__icon--orange"><i class="fas fa-cash-register"></i></div>
                    <div>
                        <div class="ep-card__title">Registrar pagamento manual</div>
                        <div class="ep-card__sub">Uso administrativo para pagamento presencial ou externo</div>
                    </div>
                </div>
                <div class="ep-card__body">
                    <div class="ep-tip mb-3">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        Use esta opcao quando o cliente pagar na empresa ou por um meio externo. O valor informado sera inserido no historico da reserva.
                    </div>
                    <form id="manual-payment-form" action="<?php echo e($manualPaymentAction); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label class="ep-form-label"><i class="fas fa-wallet mr-1"></i> Metodo de pagamento</label>
                            <select name="metodo_pagamento" class="form-control form-control-sm" required>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="pix">PIX</option>
                                <option value="cartao_credito">Cartao de credito presencial</option>
                                <option value="cartao_debito">Cartao de debito presencial</option>
                                <option value="boleto">Boleto</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="ep-form-label"><i class="fas fa-dollar-sign mr-1"></i> Valor recebido</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><?php echo e($simbolo); ?></span></div>
                                <input type="number" name="valor" class="form-control form-control-sm" step="0.01" min="0.01" max="<?php echo e($valorCobranca); ?>" value="<?php echo e($valorCobranca); ?>" required>
                                <div class="input-group-append"><span class="input-group-text text-muted" style="font-size:11px;">max. <?php echo e($simbolo); ?> <?php echo e(number_format($valorCobranca, 2, ',', '.')); ?></span></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-success btn-block">
                            <i class="fas fa-check mr-2"></i> Registrar pagamento manual
                        </button>
                    </form>
                </div>
            </div>

            
            <div class="ep-card mb-3">
                <div class="ep-card__header ep-card__header--green">
                    <div class="ep-card__icon ep-card__icon--green"><i class="fas fa-credit-card"></i></div>
                    <div>
                        <div class="ep-card__title">Processar Pagamento</div>
                        <div class="ep-card__sub">
                            <strong>Cartão de crédito</strong>
                            &nbsp;·&nbsp; Moeda: <strong><?php echo e($moeda); ?></strong>
                        </div>
                    </div>
                </div>
                <div class="ep-card__body">
                    <div class="ep-tip mb-3">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        O saldo abaixo será cobrado no método selecionado. Após confirmar, o sistema registrará a transação e atualizará o status da reserva.
                    </div>

                    <form id="payment-form" action="<?php echo e($cardPaymentAction); ?>" method="POST">
                        <?php echo csrf_field(); ?>

                        <?php if($gateway === 'stripe'): ?>
                        <div class="form-group">
                            <label class="ep-form-label"><i class="fas fa-wallet mr-1"></i> Método de Pagamento</label>
                            <div class="d-flex" style="gap:.5rem">
                                <select id="payment-method" class="form-control form-control-sm" name="payment_method">
                                    <?php if(count($metodosPagamento) == 0): ?>
                                        <option value="" disabled selected>Nenhum método cadastrado</option>
                                    <?php else: ?>
                                        <?php $__currentLoopData = $metodosPagamento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metodo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($metodo['id']); ?>">
                                                <?php echo e(strtoupper($metodo['card']->brand)); ?> ****<?php echo e(substr($metodo['card']->last4, -4)); ?>

                                                (<?php echo e($metodo['card']->exp_month); ?>/<?php echo e($metodo['card']->exp_year); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </select>
                                <button type="button" id="openMetodoModal" class="btn btn-sm btn-outline-primary" style="white-space:nowrap">
                                    <i class="fas fa-plus"></i> Novo
                                </button>
                            </div>
                            <?php if(empty($stripePublicKey)): ?>
                                <small class="text-muted">Chave pública do Stripe não configurada.</small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="ep-form-label"><i class="fas fa-dollar-sign mr-1"></i> Valor a pagar</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><?php echo e($simbolo); ?></span></div>
                                <input type="number" id="valorPagamento" name="valor_pagamento" class="form-control form-control-sm" step="0.01" min="0.01" max="<?php echo e($valorCobranca); ?>" value="<?php echo e($valorCobranca); ?>" required>
                                <div class="input-group-append"><span class="input-group-text text-muted" style="font-size:11px;">máx. <?php echo e($simbolo); ?> <?php echo e(number_format($valorCobranca, 2, ',', '.')); ?></span></div>
                            </div>
                            <small class="text-muted">Valor total do pagamento. Não pode ultrapassar o saldo pendente.</small>
                        </div>

                        <div class="ep-total-box mb-3">
                            <div class="ep-total-box__label">Saldo pendente</div>
                            <div class="ep-total-box__value"><?php echo e($simbolo); ?> <?php echo e(number_format($valorCobranca, 2, ',', '.')); ?></div>
                        </div>

                        <div class="form-group">
                            <label class="ep-form-label"><i class="fas fa-calendar-alt mr-1"></i> Parcelamento</label>
                            <select id="installments" name="installments" class="form-control form-control-sm">
                                <?php
                                    $limiteSemJuros = 3;
                                ?>
                                <option value="1">À vista — <?php echo e($simbolo); ?> <?php echo e(number_format($valorCobranca, 2, ',', '.')); ?></option>
                                <?php $__currentLoopData = [2,3,4,5,6,7,8,9,10,11,12]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $valorParcela = $valorCobranca / $parc;
                                        $comJuros = $parc > $limiteSemJuros;
                                    ?>
                                    <option value="<?php echo e($parc); ?>">
                                        <?php echo e($parc); ?>x de <?php echo e($simbolo); ?> <?php echo e(number_format($valorParcela, 2, ',', '.')); ?>

                                        <?php if($comJuros): ?>
                                            — Com juros
                                        <?php else: ?>
                                            — Sem juros
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted">Até <?php echo e($limiteSemJuros); ?>x sem juros. Acima disso, consulte as taxas aplicáveis pela operadora.</small>
                        </div>

                        <input type="hidden" name="payment_method_id" id="payment_method_id" value="">

                        <span class="ep-pay-btn-wrap" data-tooltip="<?php echo e($temMetodosPagamento ? '' : $tooltipSemMetodoPagamento); ?>" title="<?php echo e($temMetodosPagamento ? '' : $tooltipSemMetodoPagamento); ?>">
                            <button
                                type="submit"
                                id="btn-submit-pagamento"
                                class="btn btn-success btn-block ep-btn-pay"
                                <?php echo e($temMetodosPagamento ? '' : 'disabled aria-disabled=true'); ?>

                            >
                                <i class="fas fa-lock mr-2"></i> Confirmar e Processar Pagamento
                            </button>
                        </span>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="col-lg-5 mb-4">
            <div class="ep-card ep-card--sticky">
                <div class="ep-card__header ep-card__header--dark">
                    <div class="ep-card__icon ep-card__icon--dark"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div class="ep-card__title">Resumo Financeiro</div>
                        <div class="ep-card__sub">Composição do valor da reserva</div>
                    </div>
                </div>
                <div class="ep-card__body">

                    
                    <?php if($reserva->grupoFrota?->nome): ?>
                    <div class="ep-summary-row ep-summary-row--group">
                        <span><i class="fas fa-car mr-1"></i> <?php echo e($reserva->grupoFrota->nome); ?></span>
                    </div>
                    <div class="ep-divider"></div>
                    <?php endif; ?>

                    
                    <div class="ep-summary-row">
                        <span>Tarifa base</span>
                        <span><?php echo e($simbolo); ?> <?php echo e(number_format($tarifaBase, 2, ',', '.')); ?>/dia</span>
                    </div>
                    <div class="ep-summary-row">
                        <span><?php echo e($dias); ?> <?php echo e($dias === 1 ? 'diária' : 'diárias'); ?></span>
                        <span><?php echo e($simbolo); ?> <?php echo e(number_format($valorTarifaBase, 2, ',', '.')); ?></span>
                    </div>

                    
                    <?php if($valorOpcionais > 0): ?>
                    <div class="ep-summary-row">
                        <span>Opcionais</span>
                        <span><?php echo e($simbolo); ?> <?php echo e(number_format($valorOpcionais, 2, ',', '.')); ?></span>
                    </div>
                    <?php endif; ?>

                    
                    <?php if($taxaColeta > 0): ?>
                    <div class="ep-summary-row ep-summary-row--taxa">
                        <span><i class="fas fa-exclamation-triangle mr-1"></i> * Retirada fora do expediente</span>
                        <span><?php echo e($simbolo); ?> <?php echo e(number_format($taxaColeta, 2, ',', '.')); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if($taxaEntrega > 0): ?>
                    <div class="ep-summary-row ep-summary-row--taxa">
                        <span><i class="fas fa-exclamation-triangle mr-1"></i> * Devolução fora do expediente</span>
                        <span><?php echo e($simbolo); ?> <?php echo e(number_format($taxaEntrega, 2, ',', '.')); ?></span>
                    </div>
                    <?php endif; ?>

                    
                    <?php if($percentualDesconto > 0): ?>
                    <div class="ep-divider"></div>
                    <div class="ep-summary-row ep-summary-row--discount">
                        <span>
                            <i class="fas fa-tag mr-1"></i> Desconto (<?php echo e(number_format($percentualDesconto, 0)); ?>%)
                            <?php if($cupomCodigo): ?>
                                <span class="ep-cupom-badge"><?php echo e($cupomCodigo); ?></span>
                            <?php endif; ?>
                        </span>
                        <span>&minus; <?php echo e($simbolo); ?> <?php echo e(number_format($valorDesconto, 2, ',', '.')); ?></span>
                    </div>
                    <?php endif; ?>

                    
                    <div class="ep-total-final">
                        <?php if($percentualDesconto > 0): ?>
                            <span class="ep-total-final__crossed"><?php echo e($simbolo); ?> <?php echo e(number_format($subtotal, 2, ',', '.')); ?></span>
                        <?php endif; ?>
                        <span class="ep-total-final__label">Total</span>
                        <strong class="ep-total-final__val"><?php echo e($simbolo); ?> <?php echo e(number_format($valorTotal, 2, ',', '.')); ?></strong>
                    </div>

                    <div class="ep-summary-row mt-2">
                        <span>Pago aprovado</span>
                        <span><?php echo e($simbolo); ?> <?php echo e(number_format($totalPagoAprovado, 2, ',', '.')); ?></span>
                    </div>
                    <div class="ep-summary-row" style="font-weight:700;color:#b45309">
                        <span>Saldo pendente</span>
                        <span><?php echo e($simbolo); ?> <?php echo e(number_format($saldoPendente, 2, ',', '.')); ?></span>
                    </div>

                    <div class="ep-note mt-3">
                        <i class="fas fa-shield-alt text-success mr-1"></i>
                        <small>O pagamento é processado com segurança via <strong><?php echo e(strtoupper($gateway)); ?></strong>. O cliente receberá uma confirmação após o processamento.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php if($gateway === 'stripe'): ?>
<div class="modal fade" id="modalMetodoPagamento" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:14px;overflow:hidden">
            <div class="modal-header" style="background:linear-gradient(120deg,#f0f4ff,#e8f0fe)">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-credit-card mr-2 text-primary"></i> Adicionar Cartão
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Insira os dados do cartão. As informações são transmitidas com segurança diretamente ao Stripe e <strong>não ficam armazenadas</strong> em nossos servidores.
                </p>
                <?php if(empty($stripePublicKey)): ?>
                    <div class="alert alert-warning small mb-3">
                        É necessário configurar a chave pública do Stripe para salvar um novo método de pagamento.
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="ep-form-label">Dados do cartão</label>
                    <div id="card-element" class="form-control" style="padding-top:10px;border-radius:8px"></div>
                    <div id="card-errors" class="text-danger small mt-2" role="alert"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarMetodo">
                    <i class="fas fa-save mr-1"></i> Salvar Cartão
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
.ep-page { padding-bottom: 2rem; }

/* Hero */
.ep-hero {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
    background: linear-gradient(135deg, #0f2740 0%, #1a3a5c 60%, #1e4976 100%);
    border-radius: 16px; padding: 1.6rem 2rem; color: #fff;
}
.ep-hero__left  { display: flex; align-items: center; gap: 1.1rem; }
.ep-hero__icon  {
    width: 56px; height: 56px; border-radius: 14px;
    background: rgba(255,255,255,.12); display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: #fff; flex-shrink: 0;
}
.ep-hero__eyebrow { font-size: .7rem; font-weight: 700; letter-spacing: .1em; color: #f59e0b; margin-bottom: .2rem; }
.ep-hero__title   { font-size: 1.35rem; font-weight: 700; color: #fff; margin: 0 0 .15rem; }
.ep-hero__sub     { font-size: .82rem; color: #94b8d4; margin: 0; }
.ep-hero__right   { display: flex; flex-direction: column; align-items: flex-end; gap: .8rem; }
.ep-hero__kpis    { display: flex; gap: 1.5rem; }
.ep-kpi           { text-align: right; }
.ep-kpi__label    { font-size: .65rem; font-weight: 700; letter-spacing: .08em; color: #94b8d4; }
.ep-kpi__val      { font-size: 1.15rem; font-weight: 700; color: #fff; }
.ep-kpi__val--gold { color: #f59e0b; }
.ep-hero__back {
    font-size: .8rem; color: #94b8d4; border: 1px solid rgba(255,255,255,.2);
    border-radius: 8px; padding: .35rem .85rem; text-decoration: none;
    transition: background .2s;
}
.ep-hero__back:hover { background: rgba(255,255,255,.1); color: #fff; text-decoration: none; }

/* Cards */
.ep-card { border: 1px solid #e5eaf2; border-radius: 14px; overflow: hidden; background: #fff; }
.ep-card--sticky { position: sticky; top: 80px; }
.ep-card__header {
    display: flex; align-items: center; gap: .85rem;
    padding: .85rem 1.1rem;
}
.ep-card__header--blue  { background: linear-gradient(120deg,#f0f4ff,#e8f0fe); }
.ep-card__header--green { background: linear-gradient(120deg,#edfff4,#e8f9f0); }
.ep-card__header--orange { background: linear-gradient(120deg,#fff7ed,#ffedd5); }
.ep-card__header--gray  { background: #f8f9fa; }
.ep-card__header--dark  { background: linear-gradient(120deg,#f4f6f9,#eef0f4); }
.ep-card__icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(75,128,255,.12); display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: #4b80ff; flex-shrink: 0;
}
.ep-card__icon--green { background: rgba(40,167,69,.12); color: #28a745; }
.ep-card__icon--orange { background: rgba(249,115,22,.12); color: #ea580c; }
.ep-card__icon--gray  { background: rgba(108,117,125,.1); color: #6c757d; }
.ep-card__icon--dark  { background: rgba(33,54,79,.1); color: #21364f; }
.ep-card__title { font-size: .9rem; font-weight: 700; color: #21364f; }
.ep-card__sub   { font-size: .76rem; color: #6c757d; }
.ep-card__body  { padding: 1.1rem; }

/* Client */
.ep-client        { display: flex; align-items: center; gap: .85rem; }
.ep-client__avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: linear-gradient(135deg,#4b80ff,#6f9fff);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 1.1rem; color: #fff; flex-shrink: 0;
}
.ep-client__name { font-weight: 600; color: #21364f; }
.ep-client__info { font-size: .8rem; color: #6c757d; }

/* Period */
.ep-period__label   { font-size: .68rem; font-weight: 700; letter-spacing: .06em; color: #9aacbf; text-transform: uppercase; margin-bottom: .2rem; }
.ep-period__station { font-weight: 600; color: #21364f; font-size: .88rem; }
.ep-period__date    { font-size: .8rem; color: #6c757d; }
.ep-period__dias    { background: #f4f8ff; border-radius: 8px; padding: .5rem .85rem; font-size: .82rem; color: #2c4a6e; margin-top: .85rem; }

/* Tip */
.ep-tip { display: flex; align-items: flex-start; gap: .5rem; font-size: .82rem; background: #f4f8ff; border: 1px solid #d0e3ff; border-radius: 10px; padding: .65rem .9rem; color: #2c4a6e; }

/* Form */
.ep-form-label { font-weight: 600; font-size: .84rem; color: #2c4a6e; margin-bottom: .35rem; display: block; }

/* Total box */
.ep-total-box {
    background: linear-gradient(120deg,#edfff4,#e8f9f0);
    border: 1px solid #c3ecd4; border-radius: 12px; padding: 1rem 1.2rem;
    display: flex; align-items: center; justify-content: space-between;
}
.ep-total-box__label { font-size: .82rem; font-weight: 600; color: #4a7a5a; }
.ep-total-box__value { font-size: 1.5rem; font-weight: 700; color: #1a5c35; }
.ep-btn-pay { border-radius: 10px; font-weight: 600; font-size: .95rem; padding: .65rem; }
.ep-pay-btn-wrap {
    position: relative;
    display: inline-block;
    width: 100%;
}
.ep-pay-btn-wrap .btn:disabled {
    cursor: not-allowed;
    opacity: .56;
    filter: grayscale(.15);
    box-shadow: none;
}
.ep-pay-btn-wrap::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 50%;
    bottom: calc(100% + 8px);
    transform: translateX(-50%);
    background: rgba(15, 23, 42, .96);
    color: #fff;
    font-size: 11px;
    line-height: 1.35;
    padding: 7px 10px;
    border-radius: 8px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity .15s ease, transform .15s ease;
    z-index: 20;
}
.ep-pay-btn-wrap::before {
    content: '';
    position: absolute;
    left: 50%;
    bottom: calc(100% + 2px);
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: rgba(15, 23, 42, .96);
    opacity: 0;
    transition: opacity .15s ease;
    z-index: 21;
}
.ep-pay-btn-wrap:hover::after,
.ep-pay-btn-wrap:hover::before {
    opacity: 1;
}
.ep-pay-btn-wrap:hover::after {
    transform: translateX(-50%) translateY(-2px);
}

/* Summary rows */
.ep-summary-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .38rem 0; font-size: .83rem; color: #495057;
    border-bottom: 1px solid #f0f3f8;
}
.ep-summary-row:last-of-type { border-bottom: none; }
.ep-summary-row--group    { color: #4b6b8a; font-weight: 600; font-size: .8rem; background: #f4f6f9; border-radius: 6px; padding: .3rem .6rem; margin-bottom: .2rem; }
.ep-summary-row--taxa     { color: #92400e; background: #fef3c7; border-radius: 5px; padding: .3rem .6rem; margin: 2px 0; border-bottom: none; }
.ep-summary-row--discount { color: #16a34a; background: #f0fdf4; border-radius: 5px; padding: .3rem .6rem; margin: 2px 0; border-bottom: none; }
.ep-cupom-badge {
    display: inline-block; background: #16a34a; color: #fff;
    font-size: .65rem; font-weight: 700; border-radius: 4px; padding: 1px 5px; margin-left: 4px;
}
.ep-divider { border-top: 1px solid #e9ecef; margin: .4rem 0; }
.ep-total-final {
    display: flex; justify-content: flex-end; align-items: center; gap: .6rem;
    border: 2px solid #4b80ff; border-radius: 10px;
    padding: .7rem .9rem; margin-top: .6rem;
    background: #f4f8ff; font-size: 1.05rem;
}
.ep-total-final__crossed { text-decoration: line-through; color: #9ca3af; font-size: .82rem; font-weight: 400; }
.ep-total-final__label   { color: #21364f; font-weight: 600; margin-left: auto; }
.ep-total-final__val     { font-weight: 700; color: #0f2740; font-size: 1.1rem; }
.ep-note { border: 1px solid #e0e7ef; border-radius: 8px; background: #f8faff; padding: .6rem .85rem; display: flex; align-items: flex-start; gap: .4rem; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<?php if($gateway === 'stripe' && !empty($stripePublicKey)): ?>
<script src="https://js.stripe.com/v3/"></script>
<?php endif; ?>
<script>
(function () {
    const gateway    = <?php echo json_encode($gateway, 15, 512) ?>;
    const stripeKey  = <?php echo json_encode($stripePublicKey ?? null, 15, 512) ?>;
    const form       = document.getElementById('payment-form');
    const submitBtn  = document.getElementById('btn-submit-pagamento');
    const paySelect  = document.getElementById('payment-method');
    const openModal  = document.getElementById('openMetodoModal');
    const saveBtn    = document.getElementById('btnSalvarMetodo');
    const cardErrors = document.getElementById('card-errors');
    const metodoModal = document.getElementById('modalMetodoPagamento');
    const csrf       = <?php echo json_encode(csrf_token(), 15, 512) ?>;
    const temMetodosIniciais = <?php echo json_encode($temMetodosPagamento, 15, 512) ?>;

    function liberarFocoMetodoModal() {
        if (metodoModal && document.activeElement && metodoModal.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    }

    function atualizarBotaoPagamento() {
        if (!submitBtn) return;
        const temMetodoSelecionavel = !!(paySelect && paySelect.options && paySelect.options.length > 0 && !(paySelect.options.length === 1 && !paySelect.value));
        const habilitar = temMetodosIniciais || temMetodoSelecionavel;
        submitBtn.disabled = !habilitar;
        submitBtn.setAttribute('aria-disabled', habilitar ? 'false' : 'true');
        const wrapper = submitBtn.closest('.ep-pay-btn-wrap');
        if (wrapper) {
            wrapper.dataset.tooltip = habilitar
                ? ''
                : 'Adicione um método de pagamento salvo para habilitar o pagamento.';
            wrapper.title = habilitar ? '' : 'Adicione um método de pagamento salvo para habilitar o pagamento.';
        }
    }

    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status"></span> Processando...';
        });
    }

    if (paySelect) {
        paySelect.addEventListener('change', atualizarBotaoPagamento);
    }
    atualizarBotaoPagamento();

    if (gateway !== 'stripe') return;

    if (openModal) {
        openModal.addEventListener('click', function () { $('#modalMetodoPagamento').modal('show'); });
    }

    if (metodoModal) {
        $('#modalMetodoPagamento').on('hide.bs.modal', liberarFocoMetodoModal);
        $('#modalMetodoPagamento').on('hidden.bs.modal', function () {
            if (openModal && document.body.contains(openModal)) {
                openModal.focus({ preventScroll: true });
            }
        });
    }

    if (!stripeKey) return;

    const stripe   = Stripe(stripeKey);
    const elements = stripe.elements();
    const cardElem = elements.create('card');
    cardElem.mount('#card-element');

    if (saveBtn) {
        saveBtn.addEventListener('click', async function () {
            const orig = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Salvando...';
            if (cardErrors) cardErrors.textContent = '';

            const result = await stripe.createToken(cardElem);
            if (result.error) {
                if (cardErrors) cardErrors.textContent = result.error.message;
                saveBtn.disabled = false; saveBtn.innerHTML = orig;
                return;
            }

            try {
                const resp = await fetch(<?php echo json_encode($adicionarMetodoPagamentoUrl, 15, 512) ?>, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ stripeToken: result.token.id })
                });
                const data = await resp.json();
                if (!resp.ok) throw new Error(data.message || 'Erro ao adicionar método.');

                if (paySelect && Array.isArray(data.metodos)) {
                    paySelect.innerHTML = '';
                    data.metodos.forEach(function (m) {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = (m.brand || '').toUpperCase() + ' ****' + m.last4 + ' (' + m.exp_month + '/' + m.exp_year + ')';
                        paySelect.appendChild(opt);
                    });
                    if (paySelect.options.length) paySelect.value = paySelect.options[paySelect.options.length - 1].value;
                    atualizarBotaoPagamento();
                }
                liberarFocoMetodoModal();
                $('#modalMetodoPagamento').modal('hide');
            } catch (err) {
                if (cardErrors) cardErrors.textContent = err.message;
            } finally {
                $('#modalMetodoPagamento').one('hidden.bs.modal', function () {
                    saveBtn.disabled = false; saveBtn.innerHTML = orig;
                });
                if (!$('#modalMetodoPagamento').hasClass('show')) {
                    saveBtn.disabled = false; saveBtn.innerHTML = orig;
                }
            }
        });

        $('#modalMetodoPagamento').on('hidden.bs.modal', function () {
            saveBtn.disabled = false;
            if (saveBtn.innerHTML.includes('spinner-border')) {
                saveBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Salvar Cartão';
            }
        });
    } else if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.title = 'Configure a chave pública do Stripe para habilitar este recurso.';
    }
})();
</script>

<script>
(function () {
    const form = document.getElementById('manual-payment-form');
    if (!form) return;

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const original = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Registrando...';
        }

        try {
            const resp = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value
                },
                body: new FormData(form)
            });
            const data = await resp.json();
            if (!resp.ok || data.error) {
                throw new Error(data.error || 'Nao foi possivel registrar o pagamento.');
            }
            window.location.reload();
        } catch (error) {
            alert(error.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = original;
            }
        }
    });
})();
</script>

<script>
(function() {
    const inputValor = document.getElementById('valorPagamento');
    const selectParcelas = document.getElementById('installments');
    const simbolo = '<?php echo e($simbolo); ?>';
    const limiteSemJuros = 3;

    function atualizarParcelas() {
        const valor = parseFloat(inputValor.value) || 0;
        selectParcelas.innerHTML = '';

        if (valor <= 0) {
            selectParcelas.innerHTML = '<option value="1">À vista — ' + simbolo + ' 0,00</option>';
            return;
        }

        const optAVista = document.createElement('option');
        optAVista.value = '1';
        optAVista.text = 'À vista — ' + simbolo + ' ' + valor.toFixed(2).replace('.', ',');
        selectParcelas.appendChild(optAVista);

        [2,3,4,5,6,7,8,9,10,11,12].forEach(function(p) {
            const vp = valor / p;
            const opt = document.createElement('option');
            opt.value = p;
            opt.text = p + 'x de ' + simbolo + ' ' + vp.toFixed(2).replace('.', ',') + (p > limiteSemJuros ? ' — Com juros' : ' — Sem juros');
            selectParcelas.appendChild(opt);
        });
    }

    inputValor.addEventListener('input', atualizarParcelas);
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/efetuar-pagamento/efetuar-pagamento.blade.php ENDPATH**/ ?>