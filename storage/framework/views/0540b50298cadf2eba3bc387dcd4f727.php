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
    $digits = fn ($value) => preg_replace('/\D+/', '', (string) $value);

    $waLink = function ($empresa, $pagamento) use ($digits) {
        $phone = $digits($empresa?->telefone ?? $empresa?->celular ?? '');
        if ($phone === '') {
            return null;
        }
        if (! str_starts_with($phone, '55')) {
            $phone = '55'.$phone;
        }

        $valor = number_format((float) $pagamento->valor, 2, ',', '.');
        $vencimento = optional($pagamento->data_vencimento)->format('d/m/Y');
        $plano = $pagamento->empresaPlano?->plano?->nome ?? 'plano';

        $message = "Olá! Estou entrando em contato sobre a assinatura {$plano}, no valor de R$ {$valor}, com vencimento em {$vencimento}. Se já foi pago, desconsidere.";

        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    };

    $statusLabel = function ($dias) {
        if ($dias < 0) return ['Atrasado', 'danger', 'fas fa-exclamation-circle'];
        if ($dias <= 3) return ['Urgente', 'danger', 'fas fa-bolt'];
        if ($dias <= 7) return ['Próximo', 'warning', 'fas fa-clock'];
        return ['Acompanhamento', 'secondary', 'fas fa-clipboard-check'];
    };
?>

