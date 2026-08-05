<?php $__env->startSection('title', $titulo); ?>

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
    $codigoReserva = $sinistro->reserva?->codigoReserva?->codigo
        ?? $sinistro->reserva?->codigo_reserva
        ?? ($sinistro->reserva_id ? 'RES-' . str_pad((string) $sinistro->reserva_id, 6, '0', STR_PAD_LEFT) : null);
?>
<div class="container-fluid px-0">

    
    <div class="module-card mb-4">
        <div class="module-card-header">
            <div class="module-header-left">
                <?php
                    $iconeTipo = match($sinistro->tipo_sinistro) {
                        'furto_roubo' => 'fa-user-secret',
                        'incendio'    => 'fa-fire',
                        'vandalismo'  => 'fa-hammer',
                        'alagamento'  => 'fa-water',
                        default       => 'fa-car-crash',
                    };
                    $badgeStatus = match($sinistro->status) {
                        'encerrado','indenizado' => 'success',
                        'aprovado'               => 'primary',
                        'cancelado'              => 'secondary',
                        'aguardando_documentos'  => 'warning',
                        default                  => 'danger',
                    };
                ?>
                <div class="module-header-icon" style="background:rgba(220,53,69,.12);color:#dc3545">
                    <i class="fas <?php echo e($iconeTipo); ?>"></i>
                </div>
                <div>
                    <div class="module-header-eyebrow">Sinistros &bull; Detalhes</div>
                    <h1 class="module-header-title">Sinistro #<?php echo e($sinistro->id); ?></h1>
                    <p class="module-header-sub">
                        <span class="badge badge-<?php echo e($badgeStatus); ?> mr-1"><?php echo e($sinistro->label_status); ?></span>
                        <span class="badge badge-light text-dark mr-1"><?php echo e($sinistro->label_tipo); ?></span>
                        <?php if($sinistro->placa_momento): ?>
                            <span class="badge badge-secondary mr-1"><?php echo e($sinistro->placa_momento); ?></span>
                        <?php endif; ?>
                        <?php if($sinistro->data_hora_ocorrido): ?>
                            <span style="opacity:.7;font-size:.82rem;"><?php echo e($sinistro->data_hora_ocorrido->format('d/m/Y H:i')); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="module-header-right">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sinistro.edit')): ?>
                <a href="<?php echo e(route('sinistro.edit', $sinistro->id)); ?>" class="btn-xp btn-xp--sm mr-2">
                    <i class="fas fa-pen mr-1"></i>Editar
                </a>
                <?php endif; ?>
                <a href="<?php echo e(route('sinistro.index')); ?>" class="btn-xp btn-xp--outline-light btn-xp--sm">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">

        
        <div class="col-lg-8">

            
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Identificação do Ocorrido</strong>
                            <span>Tipo, data, local e detalhes do evento</span>
                        </div>
                    </div>
                    <div class="veiculo-grid">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Tipo de Sinistro</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->label_tipo); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Data / Hora do Ocorrido</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->data_hora_ocorrido?->format('d/m/Y H:i') ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Placa no Momento</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->placa_momento ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Número do BO</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->numero_bo ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Órgão (Delegacia / PRF)</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->orgao_bo ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Terceiros Envolvidos</span>
                            <span class="veiculo-field__value">
                                <?php if($sinistro->terceiros_envolvidos): ?>
                                    <span class="badge badge-warning">Sim</span>
                                    <?php if($sinistro->terceiro_nome): ?> — <?php echo e($sinistro->terceiro_nome); ?> <?php endif; ?>
                                    <?php if($sinistro->terceiro_telefone): ?> — <?php echo e($sinistro->terceiro_telefone); ?> <?php endif; ?>
                                    <?php if($sinistro->terceiro_placa): ?> — Placa: <?php echo e($sinistro->terceiro_placa); ?> <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Não</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Vítimas</span>
                            <span class="veiculo-field__value">
                                <span class="badge badge-<?php echo e($sinistro->vitimas ? 'danger' : 'secondary'); ?>">
                                    <?php echo e($sinistro->vitimas ? 'Sim' : 'Não'); ?>

                                </span>
                            </span>
                        </div>
                        <div class="veiculo-field" style="grid-column:1/-1">
                            <span class="veiculo-field__label">Local do Ocorrido</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->local_ocorrido ?? '—'); ?></span>
                        </div>
                        <?php if($sinistro->descricao): ?>
                        <div class="veiculo-field" style="grid-column:1/-1">
                            <span class="veiculo-field__label">Descrição</span>
                            <span class="veiculo-field__value" style="white-space:pre-wrap;"><?php echo e($sinistro->descricao); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if($sinistro->observacoes): ?>
                        <div class="veiculo-field" style="grid-column:1/-1">
                            <span class="veiculo-field__label">Observações</span>
                            <span class="veiculo-field__value" style="white-space:pre-wrap;"><?php echo e($sinistro->observacoes); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-link"></i>
                        <div>
                            <strong>Vínculos</strong>
                            <span>Reserva, veículo, cliente e condutor</span>
                        </div>
                    </div>
                    <div class="veiculo-grid">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Veículo</span>
                            <span class="veiculo-field__value">
                                <?php if($sinistro->veiculo): ?>
                                    <a href="<?php echo e(route('veiculo.show', $sinistro->veiculo_id)); ?>" class="xp-link">
                                        <?php echo e($sinistro->veiculo->nome); ?>

                                        <?php if($sinistro->veiculo->marca || $sinistro->veiculo->modelo): ?>
                                            — <?php echo e($sinistro->veiculo->marca?->nome); ?> <?php echo e($sinistro->veiculo->modelo?->nome); ?>

                                        <?php endif; ?>
                                    </a>
                                <?php else: ?> —
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Reserva</span>
                            <span class="veiculo-field__value">
                                <?php if($codigoReserva): ?>
                                    <a href="<?php echo e(route('reserva.show', $codigoReserva)); ?>" class="xp-link">#<?php echo e($codigoReserva); ?></a>
                                    <?php if($sinistro->reserva?->usuario): ?>
                                        — <?php echo e($sinistro->reserva->usuario->nome); ?>

                                    <?php endif; ?>
                                <?php else: ?> —
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Cliente</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->cliente?->nome ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Condutor</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->condutor?->nome ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Responsável</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->responsavel?->nome ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Registrado em</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->created_at?->format('d/m/Y H:i') ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field" style="grid-column:1/-1">
                            <span class="veiculo-field__label">Ordem de Serviço (OS)</span>
                            <span class="veiculo-field__value">
                                <?php if($sinistro->ordensServico->count() > 0): ?>
                                    <?php $__currentLoopData = $sinistro->ordensServico; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $os): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a href="<?php echo e(route('ordem-servico.show', $os)); ?>" class="badge badge-info p-2 mr-2" style="font-size: .82rem; font-weight: 600; border-radius: 8px;">
                                            <i class="fas fa-file-alt mr-1"></i> OS <?php echo e($os->numero ?? '#'.$os->id); ?> (<?php echo e(ucfirst($os->status)); ?>)
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <span class="text-muted">Nenhuma OS vinculada</span>
                                    <a href="<?php echo e(route('ordem-servico.create', ['veiculo_id' => $sinistro->veiculo_id, 'sinistro_id' => $sinistro->id])); ?>" class="btn btn-xs btn-outline-primary ml-2" style="border-radius: 6px;">
                                        <i class="fas fa-plus mr-1"></i>Criar OS
                                    </a>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>Seguro</strong>
                            <span>Dados da seguradora e valores da cobertura</span>
                        </div>
                    </div>
                    <div class="veiculo-grid">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Seguradora</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->seguradora ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Número da Apólice</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->numero_apolice ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Nº Sinistro na Seguradora</span>
                            <span class="veiculo-field__value"><?php echo e($sinistro->numero_sinistro_seguradora ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Franquia</span>
                            <span class="veiculo-field__value">
                                <?php echo e($sinistro->franquia !== null ? 'R$ ' . number_format((float)$sinistro->franquia, 2, ',', '.') : '—'); ?>

                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Prejuízo Estimado</span>
                            <span class="veiculo-field__value text-danger font-weight-bold">
                                <?php echo e($sinistro->valor_prejuizo_estimado !== null ? 'R$ ' . number_format((float)$sinistro->valor_prejuizo_estimado, 2, ',', '.') : '—'); ?>

                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Aprovado pela Seguradora</span>
                            <span class="veiculo-field__value text-success font-weight-bold">
                                <?php echo e($sinistro->valor_aprovado_seguradora !== null ? 'R$ ' . number_format((float)$sinistro->valor_aprovado_seguradora, 2, ',', '.') : '—'); ?>

                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Resp. do Cliente</span>
                            <span class="veiculo-field__value text-warning font-weight-bold">
                                <?php echo e($sinistro->valor_responsabilidade_cliente !== null ? 'R$ ' . number_format((float)$sinistro->valor_responsabilidade_cliente, 2, ',', '.') : '—'); ?>

                            </span>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-dollar-sign"></i>
                        <div>
                            <strong>Controle de Custos e Pagamentos</strong>
                            <span>Quem paga o custo residual e como foi/será pago</span>
                        </div>
                    </div>

                    <div class="alert alert-light border small py-2 px-3 mb-3">
                        <i class="fas fa-info-circle text-info mr-1"></i>
                        <strong>Como funciona:</strong>
                        O <strong>Custo Residual</strong> = Prejuízo Estimado − Aprovado pela Seguradora.
                        Esse valor pode ser absorvido pela <strong>Empresa</strong> (débito interno) ou cobrado do <strong>Cliente</strong>
                        (lançado como cobrança adicional na reserva). Quando ambos dividem, cada parte é registrada separadamente.
                    </div>

                    <div class="veiculo-grid mb-3">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Destino do Custo</span>
                            <span class="veiculo-field__value">
                                <?php $dc = $sinistro->destino_custo ?? 'empresa'; ?>
                                <?php if($dc === 'cliente'): ?>
                                    <span class="badge badge-warning"><i class="fas fa-user mr-1"></i>Cliente</span>
                                <?php elseif($dc === 'ambos'): ?>
                                    <span class="badge badge-info"><i class="fas fa-users mr-1"></i>Empresa + Cliente</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><i class="fas fa-building mr-1"></i>Empresa</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if($sinistro->valor_custo_empresa !== null): ?>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Custo para a Empresa</span>
                            <span class="veiculo-field__value font-weight-bold">
                                R$ <?php echo e(number_format((float)$sinistro->valor_custo_empresa, 2, ',', '.')); ?>

                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        
                        <?php if(in_array($dc, ['empresa','ambos'])): ?>
                        <div class="col-md-6">
                            <h6 class="sn-pay-title"><i class="fas fa-building mr-1"></i>Pagamento da Empresa</h6>
                            <?php if($sinistro->pago_empresa_em): ?>
                                <div class="alert alert-success py-2 px-3 small">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <strong>Pago em <?php echo e($sinistro->pago_empresa_em->format('d/m/Y H:i')); ?></strong><br>
                                    Forma: <?php echo e(\App\Models\Sinistro::FORMAS_PAGAMENTO[$sinistro->forma_pagamento_empresa] ?? $sinistro->forma_pagamento_empresa); ?><br>
                                    <?php if($sinistro->referencia_pagamento_empresa): ?> Ref: <?php echo e($sinistro->referencia_pagamento_empresa); ?><br> <?php endif; ?>
                                    <?php if($sinistro->pagoEmpresaPor): ?> Confirmado por: <?php echo e($sinistro->pagoEmpresaPor->nome ?? $sinistro->pagoEmpresaPor->name); ?> <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning py-2 px-3 small mb-2">
                                    <i class="fas fa-clock mr-1"></i>Pagamento pendente
                                </div>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sinistro.edit')): ?>
                                <button type="button" class="btn btn-sm btn-success w-100" id="btnPagarEmpresa">
                                    <i class="fas fa-check mr-1"></i>Registrar pagamento da empresa
                                </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        
                        <?php if(in_array($dc, ['cliente','ambos'])): ?>
                        <div class="col-md-6">
                            <h6 class="sn-pay-title"><i class="fas fa-user mr-1"></i>Pagamento do Cliente</h6>
                            <?php if($sinistro->pago_cliente_em): ?>
                                <div class="alert alert-success py-2 px-3 small">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <strong>Pago em <?php echo e($sinistro->pago_cliente_em->format('d/m/Y H:i')); ?></strong><br>
                                    Forma: <?php echo e(\App\Models\Sinistro::FORMAS_PAGAMENTO[$sinistro->forma_pagamento_cliente] ?? $sinistro->forma_pagamento_cliente); ?><br>
                                    <?php if($sinistro->referencia_pagamento_cliente): ?> Ref: <?php echo e($sinistro->referencia_pagamento_cliente); ?><br> <?php endif; ?>
                                    <?php if($sinistro->pagoClientePor): ?> Confirmado por: <?php echo e($sinistro->pagoClientePor->nome ?? $sinistro->pagoClientePor->name); ?> <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning py-2 px-3 small mb-2">
                                    <i class="fas fa-clock mr-1"></i>Pagamento pendente
                                    <?php if($codigoReserva): ?>
                                        <br><small>Cobrado na reserva <a href="<?php echo e(route('reserva.show', $codigoReserva)); ?>">#<?php echo e($codigoReserva); ?></a></small>
                                    <?php endif; ?>
                                </div>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sinistro.edit')): ?>
                                <button type="button" class="btn btn-sm btn-success w-100" id="btnPagarCliente">
                                    <i class="fas fa-check mr-1"></i>Registrar pagamento do cliente
                                </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <?php
                $anexos = array_filter([
                    'BO'     => $sinistro->anexo_bo_url,
                    'Foto 1' => $sinistro->anexo_foto1_url,
                    'Foto 2' => $sinistro->anexo_foto2_url,
                    'Foto 3' => $sinistro->anexo_foto3_url,
                    'Laudo'  => $sinistro->anexo_laudo_url,
                ]);
            ?>
            <?php if(count($anexos)): ?>
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-paperclip"></i>
                        <div><strong>Evidências e Documentos</strong></div>
                    </div>
                    <div class="d-flex flex-wrap" style="gap:.5rem">
                        <?php $__currentLoopData = $anexos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $ext  = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                                $icon = $ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-image text-primary';
                            ?>
                            <a href="<?php echo e($url); ?>" target="_blank" class="btn-xp btn-xp--sm" style="background:#f1f5f9;color:#334155;">
                                <i class="fas <?php echo e($icon); ?>"></i><?php echo e($label); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        
        <div class="col-lg-4">

            
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-chart-pie"></i>
                        <div>
                            <strong>Resumo Financeiro</strong>
                            <span>Valores consolidados do sinistro</span>
                        </div>
                    </div>
                    <div class="veiculo-summary">
                        <div class="veiculo-summary__row">
                            <span>Status</span>
                            <span class="badge badge-<?php echo e($badgeStatus); ?>"><?php echo e($sinistro->label_status); ?></span>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Prejuízo estimado</span>
                            <strong class="text-danger"><?php echo e($sinistro->valor_prejuizo_estimado !== null ? 'R$ '.number_format((float)$sinistro->valor_prejuizo_estimado,2,',','.') : '—'); ?></strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Cobertura seguradora</span>
                            <strong class="text-success"><?php echo e($sinistro->valor_aprovado_seguradora !== null ? 'R$ '.number_format((float)$sinistro->valor_aprovado_seguradora,2,',','.') : '—'); ?></strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Custo residual</span>
                            <?php
                                $residual = max(0, (float)($sinistro->valor_prejuizo_estimado ?? 0) - (float)($sinistro->valor_aprovado_seguradora ?? 0));
                            ?>
                            <strong class="<?php echo e($residual > 0 ? 'text-warning' : 'text-muted'); ?>">
                                R$ <?php echo e(number_format($residual, 2, ',', '.')); ?>

                            </strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Destino do custo</span>
                            <?php if($dc === 'cliente'): ?> <span class="badge badge-warning">Cliente</span>
                            <?php elseif($dc === 'ambos'): ?> <span class="badge badge-info">Ambos</span>
                            <?php else: ?> <span class="badge badge-secondary">Empresa</span>
                            <?php endif; ?>
                        </div>
                        <?php if(in_array($dc, ['empresa','ambos'])): ?>
                        <div class="veiculo-summary__row">
                            <span>Pago (empresa)</span>
                            <?php if($sinistro->pago_empresa_em): ?>
                                <span class="status-badge status-badge--green"><i class="fas fa-check mr-1"></i>Pago</span>
                            <?php else: ?>
                                <span class="status-badge status-badge--amber">Pendente</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if(in_array($dc, ['cliente','ambos'])): ?>
                        <div class="veiculo-summary__row">
                            <span>Pago (cliente)</span>
                            <?php if($sinistro->pago_cliente_em): ?>
                                <span class="status-badge status-badge--green"><i class="fas fa-check mr-1"></i>Pago</span>
                            <?php else: ?>
                                <span class="status-badge status-badge--amber">Pendente</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-bolt"></i>
                        <div><strong>Ações</strong></div>
                    </div>
                    <div class="veiculo-acoes-grid">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sinistro.edit')): ?>
                        <a href="<?php echo e(route('sinistro.edit', $sinistro->id)); ?>" class="veiculo-acao veiculo-acao--primary">
                            <i class="fas fa-pen"></i><span>Editar sinistro</span>
                        </a>
                        <?php endif; ?>
                        <?php if($codigoReserva): ?>
                        <a href="<?php echo e(route('reserva.show', $codigoReserva)); ?>" class="veiculo-acao">
                            <i class="fas fa-calendar-alt"></i><span>Ver reserva</span>
                        </a>
                        <?php endif; ?>
                        <?php if($sinistro->veiculo_id): ?>
                        <a href="<?php echo e(route('veiculo.show', $sinistro->veiculo_id)); ?>" class="veiculo-acao">
                            <i class="fas fa-car"></i><span>Ver veículo</span>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('sinistro.index')); ?>" class="veiculo-acao">
                            <i class="fas fa-list"></i><span>Lista de sinistros</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-history"></i>
                        <div>
                            <strong>Histórico de Alterações</strong>
                            <span>Todas as modificações registradas</span>
                        </div>
                    </div>
                    <?php if($sinistro->historico->isEmpty()): ?>
                        <p class="text-muted small mb-0">Nenhuma alteração registrada.</p>
                    <?php else: ?>
                    <div class="timeline timeline-inverse" style="max-height:320px;overflow-y:auto;">
                        <?php $__currentLoopData = $sinistro->historico; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="time-label">
                            <span class="bg-secondary text-white" style="font-size:11px;padding:2px 8px;border-radius:3px;">
                                <?php echo e($hist->created_at->format('d/m/Y H:i')); ?>

                            </span>
                        </div>
                        <div>
                            <i class="fas fa-circle bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-user mr-1"></i><?php echo e($hist->usuario?->nome ?? 'Sistema'); ?></span>
                                <p class="timeline-body small">
                                    <?php echo e($hist->descricao); ?>

                                    <?php if($hist->campo_alterado): ?>
                                        <br><strong><?php echo e($hist->campo_alterado); ?>:</strong>
                                        <span class="text-danger"><?php echo e($hist->valor_anterior ?? 'vazio'); ?></span>
                                        &rarr;
                                        <span class="text-success"><?php echo e($hist->valor_novo); ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>


