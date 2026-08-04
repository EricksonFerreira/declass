

<?php $__env->startSection('title', $empresa->nome); ?>

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
    $planoAtivo = $empresa->planoAtivo()->with('plano.itens')->first();
    $plano = $planoAtivo?->plano;
    $statusBadge = $planoAtivo?->status_badge ?? ['label' => 'Sem plano', 'class' => 'badge-secondary'];
    $totalUsers = $empresa->users()->count();
    $totalVeiculos = \App\Models\Veiculo::where('empresa_id', $empresa->id)->where('ativo', true)->where('vendido', false)->count();
    $logoUrl = $empresa->logo_url;
    $iniciais = collect(preg_split('/\s+/', trim($empresa->nome)))->filter()->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'EM';
    $planosContrato = $planos ?? collect();
    $representantePadrao = auth()->user()?->nome ?? $empresa->users()->orderBy('id')->value('nome') ?? $empresa->nome;
    $razaoSocialPadrao = $empresa->nome;
    $cnpjPadrao = $empresa->cnpj ?? '';
    $enderecoPadrao = trim((string) ($empresa->dominio ?? ''));
    $emailPadrao = $empresa->email ?? auth()->user()?->email ?? '';
    $telefonePadrao = $empresa->telefone ?? '';
    $quantidadeVeiculosPadrao = (int) ($planoAtivo?->plano?->limite_veiculos ?? 15);
    $valorPadrao = $planoAtivo?->valor_cobrado ?? $plano?->valor_mensal ?? $plano?->valor_anual ?? '';
?>

<?php $__env->startPush('css'); ?>
<style>
    .rs-page {
        padding: 0 !important;
    }

    .rs-page .rs-card,
    .rs-page .rs-hero {
        margin-bottom: 10px !important;
    }

    .rs-page .card,
    .rs-page .rs-card {
        border-radius: 12px;
    }

    .rs-page .rs-card__header {
        padding: 10px 14px;
    }

    .rs-page .rs-info-grid {
        gap: 10px;
        padding: 10px 12px 12px;
    }

    .rs-page .rs-info-card {
        padding: 12px;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
    }

    .rs-page .rs-hero__body {
        padding: 16px 18px;
    }

    .rs-page .rs-tabs-wrapper {
        margin-top: 0;
    }

    .rs-page .modal-body {
        padding: 16px;
    }

    .rs-page .modal-header,
    .rs-page .modal-footer {
        padding: 12px 16px;
    }

    .rs-contratos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 12px;
        padding: 12px;
    }

    .rs-contrato-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        padding: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .rs-contrato-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .rs-contrato-numero {
        font-size: .78rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
    }

    .rs-contrato-titulo {
        font-size: .98rem;
        font-weight: 800;
        line-height: 1.25;
        margin-top: 3px;
    }

    .rs-contrato-meta {
        font-size: .78rem;
        color: #64748b;
    }

    .rs-contrato-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .rs-contrato-actions .btn,
    .rs-contrato-actions form {
        margin: 0;
    }

    .rs-modal-mini .modal-dialog {
        max-width: 760px;
    }

    .rs-modal-mini .modal-body {
        padding: 16px 18px 10px;
    }

    .rs-modal-mini .modal-footer {
        padding: 12px 18px 18px;
    }
</style>
<?php $__env->stopPush(); ?>