<style>
    .mp-page { display:grid; gap:20px; }
    .mp-hero {
        border-radius:24px; padding:24px; color:#fff;
        background:
            radial-gradient(circle at top right, rgba(56,189,248,.18), transparent 25%),
            radial-gradient(circle at bottom left, rgba(16,185,129,.14), transparent 22%),
            linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #2563eb 100%);
        box-shadow:0 24px 60px rgba(15,23,42,.18);
    }
    .mp-hero__eyebrow { font-size:.72rem; letter-spacing:.18em; text-transform:uppercase; font-weight:800; opacity:.72; }
    .mp-hero__title { margin:.35rem 0 .25rem; font-size:1.6rem; font-weight:900; letter-spacing:-.02em; }
    .mp-hero__text { margin:0; max-width:62rem; color:rgba(255,255,255,.86); line-height:1.6; }
    .mp-hero__chips { display:flex; flex-wrap:wrap; gap:10px; margin-top:16px; }
    .mp-chip {
        display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px;
        background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.14); font-size:.8rem; font-weight:700;
    }
    .mp-kpi {
        border:1px solid #dbe4f0; border-radius:18px; background:#fff; box-shadow:0 14px 36px rgba(15,23,42,.06); overflow:hidden; height:100%;
    }
    .mp-kpi__accent { height:4px; }
    .mp-kpi__body { padding:18px; position:relative; min-height:128px; }
    .mp-kpi__label { margin:0 0 10px; color:#64748b; font-size:.78rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .mp-kpi__value { margin:0; font-size:1.65rem; font-weight:900; letter-spacing:-.03em; }
    .mp-kpi__meta { display:block; margin-top:8px; color:#64748b; font-size:.82rem; }
    .mp-kpi__icon { position:absolute; right:18px; top:18px; font-size:2rem; opacity:.12; }
    .mp-kpi--blue .mp-kpi__accent { background:linear-gradient(90deg, #2563eb, #60a5fa); }
    .mp-kpi--green .mp-kpi__accent { background:linear-gradient(90deg, #059669, #34d399); }
    .mp-kpi--amber .mp-kpi__accent { background:linear-gradient(90deg, #d97706, #fbbf24); }
    .mp-kpi--red .mp-kpi__accent { background:linear-gradient(90deg, #dc2626, #fb7185); }
    .mp-card {
        border:1px solid #dbe4f0; border-radius:18px; background:#fff; box-shadow:0 14px 36px rgba(15,23,42,.06); overflow:hidden;
    }
    .mp-card__header {
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        padding:16px 18px; border-bottom:1px solid #e8eef6; background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
    }
    .mp-card__body { padding:18px; }
    .mp-scroll {
        max-height: 420px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 6px;
        scroll-behavior: smooth;
    }
    .mp-scroll::-webkit-scrollbar { width: 10px; }
    .mp-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
        border: 2px solid #f8fbff;
    }
    .mp-scroll::-webkit-scrollbar-track { background: #f8fbff; }
    .mp-title { margin:0; font-size:1rem; font-weight:800; color:#0f172a; }
    .mp-sub { margin:4px 0 0; font-size:.82rem; color:#64748b; }
    .mp-label { display:inline-block; margin-bottom:.4rem; font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:#64748b; }
    .mp-input { height:40px; border-radius:12px; border-color:#d7e0ea; box-shadow:none; }
    .mp-input:focus { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.12); }
    .mp-btn { min-height:40px; border-radius:12px; font-weight:800; }
    .mp-grid { display:grid; gap:14px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .mp-list { display:grid; gap:10px; }
    .mp-item {
        border:1px solid #e8eef6; border-radius:16px; padding:14px 16px; background:#fff;
        display:grid; grid-template-columns: 1.4fr .7fr .7fr .8fr auto; gap:10px; align-items:center;
    }
    .mp-item:hover { background:#f8fbff; }
    .mp-company { font-weight:800; color:#0f172a; }
    .mp-muted { color:#64748b; font-size:.82rem; }
    .mp-pill { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; font-size:.74rem; font-weight:800; }
    .mp-pill--danger { background:#fef2f2; color:#b91c1c; }
    .mp-pill--warn { background:#fff7ed; color:#9a3412; }
    .mp-pill--muted { background:#f1f5f9; color:#475569; }
    .mp-sections { display:grid; gap:16px; }
    .mp-row-wrap { min-width: 0; }
    .mp-list-wrap { min-width: 0; }
    @media (max-width: 1199px) {
        .mp-grid { grid-template-columns: 1fr; }
        .mp-item { grid-template-columns: 1fr; }
        .mp-scroll { max-height: 360px; }
    }
    @media (max-width: 767.98px) {
        .mp-page { gap:16px; }
        .mp-hero { padding:18px; border-radius:18px; }
        .mp-hero__title { font-size:1.25rem; }
        .mp-card__header { padding:14px 16px; flex-direction:column; align-items:flex-start; }
        .mp-card__body { padding:14px; }
        .mp-scroll { max-height: 300px; }
        .mp-item { gap:8px; padding:12px 12px; }
        .mp-kpi__body { min-height:110px; padding:16px; }
        .mp-kpi__value { font-size:1.35rem; }
    }
</style>

<div class="mp-page">
    <section class="mp-hero">
        <div class="mp-hero__eyebrow">Superadmin</div>
        <h1 class="mp-hero__title">Monitoramento estratégico de assinaturas</h1>
        <p class="mp-hero__text">
            Para 120 empresas, a melhor leitura é por prioridade operacional: o que está vencido, o que vence nos próximos dias e o que já está só em acompanhamento.
        </p>
        <div class="mp-hero__chips">
            <span class="mp-chip"><i class="fas fa-exclamation-triangle"></i> Cobrar agora</span>
            <span class="mp-chip"><i class="fas fa-clock"></i> Vence em breve</span>
            <span class="mp-chip"><i class="fab fa-whatsapp"></i> Mensagem pronta</span>
        </div>
    </section>

    <div class="row">
        <div class="col-md-3 col-6 mb-3">
            <div class="mp-kpi mp-kpi--red">
                <div class="mp-kpi__accent"></div>
                <div class="mp-kpi__body">
                    <div class="mp-kpi__icon"><i class="fas fa-exclamation-circle"></i></div>
                    <p class="mp-kpi__label">Vencidos</p>
                    <h3 class="mp-kpi__value"><?php echo e(number_format($totais['vencidos'] ?? 0, 0, ',', '.')); ?></h3>
                    <small class="mp-kpi__meta">Prioridade máxima de cobrança</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="mp-kpi mp-kpi--amber">
                <div class="mp-kpi__accent"></div>
                <div class="mp-kpi__body">
                    <div class="mp-kpi__icon"><i class="fas fa-hourglass-half"></i></div>
                    <p class="mp-kpi__label">Próximos <?php echo e($prazo); ?> dias</p>
                    <h3 class="mp-kpi__value"><?php echo e(number_format($totais['proximos'] ?? 0, 0, ',', '.')); ?></h3>
                    <small class="mp-kpi__meta">Ação preventiva antes de virar atraso</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="mp-kpi mp-kpi--blue">
                <div class="mp-kpi__accent"></div>
                <div class="mp-kpi__body">
                    <div class="mp-kpi__icon"><i class="fas fa-building"></i></div>
                    <p class="mp-kpi__label">Empresas com pendência</p>
                    <h3 class="mp-kpi__value"><?php echo e(number_format($totais['empresas'] ?? 0, 0, ',', '.')); ?></h3>
                    <small class="mp-kpi__meta">Base ativa acompanhada</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="mp-kpi mp-kpi--green">
                <div class="mp-kpi__accent"></div>
                <div class="mp-kpi__body">
                    <div class="mp-kpi__icon"><i class="fas fa-receipt"></i></div>
                    <p class="mp-kpi__label">Pagamentos em aberto</p>
                    <h3 class="mp-kpi__value"><?php echo e(number_format($totais['pendentes'] ?? 0, 0, ',', '.')); ?></h3>
                    <small class="mp-kpi__meta">Total de linhas monitoradas</small>
                </div>
            </div>
        </div>
    </div>

    <section class="mp-card">
        <div class="mp-card__header">
            <div>
                <h3 class="mp-title">Filtros de operação</h3>
                <p class="mp-sub">Use os filtros para focar somente no que precisa de ação imediata.</p>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <a href="<?php echo e(route('assinatura.monitoramento-pagamentos')); ?>" class="btn btn-outline-secondary btn-sm mp-btn">
                    <i class="fas fa-sync mr-1"></i> Atualizar
                </a>
            </div>
        </div>
        <div class="mp-card__body">
            <form method="GET" class="row">
                <div class="col-md-4 mb-3">
                    <label class="mp-label">Empresa</label>
                    <input type="text" name="empresa" value="<?php echo e($filtrosAtivos['empresa'] ?? request('empresa')); ?>" class="form-control mp-input" placeholder="Nome da empresa">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="mp-label">Plano</label>
                    <select name="plano_id" class="form-control mp-input">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = $planos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($plano->id); ?>" <?php echo e((string)($filtrosAtivos['plano_id'] ?? request('plano_id')) === (string) $plano->id ? 'selected' : ''); ?>>
                                <?php echo e($plano->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="mp-label">Prioridade</label>
                    <select name="status" class="form-control mp-input">
                        <option value="pendente" <?php echo e(($filtrosAtivos['status'] ?? request('status', 'pendente')) === 'pendente' ? 'selected' : ''); ?>>Pendentes</option>
                        <option value="vencido" <?php echo e(($filtrosAtivos['status'] ?? request('status')) === 'vencido' ? 'selected' : ''); ?>>Vencidos</option>
                        <option value="proximo" <?php echo e(($filtrosAtivos['status'] ?? request('status')) === 'proximo' ? 'selected' : ''); ?>>Próximos</option>
                    </select>
                </div>
                <div class="col-md-1 mb-3">
                    <label class="mp-label">Dias</label>
                    <select name="prazo" class="form-control mp-input">
                        <?php $__currentLoopData = $proximosDias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item); ?>" <?php echo e((int)($filtrosAtivos['prazo'] ?? request('prazo', $prazo)) === $item ? 'selected' : ''); ?>>
                                <?php echo e($item); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex" style="gap:8px;">
                    <button type="submit" class="btn btn-primary mp-btn flex-grow-1"><i class="fas fa-filter mr-1"></i> Filtrar</button>
                    <a href="<?php echo e(route('assinatura.monitoramento-pagamentos')); ?>" class="btn btn-outline-secondary mp-btn">Limpar</a>
                </div>
            </form>
        </div>
    </section>

    <div class="mp-sections">
        <section class="mp-card">
            <div class="mp-card__header">
                <div>
                    <h3 class="mp-title">Fila de cobrança imediata</h3>
                    <p class="mp-sub">Itens vencidos ou com urgência de até 3 dias.</p>
                </div>
                <span class="badge badge-danger"><?php echo e($listaVencidos->count()); ?></span>
            </div>
            <div class="mp-card__body">
            <div class="mp-list mp-scroll">
                    <?php $__empty_1 = true; $__currentLoopData = $listaVencidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $empresa = $pagamento->empresa;
                            $assinatura = $pagamento->empresaPlano;
                            $dias = now()->startOfDay()->diffInDays($pagamento->data_vencimento, false);
                            $wa = $waLink($empresa, $pagamento);
                            [$label, $color, $icon] = $statusLabel($dias);
                        ?>
                        <div class="mp-item">
                            <div>
                                <div class="mp-company"><?php echo e($empresa?->nome ?? 'Empresa não informada'); ?></div>
                                <div class="mp-muted"><?php echo e($assinatura?->plano?->nome ?? 'Plano'); ?> · Assinatura #<?php echo e($assinatura?->id ?? '-'); ?></div>
                            </div>
                            <div>
                                <div class="mp-muted">Vencimento</div>
                                <div class="font-weight-bold"><?php echo e($pagamento->data_vencimento->format('d/m/Y')); ?></div>
                            </div>
                            <div>
                                <div class="mp-muted">Valor</div>
                                <div class="font-weight-bold">R$ <?php echo e(number_format((float) $pagamento->valor, 2, ',', '.')); ?></div>
                            </div>
                            <div>
                                <span class="mp-pill mp-pill--danger"><i class="<?php echo e($icon); ?> mr-1"></i><?php echo e($label); ?> · <?php echo e(abs($dias)); ?> dia(s)</span>
                            </div>
                            <div class="text-right">
                                <div class="d-flex justify-content-end" style="gap:8px; flex-wrap:wrap;">
                                    <?php if($wa): ?>
                                        <a href="<?php echo e($wa); ?>" target="_blank" rel="noopener" class="btn btn-success btn-sm mp-btn">
                                            <i class="fab fa-whatsapp mr-1"></i> Cobrar
                                        </a>
                                    <?php endif; ?>
                                    <?php if($assinatura): ?>
                                        <a href="<?php echo e(route('assinatura.show', $assinatura)); ?>" class="btn btn-outline-secondary btn-sm mp-btn">
                                            <i class="fas fa-eye mr-1"></i> Abrir
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center text-muted py-4">Nenhum pagamento urgente encontrado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="mp-grid">
            <section class="mp-card">
                <div class="mp-card__header">
                    <div>
                        <h3 class="mp-title">Próximos do vencimento</h3>
                        <p class="mp-sub">Cobrança preventiva para evitar atraso.</p>
                    </div>
                    <span class="badge badge-warning"><?php echo e($listaProximos->count()); ?></span>
                </div>
                <div class="mp-card__body">
                    <div class="mp-list mp-scroll">
                        <?php $__empty_1 = true; $__currentLoopData = $listaProximos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $empresa = $pagamento->empresa;
                                $assinatura = $pagamento->empresaPlano;
                                $dias = now()->startOfDay()->diffInDays($pagamento->data_vencimento, false);
                                $wa = $waLink($empresa, $pagamento);
                            ?>
                            <div class="mp-item">
                                <div>
                                    <div class="mp-company"><?php echo e($empresa?->nome ?? 'Empresa não informada'); ?></div>
                                    <div class="mp-muted"><?php echo e($assinatura?->plano?->nome ?? 'Plano'); ?></div>
                                </div>
                                <div>
                                    <div class="mp-muted">Vencimento</div>
                                    <div class="font-weight-bold"><?php echo e($pagamento->data_vencimento->format('d/m/Y')); ?></div>
                                </div>
                                <div>
                                    <div class="mp-muted">Valor</div>
                                    <div class="font-weight-bold">R$ <?php echo e(number_format((float) $pagamento->valor, 2, ',', '.')); ?></div>
                                </div>
                                <div>
                                    <span class="mp-pill mp-pill--warn"><i class="fas fa-clock mr-1"></i><?php echo e($dias); ?> dia(s)</span>
                                </div>
                                <div class="text-right">
                                    <?php if($wa): ?>
                                        <a href="<?php echo e($wa); ?>" target="_blank" rel="noopener" class="btn btn-success btn-sm mp-btn">
                                            <i class="fab fa-whatsapp mr-1"></i> Cobrar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center text-muted py-4">Nenhum pagamento próximo encontrado.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="mp-card">
                <div class="mp-card__header">
                    <div>
                        <h3 class="mp-title">Carteira em acompanhamento</h3>
                        <p class="mp-sub">Pagamentos ainda dentro do ciclo, mas que precisam ser observados.</p>
                    </div>
                    <span class="badge badge-secondary"><?php echo e($listaAcompanhamento->count()); ?></span>
                </div>
                <div class="mp-card__body">
                    <div class="mp-list mp-scroll">
                        <?php $__empty_1 = true; $__currentLoopData = $listaAcompanhamento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $empresa = $pagamento->empresa;
                                $assinatura = $pagamento->empresaPlano;
                                $wa = $waLink($empresa, $pagamento);
                            ?>
                            <div class="mp-item">
                                <div>
                                    <div class="mp-company"><?php echo e($empresa?->nome ?? 'Empresa não informada'); ?></div>
                                    <div class="mp-muted"><?php echo e($assinatura?->plano?->nome ?? 'Plano'); ?></div>
                                </div>
                                <div>
                                    <div class="mp-muted">Vencimento</div>
                                    <div class="font-weight-bold"><?php echo e($pagamento->data_vencimento->format('d/m/Y')); ?></div>
                                </div>
                                <div>
                                    <div class="mp-muted">Valor</div>
                                    <div class="font-weight-bold">R$ <?php echo e(number_format((float) $pagamento->valor, 2, ',', '.')); ?></div>
                                </div>
                                <div>
                                    <span class="mp-pill mp-pill--muted"><i class="fas fa-clipboard-check mr-1"></i> Acompanhar</span>
                                </div>
                                <div class="text-right">
                                    <?php if($wa): ?>
                                        <a href="<?php echo e($wa); ?>" target="_blank" rel="noopener" class="btn btn-success btn-sm mp-btn">
                                            <i class="fab fa-whatsapp mr-1"></i> Avisar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center text-muted py-4">Nenhum pagamento em acompanhamento.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\assinatura\monitoramento-pagamentos.blade.php ENDPATH**/ ?>