<div class="modal fade" id="modalPagarEmpresa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-building text-secondary mr-2"></i>Pagamento — Empresa</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small py-2 px-3 mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Este é o custo que a empresa absorve internamente (ex.: franquia, reparos, despesas não cobertas pelo seguro).
                    Registre como o valor foi quitado. Para pagamentos em <strong>dinheiro</strong> ou <strong>transferência</strong> feitos internamente,
                    selecione a forma correta — o sistema registra quem confirmou e quando.
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Forma de Pagamento <span class="text-danger">*</span></label>
                    <select id="pe_forma" class="form-control">
                        <option value="">Selecione...</option>
                        <?php $__currentLoopData = \App\Models\Sinistro::FORMAS_PAGAMENTO; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php if(!empty($metodosPagamento)): ?>
                <div class="form-group d-none" id="pc_cartao_group">
                    <label class="font-weight-bold">Cartão do Cliente (Reserva)</label>
                    <select id="pc_payment_method" class="form-control">
                        <option value="">Selecione...</option>
                        <?php $__currentLoopData = $metodosPagamento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($card = $pm['card'] ?? null); ?>
                            <option value="<?php echo e($pm['id']); ?>">
                                <?php echo e(strtoupper($card->brand ?? 'CARD')); ?> **** <?php echo e($card->last4 ?? ''); ?>

                                (<?php echo e($card->exp_month ?? ''); ?>/<?php echo e($card->exp_year ?? ''); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <small class="text-muted">Se a forma for cartão de crédito, o sistema cobra pelo Stripe usando o cartão do cliente.</small>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="font-weight-bold">Referência / Comprovante</label>
                    <input type="text" id="pe_referencia" class="form-control" placeholder="Nº da transferência, comprovante, etc.">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btnSalvarPagarEmpresa">
                    <i class="fas fa-check mr-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalPagarCliente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user text-warning mr-2"></i>Pagamento — Cliente</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small py-2 px-3 mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Este é o valor cobrado do <strong>cliente da reserva</strong> como responsabilidade pelo sinistro.
                    Confirme apenas quando o cliente efetuar o pagamento — pode ser em dinheiro presencialmente,
                    cartão ou PIX. O sistema registra a forma, o responsável pela confirmação e o horário.
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Forma de Pagamento <span class="text-danger">*</span></label>
                    <select id="pc_forma" class="form-control">
                        <option value="">Selecione...</option>
                        <?php $__currentLoopData = \App\Models\Sinistro::FORMAS_PAGAMENTO; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($key !== 'transferencia'): ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Referência / Comprovante</label>
                    <input type="text" id="pc_referencia" class="form-control" placeholder="NSU do cartão, código PIX, etc.">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btnSalvarPagarCliente">
                    <i class="fas fa-check mr-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
.module-card { background:linear-gradient(135deg,#1e293b 0%,#334155 100%);border-radius:16px;overflow:hidden; }
.module-card-header { display:flex;align-items:center;justify-content:space-between;padding:1.5rem;gap:1rem;flex-wrap:wrap; }
.module-header-left { display:flex;align-items:center;gap:1rem; }
.module-header-icon { width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0; }
.module-header-eyebrow { font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:.2rem; }
.module-header-title { font-size:1.5rem;font-weight:800;color:#fff;margin:0 0 .2rem; }
.module-header-sub { font-size:.84rem;color:rgba(255,255,255,.7);margin:0; }
.module-header-right { display:flex;gap:.5rem;flex-wrap:wrap; }
.btn-xp { display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .9rem;border-radius:999px;font-size:.8rem;font-weight:700;cursor:pointer;text-decoration:none;border:none;background:rgba(255,255,255,.15);color:#fff;transition:background .15s; }
.btn-xp:hover { background:rgba(255,255,255,.25);color:#fff;text-decoration:none; }
.btn-xp--sm { padding:.25rem .7rem;font-size:.76rem; }
.btn-xp--outline-light { background:transparent;border:1px solid rgba(255,255,255,.4); }
.config-card { background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden; }
.config-block { padding:1.25rem; }
.config-block + .config-block { border-top:1px solid #f0f4f8; }
.config-block-label { display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1rem; }
.config-block-label > i { font-size:1rem;color:#64748b;margin-top:.15rem;flex-shrink:0; }
.config-block-label strong { display:block;font-size:.85rem;color:#1e293b;font-weight:700; }
.config-block-label span { display:block;font-size:.75rem;color:#94a3b8; }
.veiculo-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem 1.5rem; }
.veiculo-field { display:flex;flex-direction:column;gap:.15rem; }
.veiculo-field__label { font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8; }
.veiculo-field__value { font-size:.88rem;color:#1e293b;font-weight:500; }
.veiculo-summary { display:flex;flex-direction:column;gap:.5rem; }
.veiculo-summary__row { display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #f1f5f9;font-size:.84rem; }
.veiculo-summary__row:last-child { border-bottom:none; }
.veiculo-acoes-grid { display:grid;grid-template-columns:1fr 1fr;gap:.5rem; }
.veiculo-acao { display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.35rem;padding:.75rem .5rem;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;text-align:center;font-size:.76rem;font-weight:700;color:#334155;cursor:pointer;text-decoration:none;transition:background .15s,border-color .15s; }
.veiculo-acao:hover { background:#e0f2fe;border-color:#38bdf8;color:#0369a1;text-decoration:none; }
.veiculo-acao i { font-size:1.1rem;color:#64748b; }
.veiculo-acao:hover i { color:#0369a1; }
.veiculo-acao--primary { background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);border-color:#2563eb;color:#fff; }
.veiculo-acao--primary i { color:#fff; }
.veiculo-acao--primary:hover { background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);color:#fff; }
.status-badge { display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .6rem;border-radius:999px;font-size:.75rem;font-weight:700; }
.status-badge--green { background:#dcfce7;color:#166534; }
.status-badge--amber { background:#fef3c7;color:#92400e; }
.xp-link { color:#2563eb;font-weight:600; }
.xp-link:hover { color:#1d4ed8; }
.sn-pay-title { font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.5rem; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'sinistro-show';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.module-card', popover: { title: 'Cabeçalho do sinistro', description: 'Exibe o número do sinistro, ícone do tipo de evento (colisão, furto, incêndio…), status atual, tipo, placa e data/hora do ocorrido.', side: 'bottom' } },
        { element: '.module-header-sub .badge:first-child', popover: { title: 'Status atual', description: 'Indica a situação do sinistro: <b>Aberto</b>, <b>Aguardando Documentos</b>, <b>Aprovado</b>, <b>Encerrado</b>, <b>Indenizado</b> ou <b>Cancelado</b>. Atualizado conforme o processo avança.', side: 'bottom' } },
        { element: '.col-lg-8 .config-card:nth-child(1)', popover: { title: 'Identificação do Ocorrido', description: 'Dados completos do evento: tipo, data, local, placa, número do BO, órgão emissor, terceiros envolvidos, vítimas e descrição detalhada.', side: 'right' } },
        { element: '.col-lg-8 .config-card:nth-child(2)', popover: { title: 'Vínculos', description: 'Mostra o veículo, a reserva associada, o cliente, o condutor e o responsável pelo registro. Clique nos links para navegar direto para o veículo ou reserva.', side: 'right' } },
        { element: '.col-lg-8 .config-card:nth-child(3)', popover: { title: 'Seguro', description: 'Seguradora, número de apólice, franquia, prejuízo estimado, valor aprovado pela seguradora e responsabilidade do cliente. O custo residual é calculado automaticamente.', side: 'right' } },
        { element: '.col-lg-8 .config-card:nth-child(4)', popover: { title: 'Controle de Custos e Pagamentos', description: 'Define quem arca com o custo residual (empresa, cliente ou ambos) e registra os pagamentos. Você pode confirmar o pagamento da empresa ou lançar cobrança ao cliente via Stripe.', side: 'right' } },
        { element: '.col-lg-4', popover: { title: 'Painel lateral', description: 'Apresenta evidências anexadas (BO, fotos, laudo), o resumo financeiro consolidado, as ações disponíveis (editar, exportar) e o histórico de alterações do sinistro.', side: 'left' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }).catch(() => {});
    }

    function getAvailableSteps() {
        return steps.filter(function (step) {
            if (!step || !step.element) return false;

            return String(step.element)
                .split(',')
                .map(function (selector) { return selector.trim(); })
                .filter(Boolean)
                .some(function (selector) {
                    try {
                        return !!document.querySelector(selector);
                    } catch (error) {
                        return false;
                    }
                });
        });
    }

    function buildTour() {
        if (typeof window.driver === 'undefined' || !window.driver.js) return null;

        var availableSteps = getAvailableSteps();
        if (!availableSteps.length) return null;

        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            progressText: 'Passo {{current}} de {{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn'; btnTour.type = 'button'; btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn'; btnStep.type = 'button'; btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
            },
            steps: availableSteps,
        });
    }

    function startTour() {
        var tour = buildTour();
        if (!tour) return;
        setTimeout(function () { tour.drive(); }, 350);
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .catch(() => {})
            .then(() => startTour());
    };

    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            startTour();
        })
        .catch(() => {
            if (!localStorage.getItem(CACHE_KEY) || forceTour) startTour();
        });
})();
</script>
<script>
(function () {
    function postPagamento(url, forma, referencia, paymentMethod, btnEl, successCb) {
        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Aguarde...';
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ forma_pagamento: forma, referencia_pagamento: referencia, payment_method: paymentMethod }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                btnEl.disabled = false;
                btnEl.innerHTML = '<i class="fas fa-check mr-1"></i>Confirmar';
                return;
            }
            successCb();
        })
        .catch(() => {
            alert('Erro ao registrar pagamento. Tente novamente.');
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="fas fa-check mr-1"></i>Confirmar';
        });
    }

    document.getElementById('btnPagarEmpresa')?.addEventListener('click', () => $('#modalPagarEmpresa').modal('show'));
    document.getElementById('btnPagarCliente')?.addEventListener('click', () => $('#modalPagarCliente').modal('show'));

    document.getElementById('btnSalvarPagarEmpresa')?.addEventListener('click', function () {
        const forma = document.getElementById('pe_forma').value;
        if (!forma) { alert('Selecione a forma de pagamento.'); return; }
        postPagamento(
            '<?php echo e(route("sinistro.pagarEmpresa", $sinistro->id)); ?>',
            forma,
            document.getElementById('pe_referencia').value,
            null,
            this,
            () => { $('#modalPagarEmpresa').modal('hide'); location.reload(); }
        );
    });

    document.getElementById('btnSalvarPagarCliente')?.addEventListener('click', function () {
        const forma = document.getElementById('pc_forma').value;
        if (!forma) { alert('Selecione a forma de pagamento.'); return; }
        const pmSel = document.getElementById('pc_payment_method');
        const paymentMethod = pmSel ? pmSel.value : null;
        if (forma === 'cartao_credito' && pmSel && !paymentMethod) {
            alert('Selecione um cartao do cliente para cobrar.');
            return;
        }
        postPagamento(
            '<?php echo e(route("sinistro.pagarCliente", $sinistro->id)); ?>',
            forma,
            document.getElementById('pc_referencia').value,
            paymentMethod,
            this,
            () => { $('#modalPagarCliente').modal('hide'); location.reload(); }
        );
    });

    document.getElementById('pc_forma')?.addEventListener('change', function () {
        const group = document.getElementById('pc_cartao_group');
        if (!group) return;
        if (this.value === 'cartao_credito') group.classList.remove('d-none');
        else group.classList.add('d-none');
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/sinistro/show.blade.php ENDPATH**/ ?>