

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
    $codigoReserva = $multa->reserva?->codigoReserva?->codigo
        ?? $multa->reserva?->codigo_reserva
        ?? $multa->reserva?->codigoReserva?->codigo
        ?? ($multa->reserva_id ? 'RES-' . str_pad((string) $multa->reserva_id, 6, '0', STR_PAD_LEFT) : null);
?>
<div class="container-fluid px-0">

    
    <div class="module-card mb-4">
        <div class="module-card-header">
            <div class="module-header-left">
                <div class="module-header-icon" style="background:rgba(220,53,69,.12);color:#dc3545">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="module-header-eyebrow">Multas &bull; Detalhes</div>
                    <h1 class="module-header-title">Multa #<?php echo e($multa->id); ?></h1>
                    <p class="module-header-sub">
                        <?php
                            $badgeMulta = match($multa->status_multa) {
                                'paga','deferida' => 'success',
                                'cancelada'       => 'secondary',
                                'recorrida'       => 'info',
                                'indeferida'      => 'danger',
                                default           => 'warning',
                            };
                            $badgeDesc = match($multa->status_desconto) {
                                'descontado'  => 'success',
                                'nao_descontar','estornado' => 'secondary',
                                'pendente'    => 'info',
                                default       => 'light',
                            };
                        ?>
                        <span class="badge badge-<?php echo e($badgeMulta); ?> mr-1"><?php echo e($multa->label_status_multa); ?></span>
                        <?php if($multa->placa_momento): ?>
                            <span class="badge badge-secondary mr-1"><?php echo e($multa->placa_momento); ?></span>
                        <?php endif; ?>
                        <?php if($multa->data_hora_infracao): ?>
                            <span class="text-white-60" style="opacity:.7;font-size:.82rem;"><?php echo e($multa->data_hora_infracao->format('d/m/Y H:i')); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="module-header-right">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('multa.edit')): ?>
                <a href="<?php echo e(route('multa.edit', $multa->id)); ?>" class="btn-xp btn-xp--sm mr-2">
                    <i class="fas fa-pen mr-1"></i>Editar
                </a>
                <?php endif; ?>
                <a href="<?php echo e(route('multa.index')); ?>" class="btn-xp btn-xp--outline-light btn-xp--sm">
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
                        <i class="fas fa-file-alt"></i>
                        <div>
                            <strong>Identificação da Infração</strong>
                            <span>Dados da autuação e do veículo no momento</span>
                        </div>
                    </div>
                    <div class="veiculo-grid">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Número do Auto</span>
                            <span class="veiculo-field__value"><?php echo e($multa->numero_auto ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Órgão Autuador</span>
                            <span class="veiculo-field__value"><?php echo e($multa->orgao_autuador ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Data / Hora da Infração</span>
                            <span class="veiculo-field__value"><?php echo e($multa->data_hora_infracao?->format('d/m/Y H:i') ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Data de Vencimento</span>
                            <span class="veiculo-field__value <?php echo e($multa->data_vencimento && $multa->data_vencimento->isPast() && $multa->status_multa !== 'paga' ? 'text-danger font-weight-bold' : ''); ?>">
                                <?php echo e($multa->data_vencimento?->format('d/m/Y') ?? '—'); ?>

                                <?php if($multa->data_vencimento?->isPast() && $multa->status_multa !== 'paga'): ?>
                                    <span class="badge badge-danger ml-1">Vencida</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Pontos na CNH</span>
                            <span class="veiculo-field__value"><?php echo e($multa->pontos !== null ? $multa->pontos . ' pts' : '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Placa no Momento</span>
                            <span class="veiculo-field__value"><?php echo e($multa->placa_momento ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field" style="grid-column: 1/-1">
                            <span class="veiculo-field__label">Local da Infração</span>
                            <span class="veiculo-field__value"><?php echo e($multa->local_infracao ?? '—'); ?></span>
                        </div>
                        <?php if($multa->descricao): ?>
                        <div class="veiculo-field" style="grid-column: 1/-1">
                            <span class="veiculo-field__label">Descrição</span>
                            <span class="veiculo-field__value" style="white-space:pre-wrap;"><?php echo e($multa->descricao); ?></span>
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
                                <?php if($multa->veiculo): ?>
                                    <a href="<?php echo e(route('veiculo.show', $multa->veiculo_id)); ?>" class="xp-link">
                                        <?php echo e($multa->veiculo->nome); ?>

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
                                <?php else: ?> —
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Cliente</span>
                            <span class="veiculo-field__value"><?php echo e($multa->cliente?->nome ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Condutor</span>
                            <span class="veiculo-field__value"><?php echo e($multa->condutor?->nome ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Lançado por</span>
                            <span class="veiculo-field__value"><?php echo e($multa->responsavelLancamento?->nome ?? '—'); ?></span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Registrado em</span>
                            <span class="veiculo-field__value"><?php echo e($multa->created_at?->format('d/m/Y H:i') ?? '—'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-dollar-sign"></i>
                        <div>
                            <strong>Financeiro</strong>
                            <span>Valores, destino de cobrança e pagamento</span>
                        </div>
                    </div>

                    
                    <div class="alert alert-light border small mb-3 py-2 px-3">
                        <i class="fas fa-info-circle text-info mr-1"></i>
                        <strong>Como funciona:</strong> O <strong>Valor Original</strong> é o valor da multa emitida pelo órgão.
                        O <strong>Desconto</strong> é aplicado caso o pagamento antecipado gere abatimento.
                        O <strong>Valor Final</strong> é o que efetivamente será pago.
                        O <strong>Destino de Cobrança</strong> define quem paga: a empresa (débito interno) ou o cliente (cobrado na reserva).
                    </div>

                    <div class="veiculo-grid mb-3">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Valor Original</span>
                            <span class="veiculo-field__value text-danger font-weight-bold">
                                R$ <?php echo e(number_format((float)$multa->valor_original, 2, ',', '.')); ?>

                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Desconto</span>
                            <span class="veiculo-field__value text-success">
                                <?php echo e($multa->valor_desconto ? 'R$ ' . number_format((float)$multa->valor_desconto, 2, ',', '.') : '—'); ?>

                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Valor Final a Pagar</span>
                            <span class="veiculo-field__value font-weight-bold" style="font-size:1.1rem">
                                R$ <?php echo e(number_format((float)$multa->valor_final, 2, ',', '.')); ?>

                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Status do Desconto</span>
                            <span class="veiculo-field__value">
                                <span class="badge badge-<?php echo e($badgeDesc); ?>"><?php echo e($multa->label_status_desconto); ?></span>
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Destino de Cobrança</span>
                            <span class="veiculo-field__value">
                                <?php if($multa->destino_cobranca === 'cliente'): ?>
                                    <span class="badge badge-warning"><i class="fas fa-user mr-1"></i>Cliente</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><i class="fas fa-building mr-1"></i>Empresa (débito interno)</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    
                    <?php if($multa->pago_em): ?>
                        <div class="alert alert-success py-2 px-3 mb-0">
                            <div class="d-flex align-items-center" style="gap:.75rem">
                                <i class="fas fa-check-circle fa-lg"></i>
                                <div>
                                    <strong>Pagamento confirmado</strong><br>
                                    <small>
                                        Em <?php echo e($multa->pago_em->format('d/m/Y H:i')); ?>

                                        &bull; Forma: <?php echo e(\App\Models\Multa::FORMAS_PAGAMENTO[$multa->forma_pagamento] ?? $multa->forma_pagamento); ?>

                                        <?php if($multa->referencia_pagamento): ?> &bull; Ref: <?php echo e($multa->referencia_pagamento); ?> <?php endif; ?>
                                        <?php if($multa->pagoPor): ?> &bull; Confirmado por: <?php echo e($multa->pagoPor->nome ?? $multa->pagoPor->name); ?> <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('multa.edit')): ?>
                        <div class="alert alert-warning py-2 px-3 mb-0">
                            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem">
                                <div>
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>Pagamento pendente</strong>
                                    <small class="d-block text-muted">Confirme o pagamento quando receber o comprovante ou o dinheiro em mãos.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-success" id="btnConfirmarPagamentoMulta">
                                    <i class="fas fa-check mr-1"></i>Registrar Pagamento
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($multa->observacao): ?>
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-comment-alt"></i>
                        <div><strong>Observação</strong></div>
                    </div>
                    <p class="small text-secondary mb-0" style="white-space:pre-wrap;"><?php echo e($multa->observacao); ?></p>
                </div>
            </div>
            <?php endif; ?>

        </div>

        
        <div class="col-lg-4">

            
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-traffic-light"></i>
                        <div><strong>Status</strong></div>
                    </div>
                    <div class="veiculo-summary">
                        <div class="veiculo-summary__row">
                            <span>Status da Multa</span>
                            <span class="badge badge-<?php echo e($badgeMulta); ?>"><?php echo e($multa->label_status_multa); ?></span>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Status do Desconto</span>
                            <span class="badge badge-<?php echo e($badgeDesc); ?>"><?php echo e($multa->label_status_desconto); ?></span>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Valor Final</span>
                            <strong class="text-danger">R$ <?php echo e(number_format((float)$multa->valor_final, 2, ',', '.')); ?></strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Pagamento</span>
                            <?php if($multa->pago_em): ?>
                                <span class="status-badge status-badge--green"><i class="fas fa-check mr-1"></i>Pago</span>
                            <?php else: ?>
                                <span class="status-badge status-badge--amber">Pendente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php
                $anexosComprovante = collect($arquivosRastreados ?? [])->filter(fn ($arquivo) => $arquivo->campo === 'anexo_comprovante');
                $anexosPdf = collect($arquivosRastreados ?? [])->filter(fn ($arquivo) => $arquivo->campo === 'anexo_pdf');
                $temAnexosMulta = $anexosComprovante->isNotEmpty() || $anexosPdf->isNotEmpty() || $multa->anexo_comprovante_path || $multa->anexo_pdf_path;
            ?>
            <?php if($temAnexosMulta): ?>
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-paperclip"></i>
                        <div><strong>Documentos Anexados</strong></div>
                    </div>
                    <div class="multa-doc-grid">
                        <?php $__empty_1 = true; $__currentLoopData = $anexosComprovante; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arquivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $urlArquivo = \Illuminate\Support\Facades\Storage::disk($arquivo->disk ?: 'public')->url($arquivo->path);
                            $extensao = strtolower(pathinfo((string) $arquivo->original_name, PATHINFO_EXTENSION));
                            $badgeClasse = in_array($extensao, ['jpg', 'jpeg', 'png', 'webp'], true) ? 'info' : 'secondary';
                        ?>
                        <div class="multa-doc-card multa-doc-card--image">
                            <div class="multa-doc-icon">
                                <i class="fas fa-image"></i>
                            </div>
                            <div class="multa-doc-body">
                                <div class="multa-doc-title"><?php echo e($arquivo->original_name ?: 'Comprovante'); ?></div>
                                <div class="multa-doc-meta">
                                    <span class="badge badge-<?php echo e($badgeClasse); ?>">Comprovante</span>
                                    <span><?php echo e($arquivo->mime_type ?: 'arquivo'); ?></span>
                                </div>
                            </div>
                            <a href="<?php echo e($urlArquivo); ?>" target="_blank" class="multa-doc-action">
                                <i class="fas fa-eye mr-1"></i>Abrir
                            </a>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if($multa->anexo_comprovante_path): ?>
                        <div class="multa-doc-card multa-doc-card--image">
                            <div class="multa-doc-icon">
                                <i class="fas fa-image"></i>
                            </div>
                            <div class="multa-doc-body">
                                <div class="multa-doc-title">Comprovante</div>
                                <div class="multa-doc-meta">
                                    <span class="badge badge-info">Comprovante</span>
                                    <span>arquivo legado</span>
                                </div>
                            </div>
                            <a href="<?php echo e($multa->anexo_comprovante_url); ?>" target="_blank" class="multa-doc-action">
                                <i class="fas fa-eye mr-1"></i>Abrir
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php $__empty_1 = true; $__currentLoopData = $anexosPdf; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arquivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $urlArquivo = \Illuminate\Support\Facades\Storage::disk($arquivo->disk ?: 'public')->url($arquivo->path);
                        ?>
                        <div class="multa-doc-card multa-doc-card--pdf">
                            <div class="multa-doc-icon multa-doc-icon--pdf">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="multa-doc-body">
                                <div class="multa-doc-title"><?php echo e($arquivo->original_name ?: 'PDF do Auto de Infração'); ?></div>
                                <div class="multa-doc-meta">
                                    <span class="badge badge-danger">PDF</span>
                                    <span><?php echo e($arquivo->mime_type ?: 'application/pdf'); ?></span>
                                </div>
                            </div>
                            <a href="<?php echo e($urlArquivo); ?>" target="_blank" class="multa-doc-action">
                                <i class="fas fa-eye mr-1"></i>Abrir
                            </a>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if($multa->anexo_pdf_path): ?>
                        <div class="multa-doc-card multa-doc-card--pdf">
                            <div class="multa-doc-icon multa-doc-icon--pdf">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="multa-doc-body">
                                <div class="multa-doc-title">PDF do Auto de Infração</div>
                                <div class="multa-doc-meta">
                                    <span class="badge badge-danger">PDF</span>
                                    <span>arquivo legado</span>
                                </div>
                            </div>
                            <a href="<?php echo e($multa->anexo_pdf_url); ?>" target="_blank" class="multa-doc-action">
                                <i class="fas fa-eye mr-1"></i>Abrir
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-bolt"></i>
                        <div><strong>Ações</strong></div>
                    </div>
                    <div class="veiculo-acoes-grid">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('multa.edit')): ?>
                        <a href="<?php echo e(route('multa.edit', $multa->id)); ?>" class="veiculo-acao veiculo-acao--primary">
                            <i class="fas fa-pen"></i><span>Editar multa</span>
                        </a>
                        <?php if($multa->reserva_id && in_array($multa->status_multa, ['pendente', 'em_analise'], true) && $multa->data_hora_infracao && $multa->veiculo_id): ?>
                        <form action="<?php echo e(route('multa.atribuirAoCliente', $multa->id)); ?>" method="POST" class="veiculo-acao-form">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="veiculo-acao">
                                <i class="fas fa-user-check"></i><span>Atribuir multa ao cliente</span>
                            </button>
                        </form>
                        <?php else: ?>
                        <button type="button" class="veiculo-acao" disabled title="A multa precisa estar vinculada a uma reserva para ser atribuída ao cliente.">
                            <i class="fas fa-user-check"></i><span>Atribuir multa ao cliente</span>
                        </button>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php if($codigoReserva): ?>
                        <a href="<?php echo e(route('reserva.show', $codigoReserva)); ?>" class="veiculo-acao">
                            <i class="fas fa-calendar-alt"></i><span>Ver reserva</span>
                        </a>
                        <?php endif; ?>
                        <?php if($multa->veiculo_id): ?>
                        <a href="<?php echo e(route('veiculo.show', $multa->veiculo_id)); ?>" class="veiculo-acao">
                            <i class="fas fa-car"></i><span>Ver veículo</span>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('multa.index')); ?>" class="veiculo-acao">
                            <i class="fas fa-list"></i><span>Lista de multas</span>
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
                    <?php if($multa->historico->isEmpty()): ?>
                        <p class="text-muted small mb-0">Nenhuma alteração registrada.</p>
                    <?php else: ?>
                    <div class="timeline timeline-inverse" style="max-height:320px;overflow-y:auto;">
                        <?php $__currentLoopData = $multa->historico; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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


<div class="modal fade" id="modalPagamentoMulta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle text-success mr-2"></i>Registrar Pagamento da Multa</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small py-2 px-3 mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Registre como o pagamento foi efetuado. Para pagamentos em <strong>dinheiro</strong> feitos presencialmente,
                    selecione "Dinheiro (cédulas)" — o sistema registrará quem confirmou e quando.
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Forma de Pagamento <span class="text-danger">*</span></label>
                    <select id="mp_forma" class="form-control">
                        <option value="">Selecione...</option>
                        <?php $__currentLoopData = \App\Models\Multa::FORMAS_PAGAMENTO; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <?php if(!empty($metodosPagamento)): ?>
                <div class="form-group d-none" id="mp_cartao_group">
                    <label class="font-weight-bold">Cartao do Cliente (Reserva)</label>
                    <select id="mp_payment_method" class="form-control">
                        <option value="">Selecione...</option>
                        <?php $__currentLoopData = $metodosPagamento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($card = $pm['card'] ?? null); ?>
                            <option value="<?php echo e($pm['id']); ?>">
                                <?php echo e(strtoupper($card->brand ?? 'CARD')); ?> **** <?php echo e($card->last4 ?? ''); ?>

                                (<?php echo e($card->exp_month ?? ''); ?>/<?php echo e($card->exp_year ?? ''); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <small class="text-muted">Se a forma for cartao de credito, o sistema cobra pelo Stripe usando o cartao do autor da reserva.</small>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="font-weight-bold">Referência / Comprovante</label>
                    <input type="text" id="mp_referencia" class="form-control" placeholder="Nº do comprovante, NSU, código PIX...">
                    <small class="text-muted">Opcional — anote o número que identifica este pagamento.</small>
                </div>
                <div class="alert alert-warning small py-2 px-3 mb-0">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Esta ação <strong>não pode ser desfeita</strong>. Confirme apenas após receber o comprovante ou o dinheiro.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btnSalvarPagamentoMulta">
                    <i class="fas fa-check mr-1"></i>Confirmar Pagamento
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
.module-card { background: linear-gradient(135deg,#1e293b 0%,#334155 100%); border-radius:16px; overflow:hidden; }
.module-card-header { display:flex; align-items:center; justify-content:space-between; padding:1.5rem; gap:1rem; flex-wrap:wrap; }
.module-header-left { display:flex; align-items:center; gap:1rem; }
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
.veiculo-acao-form { margin:0; }
.veiculo-acao-form .veiculo-acao { width:100%; }
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
.multa-doc-grid { display:grid;grid-template-columns:1fr;gap:.75rem; }
.multa-doc-card { display:flex;align-items:center;gap:.9rem;padding:.9rem 1rem;border:1px solid #e2e8f0;border-radius:14px;background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);box-shadow:0 1px 2px rgba(15,23,42,.04); }
.multa-doc-card--image { border-left:4px solid #3b82f6; }
.multa-doc-card--pdf { border-left:4px solid #ef4444; }
.multa-doc-icon { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:#dbeafe;color:#1d4ed8;font-size:1.1rem;flex-shrink:0; }
.multa-doc-icon--pdf { background:#fee2e2;color:#b91c1c; }
.multa-doc-body { min-width:0;flex:1; }
.multa-doc-title { font-size:.9rem;font-weight:800;color:#0f172a;line-height:1.2;word-break:break-word; }
.multa-doc-meta { display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-top:.25rem;font-size:.75rem;color:#64748b; }
.multa-doc-action { display:inline-flex;align-items:center;gap:.35rem;padding:.45rem .8rem;border-radius:999px;background:#0f172a;color:#fff;font-size:.76rem;font-weight:700;text-decoration:none;white-space:nowrap;transition:transform .15s,background .15s; }
.multa-doc-action:hover { background:#1d4ed8;color:#fff;text-decoration:none;transform:translateY(-1px); }
@media (max-width: 575.98px) {
    .multa-doc-card { align-items:flex-start;flex-direction:column; }
    .multa-doc-action { width:100%;justify-content:center; }
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'multa-show';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.module-card', popover: { title: 'Cabeçalho da multa', description: 'Exibe o número da multa, status atual, placa no momento da infração e a data/hora do evento. Use os botões à direita para editar ou voltar à lista.', side: 'bottom' } },
        { element: '.module-header-sub .badge:first-child', popover: { title: 'Status atual', description: 'Indica a situação da multa: <b>Aberta</b> (vermelho), <b>Paga</b> (verde), <b>Contestada</b> (âmbar) ou <b>Cancelada</b> (cinza). Atualizado conforme pagamento ou contestação.', side: 'bottom' } },
        { element: '.col-lg-8 .config-card:nth-child(1)', popover: { title: 'Identificação da Infração', description: 'Dados completos do auto: número, órgão autuador, data/hora da infração, vencimento, pontuação na CNH, placa, local e descrição.', side: 'right' } },
        { element: '.col-lg-8 .config-card:nth-child(2)', popover: { title: 'Vínculos', description: 'Mostra o veículo, a reserva associada, o cliente, o condutor e o responsável pelo lançamento. Clique nos links para navegar direto para a reserva ou veículo.', side: 'right' } },
        { element: '.col-lg-8 .config-card:nth-child(3)', popover: { title: 'Financeiro', description: 'Exibe o valor original, o desconto e o <b>valor final</b> em destaque. Mostra quem é responsável pelo pagamento (cliente ou empresa) e o status do pagamento.', side: 'right' } },
        { element: '#btnConfirmarPagamentoMulta', popover: { title: 'Registrar Pagamento', description: 'Clique aqui para confirmar o pagamento da multa. Você informará a forma de pagamento e uma referência (número de comprovante ou protocolo). O registro fica no histórico.', side: 'top' } },
        { element: '.col-lg-4', popover: { title: 'Painel lateral', description: 'Mostra o <b>resumo de status</b> (multa, desconto, valor final e pagamento), os <b>documentos anexados</b> (comprovante e PDF do auto) e as <b>ações rápidas</b> (editar, ver reserva, ver veículo).', side: 'left' } },
        { element: '.timeline', popover: { title: 'Histórico de alterações', description: 'Registro cronológico de todas as mudanças feitas na multa: quem alterou, quando, qual campo foi modificado e os valores anterior e novo.', side: 'top' } },
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
(function() {
    const btn = document.getElementById('btnConfirmarPagamentoMulta');
    if (btn) {
        btn.addEventListener('click', function () {
            $('#modalPagamentoMulta').modal('show');
        });
    }

    document.getElementById('btnSalvarPagamentoMulta')?.addEventListener('click', function () {
        const forma = document.getElementById('mp_forma').value;
        const ref   = document.getElementById('mp_referencia').value;
        const pmSel = document.getElementById('mp_payment_method');
        const paymentMethod = pmSel ? pmSel.value : null;

        if (!forma) {
            alert('Selecione a forma de pagamento.');
            return;
        }

        if (forma === 'cartao_credito' && pmSel && !paymentMethod) {
            alert('Selecione um cartao do cliente para cobrar.');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Aguarde...';

        fetch('<?php echo e(route("multa.confirmarPagamento", $multa->id)); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ forma_pagamento: forma, referencia_pagamento: ref, payment_method: paymentMethod }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i>Confirmar Pagamento';
                return;
            }
            $('#modalPagamentoMulta').modal('hide');
            location.reload();
        })
        .catch(() => {
            alert('Erro ao registrar pagamento. Tente novamente.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check mr-1"></i>Confirmar Pagamento';
        });
    });

    document.getElementById('mp_forma')?.addEventListener('change', function () {
        const group = document.getElementById('mp_cartao_group');
        if (!group) return;
        if (this.value === 'cartao_credito') group.classList.remove('d-none');
        else group.classList.add('d-none');
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/multa/show.blade.php ENDPATH**/ ?>