<div class="rs-page">

    
    <div class="rs-hero rs-hero--<?php echo e($empresa->ativo === 'S' ? 'confirmada' : 'cancelada'); ?>">
        <div class="rs-hero__accent"></div>
        <div class="rs-hero__body">
            <div class="rs-hero__left">
                <div class="rs-hero__eyebrow">
                    <i class="fas fa-building"></i>
                    Empresa #<?php echo e($empresa->id); ?>

                </div>
                <h1 class="rs-hero__title"><?php echo e($empresa->nome); ?></h1>
                <div class="rs-hero__meta">
                    <span><i class="fas fa-globe"></i><?php echo e($empresa->slug ?: 'Sem slug'); ?></span>
                    <?php if($empresa->cnpj): ?>
                        <span><i class="fas fa-id-card"></i><?php echo e($empresa->cnpj); ?></span>
                    <?php endif; ?>
                    <span><i class="fas fa-calendar-plus"></i>Criada em <?php echo e($empresa->created_at?->format('d/m/Y H:i') ?: '-'); ?></span>
                </div>
            </div>
            <div class="rs-hero__right">
                <span class="rs-status-badge <?php echo e($empresa->ativo === 'S' ? 'is-success' : 'is-danger'); ?>">
                    <i class="fas <?php echo e($empresa->ativo === 'S' ? 'fa-check-circle' : 'fa-times-circle'); ?>"></i>
                    <?php echo e($empresa->ativo === 'S' ? 'ATIVO' : 'INATIVO'); ?>

                </span>
                <div class="rs-hero__kpis">
                    <div class="rs-kpi">
                        <span>Usuarios</span>
                        <strong><?php echo e($totalUsers); ?></strong>
                    </div>
                    <div class="rs-kpi-divider"></div>
                    <div class="rs-kpi">
                        <span>Veiculos</span>
                        <strong><?php echo e($totalVeiculos); ?></strong>
                    </div>
                    <?php if($plano): ?>
                        <div class="rs-kpi-divider"></div>
                        <div class="rs-kpi rs-kpi--accent">
                            <span>Plano</span>
                            <strong><?php echo e($plano->nome); ?></strong>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    
    

    
    <div class="rs-tabs-wrapper">
        <ul class="nav nav-tabs rs-nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-dados" data-toggle="tab" href="#pane-dados" role="tab">
                    <i class="fas fa-building"></i>
                    <span>Dados</span>
                </a>
                
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-plano" data-toggle="tab" href="#pane-plano" role="tab">
                    <i class="fas fa-layer-group"></i>
                    <span>Plano</span>
                </a>
            </li>
            <li class="nav-item" style="display:flex;justify-content:space-between;align-items:center;flex:1;">
            
                <a class="nav-link" id="tab-pagamentos" data-toggle="tab" href="#pane-pagamentos" role="tab">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Pagamentos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-contratos" data-toggle="tab" href="#pane-contratos" role="tab">
                    <i class="fas fa-file-signature"></i>
                    <span>Contratos</span>
                </a>
            </li>

            <li class="nav-item ml-auto" style="display:flex;gap:10px;align-items:center;">
                <a href="<?php echo e(route('empresa.editar')); ?>" class="rs-btn rs-btn--primary rs-btn--sm">
                    <i class="fas fa-pen mr-1"></i> Editar
                </a>
                <?php if($empresa->site_apresentacao_ativo && $plano?->site_vendas): ?>
                    <a href="<?php echo e(route('empresa.site-editor.edit', $empresa->id)); ?>" class="rs-btn bg-info rs-btn--sm">
                        <i class="fas fa-globe mr-1"></i> Site
                    </a>
                <?php endif; ?>
                <a href="<?php echo e(route('assinatura.meu-plano')); ?>" class="rs-btn rs-btn--sm">
                    <i class="fas fa-credit-card mr-1"></i> Assinatura
                </a>
            </li>

        </ul>

        <div class="tab-content rs-tab-content">

            
            <div class="tab-pane fade show active" id="pane-dados" role="tabpanel">
                <div class="rs-grid">
                    <div class="rs-main">

                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-info-circle rs-card__icon"></i>
                                <h2>Informacoes da Empresa</h2>
                            </div>
                            <div class="rs-info-grid">
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-tag"></i> Nome</span>
                                    <strong class="rs-info-card__value"><?php echo e($empresa->nome); ?></strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-globe"></i> Slug</span>
                                    <strong class="rs-info-card__value"><?php echo e($empresa->slug ?? '—'); ?></strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-id-card"></i> CNPJ</span>
                                    <strong class="rs-info-card__value"><?php echo e($empresa->cnpj ?? '—'); ?></strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-envelope"></i> E-mail</span>
                                    <strong class="rs-info-card__value"><?php echo e($empresa->email ?? '—'); ?></strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-phone"></i> Telefone</span>
                                    <strong class="rs-info-card__value"><?php echo e($empresa->telefone ?? '—'); ?></strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-check-circle"></i> Status</span>
                                    <strong class="rs-info-card__value">
                                        <?php if($empresa->ativo === 'S'): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php endif; ?>
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <?php if($empresa->cor_primaria || $empresa->cor_secundaria): ?>
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-palette rs-card__icon"></i>
                                <h2>Cores da Marca</h2>
                            </div>
                            <div class="d-flex" style="gap:16px;padding:12px 16px;">
                                <?php if($empresa->cor_primaria): ?>
                                    <div>
                                        <span class="text-muted small">Primaria</span>
                                        <div style="width:48px;height:48px;border-radius:10px;background:<?php echo e($empresa->cor_primaria); ?>;border:1px solid #e2e8f0;margin-top:4px;"></div>
                                        <span style="font-size:.72rem;color:#64748b;"><?php echo e($empresa->cor_primaria); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($empresa->cor_secundaria): ?>
                                    <div>
                                        <span class="text-muted small">Secundaria</span>
                                        <div style="width:48px;height:48px;border-radius:10px;background:<?php echo e($empresa->cor_secundaria); ?>;border:1px solid #e2e8f0;margin-top:4px;"></div>
                                        <span style="font-size:.72rem;color:#64748b;"><?php echo e($empresa->cor_secundaria); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <div class="rs-sidebar">

                        <?php if($logoUrl): ?>
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-image rs-card__icon"></i>
                                <h2>Logo</h2>
                            </div>
                            <div class="text-center p-3">
                                <img src="<?php echo e($logoUrl); ?>" alt="Logo <?php echo e($empresa->nome); ?>" style="max-width:100%;max-height:120px;border-radius:8px;">
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if($plano): ?>
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-layer-group rs-card__icon"></i>
                                <h2>Plano Atual</h2>
                            </div>
                            <div class="rs-info-grid">
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-tag"></i> Plano</span>
                                    <strong class="rs-info-card__value"><?php echo e($plano->nome); ?></strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-circle"></i> Status</span>
                                    <strong class="rs-info-card__value"><span class="badge text-white <?php echo e($statusBadge['class']); ?>"><?php echo e($statusBadge['label']); ?></span></strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-calendar-alt"></i> Periodicidade</span>
                                    <strong class="rs-info-card__value"><?php echo e($planoAtivo->periodicidade_texto ?? ($planoAtivo->periodicidade === 'anual' ? 'Anual' : 'Mensal')); ?></strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-hourglass-end"></i> Vencimento</span>
                                    <strong class="rs-info-card__value"><?php echo e($planoAtivo->data_vencimento?->format('d/m/Y') ?? '—'); ?></strong>
                                </div>
                                <?php
                                    $storageMb = $plano->limite_armazenamento_mb ?? 0;
                                    $storageTxt = $storageMb >= 1024 ? number_format($storageMb / 1024, 1, ',', '.') . ' GB' : $storageMb . ' MB';
                                ?>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-database"></i> Armazenamento</span>
                                    <strong class="rs-info-card__value"><?php echo e($storageTxt); ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            
            <div class="tab-pane fade" id="pane-plano" role="tabpanel">
                <?php if($plano): ?>
                <div class="rs-plan-card">
                    <div class="rs-plan-price">
                        <div class="rs-plan-amount">R$ <?php echo e(number_format($planoAtivo->valor_cobrado, 2, ',', '.')); ?></div>
                        <div class="rs-plan-period"><?php echo e($planoAtivo->periodicidade_texto ?? ($planoAtivo->periodicidade === 'anual' ? 'Anual' : 'Mensal')); ?></div>
                    </div>
                    <div class="rs-plan-info">
                        <div class="rs-plan-header">
                            <h2 class="rs-plan-name"><?php echo e($plano->nome); ?></h2>
                            <span class="badge <?php echo e($statusBadge['class']); ?>" style="font-size:.72rem;"><?php echo e($statusBadge['label']); ?></span>
                        </div>
                        <div class="rs-plan-dates">
                            <div class="rs-plan-date"><span>Inicio</span><strong><?php echo e($planoAtivo->data_inicio?->format('d/m/Y') ?? '—'); ?></strong></div>
                            <div class="rs-plan-date"><span>Vencimento</span><strong><?php echo e($planoAtivo->data_vencimento?->format('d/m/Y') ?? '—'); ?></strong></div>
                            <div class="rs-plan-date"><span>Metodo</span><strong><?php echo e($planoAtivo->metodo_cobranca_texto ?? ucfirst($planoAtivo->metodo_cobranca ?? '—')); ?></strong></div>
                            <?php if($planoAtivo->renovacao_automatica): ?>
                            <div class="rs-plan-date"><span>Renovacao</span><strong class="text-success">Automatica</strong></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if($plano->itens->count()): ?>
                <div class="rs-card">
                    <div class="rs-card__header">
                        <i class="fas fa-list rs-card__icon"></i>
                        <h2>Recursos Inclusos</h2>
                        <span class="rs-badge-count"><?php echo e($plano->itens->count()); ?></span>
                    </div>
                    <div class="rs-items-grid">
                        <?php $__currentLoopData = $plano->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rs-item <?php echo e($item->incluso ? '' : 'rs-item--disabled'); ?>">
                                <div class="rs-item-icon <?php echo e($item->incluso ? 'rs-item-icon--yes' : 'rs-item-icon--no'); ?>">
                                    <i class="fas fa-<?php echo e($item->incluso ? 'check' : 'times'); ?>"></i>
                                </div>
                                <span class="rs-item-text"><?php echo e($item->descricao); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="rs-card">
                    <div class="rs-card__header">
                        <i class="fas fa-box-open rs-card__icon"></i>
                        <h2>Sem plano ativo</h2>
                    </div>
                    <div class="p-3">
                        <p class="text-muted">Esta empresa nao possui um plano ativo no momento.</p>
                        <a href="<?php echo e(route('assinatura.meu-plano')); ?>" class="rs-btn rs-btn--primary">
                            <i class="fas fa-layer-group mr-1"></i> Ver planos
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            
            <div class="tab-pane fade" id="pane-pagamentos" role="tabpanel">
                <?php $pagamentos = $empresa->pagamentosPlano()->orderByDesc('created_at')->limit(20)->get(); ?>
                <?php if($pagamentos->count()): ?>
                <div class="rs-card">
                    <div class="rs-card__header">
                        <i class="fas fa-history rs-card__icon"></i>
                        <h2>Historico de Pagamentos</h2>
                        <span class="ml-auto badge badge-secondary"><?php echo e($pagamentos->count()); ?></span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="rs-table">
                            <thead>
                                <tr>
                                    <th>Referencia</th>
                                    <th>Valor</th>
                                    <th>Metodo</th>
                                    <th>Status</th>
                                    <th>Vencimento</th>
                                    <th>Pago em</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $pagamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="font-weight-bold" style="font-size:.78rem;"><?php echo e($pag->referencia_interna ?? '#' . $pag->id); ?></td>
                                    <td>R$ <?php echo e(number_format($pag->valor, 2, ',', '.')); ?></td>
                                    <td><?php echo e(ucfirst($pag->metodo ?? '—')); ?></td>
                                    <td>
                                        <?php
                                            $pagBadge = match($pag->status) {
                                                'pago' => 'bg-success',
                                                'pendente' => 'bg-warning',
                                                'vencido' => 'bg-danger',
                                                'cancelado' => 'bg-secondary',
                                                default => 'bg-info',
                                            };
                                        ?>
                                        <span class="badge <?php echo e($pagBadge); ?>"><?php echo e(ucfirst($pag->status)); ?></span>
                                    </td>
                                    <td style="font-size:.82rem;"><?php echo e($pag->data_vencimento?->format('d/m/Y') ?? '—'); ?></td>
                                    <td style="font-size:.82rem;"><?php echo e($pag->pago_em?->format('d/m/Y') ?? '—'); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <div class="rs-card">
                    <div class="card-body">
                        <div class="rs-empty">
                            <i class="fas fa-inbox"></i>
                            <span>Nenhum pagamento registrado.</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            
            <div class="tab-pane fade" id="pane-contratos" role="tabpanel">
                <?php if (isset($component)) { $__componentOriginal20ef3876cb73d6ca6a907fff1d6485be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal20ef3876cb73d6ca6a907fff1d6485be = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empresa.contratos','data' => ['empresa' => $empresa,'contratos' => $contratos,'planosContrato' => $planosContrato,'razaoSocialPadrao' => $razaoSocialPadrao,'cnpjPadrao' => $cnpjPadrao,'emailPadrao' => $emailPadrao,'telefonePadrao' => $telefonePadrao,'enderecoPadrao' => $enderecoPadrao,'representantePadrao' => $representantePadrao,'quantidadeVeiculosPadrao' => $quantidadeVeiculosPadrao,'valorPadrao' => $valorPadrao]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empresa.contratos'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['empresa' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($empresa),'contratos' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contratos),'planos-contrato' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($planosContrato),'razao-social-padrao' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($razaoSocialPadrao),'cnpj-padrao' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cnpjPadrao),'email-padrao' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($emailPadrao),'telefone-padrao' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($telefonePadrao),'endereco-padrao' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($enderecoPadrao),'representante-padrao' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($representantePadrao),'quantidade-veiculos-padrao' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quantidadeVeiculosPadrao),'valor-padrao' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($valorPadrao)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal20ef3876cb73d6ca6a907fff1d6485be)): ?>
<?php $attributes = $__attributesOriginal20ef3876cb73d6ca6a907fff1d6485be; ?>
<?php unset($__attributesOriginal20ef3876cb73d6ca6a907fff1d6485be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal20ef3876cb73d6ca6a907fff1d6485be)): ?>
<?php $component = $__componentOriginal20ef3876cb73d6ca6a907fff1d6485be; ?>
<?php unset($__componentOriginal20ef3876cb73d6ca6a907fff1d6485be); ?>
<?php endif; ?>
            </div>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
.content-wrapper { background:#f0f4f9; }

.rs-page { display:grid; gap:20px; padding-bottom:32px; }

.rs-hero { position:relative; border-radius:20px; background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%); overflow:hidden; box-shadow:0 8px 32px rgba(15,23,42,.20); }
.rs-hero__accent { position:absolute; top:0;left:0;right:0; height:4px; background:linear-gradient(90deg,#3b82f6,#8b5cf6); }
.rs-hero--cancelada .rs-hero__accent { background:linear-gradient(90deg,#ef4444,#f87171); }
.rs-hero__body { display:flex; align-items:flex-start; justify-content:space-between; gap:24px; padding:28px 30px; flex-wrap:wrap; }
.rs-hero__left { display:grid; gap:8px; }
.rs-hero__eyebrow { display:flex; align-items:center; gap:6px; font-size:.72rem; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:rgba(148,163,184,.9); }
.rs-hero__title { margin:0; font-size:1.9rem; font-weight:800; letter-spacing:-.03em; color:#fff; line-height:1.15; }
.rs-hero__meta { display:flex; flex-wrap:wrap; gap:8px 18px; color:rgba(148,163,184,.85); font-size:.84rem; }
.rs-hero__meta span { display:flex; align-items:center; gap:6px; }
.rs-hero__right { display:flex; flex-direction:column; align-items:flex-end; gap:16px; }
.rs-hero__kpis { display:flex; align-items:center; gap:0; background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.1); border-radius:14px; overflow:hidden; }
.rs-kpi { padding:14px 20px; text-align:center; }
.rs-kpi span { display:block; font-size:.68rem; text-transform:uppercase; letter-spacing:.12em; color:rgba(148,163,184,.8); margin-bottom:4px; }
.rs-kpi strong { display:block; font-size:1.1rem; font-weight:800; color:#fff; }
.rs-kpi--accent strong { color:#fbbf24; }
.rs-kpi-divider { width:1px; height:40px; background:rgba(255,255,255,.1); }

.rs-status-badge { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:999px; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; white-space:nowrap; }
.rs-status-badge.is-success { background:rgba(16,185,129,.15); color:#059669; border:1px solid rgba(16,185,129,.25); }
.rs-status-badge.is-danger { background:rgba(239,68,68,.15); color:#dc2626; border:1px solid rgba(239,68,68,.25); }

.rs-tabs-wrapper { background:#fff; border:1px solid #e2e8f0; border-radius:20px; overflow:hidden; box-shadow:0 2px 12px rgba(15,23,42,.05); }
.rs-nav-tabs { background:#f8fafc; border-bottom:2px solid #e2e8f0; padding:0 16px; flex-wrap:nowrap; overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none; }
.rs-nav-tabs::-webkit-scrollbar { display:none; }
.rs-nav-tabs .nav-item { flex-shrink:0; }
.rs-nav-tabs .nav-link { display:flex; align-items:center; gap:7px; padding:14px 18px; font-size:.83rem; font-weight:600; color:#64748b; border:none; border-bottom:3px solid transparent; border-radius:0; white-space:nowrap; transition:color .15s,border-color .15s; margin-bottom:-2px; }
.rs-nav-tabs .nav-link:hover { color:#2563eb; border-bottom-color:#bfdbfe; background:transparent; }
.rs-nav-tabs .nav-link.active { color:#2563eb; border-bottom-color:#2563eb; background:transparent; font-weight:700; }
.rs-nav-tabs .nav-link i { font-size:.85rem; }
.rs-tab-content { padding:24px; }

.rs-grid { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(290px,.75fr); gap:20px; align-items:start; }
.rs-main, .rs-sidebar { display:grid; gap:16px; }

.rs-card { background:#fff; border:1px solid #e2e8f0; border-radius:18px; padding:20px 22px; box-shadow:0 2px 12px rgba(15,23,42,.05); }
.rs-card__header { display:flex; align-items:center; gap:10px; margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid #f1f5f9; }
.rs-card__icon { width:32px; height:32px; border-radius:9px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:.82rem; flex-shrink:0; }
.rs-card__header h2 { margin:0; font-size:.92rem; font-weight:700; color:#0f172a; }

.rs-info-rows { display:grid; gap:0; }
.rs-info-row { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:9px 0; border-bottom:1px solid #f1f5f9; }
.rs-info-row:last-child { border-bottom:0; padding-bottom:0; }
.rs-info-row span { display:flex; align-items:center; gap:6px; font-size:.78rem; color:#64748b; }
.rs-info-row span i { width:14px; text-align:center; color:#94a3b8; }
.rs-info-row strong { font-size:.82rem; font-weight:600; color:#0f172a; text-align:right; }

.rs-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:10px; font-size:.82rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; transition:all .18s; color:#fff; background:#0f172a; }
.rs-btn:hover { color:#fff; text-decoration:none; background:#1e293b; }
.rs-btn--primary { background:#2563eb; }
.rs-btn--primary:hover { background:#1d4ed8; }

.rs-empty { display:flex; flex-direction:column; align-items:center; gap:8px; padding:20px; color:#94a3b8; text-align:center; }
.rs-empty i { font-size:1.8rem; }
.rs-empty span { font-size:.8rem; }

.rs-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.rs-table th { padding:9px 12px; text-align:left; font-weight:700; color:#475569; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; border-bottom:2px solid #e2e8f0; background:#f8fafc; }
.rs-table td { padding:9px 12px; color:#334155; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.rs-table tbody tr:last-child td { border-bottom:0; }
.rs-table tbody tr:hover td { background:#f8fafc; }

/* ═══ Enhanced Info Page ═══ */
.rs-hero {
    position:relative; border-radius:16px; overflow:hidden; margin-bottom:24px;
    background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);
    box-shadow:0 4px 24px rgba(0,0,0,.08);
}
.rs-hero__accent {
    position:absolute; top:-60px; right:-60px; width:200px; height:200px;
    border-radius:50%; background:rgba(255,255,255,.03);
}
.rs-hero__body { position:relative; z-index:1; padding:32px; display:flex; justify-content:space-between; align-items:center; gap:24px; flex-wrap:wrap; }
.rs-hero__left { flex:1; min-width:240px; }
.rs-hero__eyebrow { font-size:.72rem; font-weight:600; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; display:flex; align-items:center; gap:6px; }
.rs-hero__title { font-size:1.5rem; font-weight:800; color:#fff; margin:0 0 8px; line-height:1.1; }
.rs-hero__meta { display:flex; flex-wrap:wrap; gap:8px 16px; }
.rs-hero__meta span { font-size:.78rem; color:rgba(255,255,255,.55); display:flex; align-items:center; gap:5px; }
.rs-hero__meta span i { width:14px; font-size:.7rem; }
.rs-hero__right { display:flex; align-items:center; gap:20px; flex-shrink:0; }
.rs-status-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:20px; font-size:.72rem; font-weight:700; letter-spacing:.05em; }
.rs-status-badge.is-success { background:rgba(255,255,255,.12); color:#4ade80; }
.rs-status-badge.is-danger { background:rgba(255,255,255,.12); color:#f87171; }
.rs-hero__kpis { display:flex; align-items:center; gap:16px; }
.rs-kpi { text-align:center; }
.rs-kpi span { display:block; font-size:.65rem; color:rgba(255,255,255,.45); text-transform:uppercase; letter-spacing:.06em; font-weight:600; }
.rs-kpi strong { display:block; font-size:1.2rem; font-weight:800; color:#fff; margin-top:2px; }
.rs-kpi--accent strong { color:#fbbf24; }
.rs-kpi-divider { width:1px; height:32px; background:rgba(255,255,255,.1); }

.rs-card {
    background:#fff; border-radius:14px; border:1px solid #f1f5f9;
    box-shadow:0 1px 3px rgba(0,0,0,.04); overflow:hidden; margin-bottom:16px;
    transition:box-shadow .2s;
}
.rs-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.06); }
.rs-card__header {
    display:flex; align-items:center; gap:10px; padding:16px 20px;
    border-bottom:1px solid #f8fafc; background:#fafbfc;
}
.rs-card__header h2 { font-size:.9rem; font-weight:700; color:#0f172a; margin:0; }
.rs-card__icon { width:20px; height:20px; border-radius:6px; background:#eef2ff; color:#4f46e5; display:flex; align-items:center; justify-content:center; font-size:.75rem; }

.rs-info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; padding:16px 20px; }
.rs-info-card { padding:12px; background:#f8fafc; border-radius:10px; border:1px solid #f1f5f9; }
.rs-info-card__label { font-size:.72rem; color:#64748b; display:flex; align-items:center; gap:5px; margin-bottom:4px; }
.rs-info-card__label i { width:12px; font-size:.65rem; color:#94a3b8; }
.rs-info-card__value { font-size:.9rem; font-weight:700; color:#0f172a; display:block; }

.rs-plan-card { display:grid; grid-template-columns:auto 1fr; gap:20px; align-items:start; background:#fff; border-radius:14px; border:1px solid #f1f5f9; box-shadow:0 1px 3px rgba(0,0,0,.04); padding:24px; margin-bottom:16px; transition:box-shadow .2s; }
.rs-plan-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.06); }
.rs-plan-price { text-align:center; padding:16px 20px; background:linear-gradient(135deg,#0f172a,#1e293b); border-radius:12px; color:#fff; min-width:120px; }
.rs-plan-amount { font-size:1.5rem; font-weight:900; line-height:1.2; }
.rs-plan-period { font-size:.68rem; opacity:.6; text-transform:uppercase; letter-spacing:.05em; margin-top:2px; }
.rs-plan-header { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.rs-plan-name { margin:0; font-size:1.05rem; font-weight:700; color:#0f172a; }
.rs-plan-dates { display:flex; flex-wrap:wrap; gap:6px 20px; margin-top:12px; }
.rs-plan-date { display:flex; flex-direction:column; gap:1px; }
.rs-plan-date span { font-size:.7rem; color:#64748b; text-transform:uppercase; letter-spacing:.04em; }
.rs-plan-date strong { font-size:.85rem; font-weight:600; color:#0f172a; }
.rs-badge-count { margin-left:auto; padding:2px 10px; border-radius:20px; background:#e0f2fe; color:#0284c7; font-size:.72rem; font-weight:700; }
.rs-items-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:10px; padding:16px 20px; }
.rs-item { display:flex; align-items:center; gap:10px; padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; transition:border-color .15s; }
.rs-item:hover { border-color:#cbd5e1; }
.rs-item--disabled { opacity:.5; }
.rs-item-icon { width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.65rem; }
.rs-item-icon--yes { background:#dcfce7; color:#16a34a; }
.rs-item-icon--no { background:#f1f5f9; color:#94a3b8; }
.rs-item-text { font-size:.8rem; color:#0f172a; font-weight:500; }

.rs-color-swatch { display:flex; align-items:center; gap:10px; padding:8px 12px; background:#f8fafc; border-radius:10px; border:1px solid #f1f5f9; }
.rs-color-swatch__dot { width:36px; height:36px; border-radius:8px; flex-shrink:0; }
.rs-color-swatch__info { display:flex; flex-direction:column; }
.rs-color-swatch__label { font-size:.7rem; color:#64748b; text-transform:uppercase; letter-spacing:.04em; }
.rs-color-swatch__hex { font-size:.82rem; font-weight:700; color:#0f172a; font-family:monospace; }

.rs-grid { display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start; }
.rs-sidebar .rs-card { margin-bottom:16px; }

.modal-content { border-radius: 18px; overflow: hidden; border: 0; box-shadow: 0 24px 70px rgba(15, 23, 42, .22); }
.modal-header { background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 100%); border-bottom: 1px solid #e2e8f0; }
.modal-title { font-weight: 800; color: #0f172a; }
.modal-body .form-control { border-radius: 12px; border-color: #dbe4ef; }
.modal-body .form-control:focus { border-color: #4b80ff; box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .15); }

@media(max-width:768px){
    .rs-grid { grid-template-columns:1fr; }
    .rs-hero__body { flex-direction:column; align-items:flex-start; }
    .rs-hero__kpis { flex-wrap:wrap; }
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
    (function () {
        var planoSelect = document.getElementById('empresaContratoPlano');
        var valorInput = document.getElementById('empresaContratoValor');
        var form = document.querySelector('#modalGerarContratoEmpresa form');
        var submitBtn = document.getElementById('btnGerarContratoEmpresa');
        if (!planoSelect || !valorInput || !form || !submitBtn) return;

        function syncValor() {
            var option = planoSelect.options[planoSelect.selectedIndex];
            if (!option || !option.dataset.valor) return;
            valorInput.value = option.dataset.valor;
        }

        function maskTelefone(value) {
            var digits = String(value || '').replace(/\D/g, '').slice(0, 11);
            if (digits.length <= 10) {
                return digits.replace(/^(\d{2})(\d{4})(\d{0,4})$/, function (_, ddd, first, last) {
                    return last ? '(' + ddd + ') ' + first + '-' + last : '(' + ddd + ') ' + first;
                });
            }
            return digits.replace(/^(\d{2})(\d{5})(\d{0,4})$/, function (_, ddd, first, last) {
                return last ? '(' + ddd + ') ' + first + '-' + last : '(' + ddd + ') ' + first;
            });
        }

        planoSelect.addEventListener('change', syncValor);
        syncValor();

        var telefoneInput = document.getElementById('telefoneClienteContrato');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function (event) {
                event.target.value = maskTelefone(event.target.value);
            });
        }

        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processando...';
        });
    }());
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/empresa/show.blade.php ENDPATH**/ ?>