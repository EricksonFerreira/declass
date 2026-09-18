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
        $moeda = fn ($valor) => 'R$ '.number_format((float) $valor, 2, ',', '.');
        $periodoAtual = now()->translatedFormat('F \d\e Y');
        $mesesEvolucao = (int) ($filtros['meses'] ?? 6);
    ?>

    <style>
        .sf-dashboard { display:grid; gap:20px; }
        .sf-hero {
            border-radius:24px; padding:24px; color:#fff;
            background:
                radial-gradient(circle at top right, rgba(56,189,248,.18), transparent 24%),
                radial-gradient(circle at bottom left, rgba(16,185,129,.15), transparent 20%),
                linear-gradient(135deg, #111827 0%, #1d4ed8 52%, #2563eb 100%);
            box-shadow:0 24px 60px rgba(15,23,42,.18);
        }
        .sf-hero__eyebrow { font-size:.72rem; letter-spacing:.18em; text-transform:uppercase; font-weight:900; opacity:.75; }
        .sf-hero__title { margin:.35rem 0 .4rem; font-size:1.75rem; font-weight:900; letter-spacing:-.03em; }
        .sf-hero__text { margin:0; max-width:72rem; color:rgba(255,255,255,.86); line-height:1.6; }
        .sf-hero__chips { display:flex; flex-wrap:wrap; gap:10px; margin-top:16px; }
        .sf-chip {
            display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px;
            background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.14); font-size:.8rem; font-weight:800;
        }
        .sf-kpi {
            border:1px solid #dbe4f0; border-radius:18px; background:#fff; box-shadow:0 14px 36px rgba(15,23,42,.06); overflow:hidden; height:100%;
        }
        .sf-kpi__accent { height:4px; }
        .sf-kpi__body { padding:18px; position:relative; min-height:132px; }
        .sf-kpi__label { margin:0 0 10px; color:#64748b; font-size:.76rem; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
        .sf-kpi__value { margin:0; font-size:1.7rem; font-weight:900; letter-spacing:-.03em; }
        .sf-kpi__meta { display:block; margin-top:8px; color:#64748b; font-size:.82rem; }
        .sf-kpi__icon { position:absolute; right:18px; top:18px; font-size:2.1rem; opacity:.12; }
        .sf-kpi--green .sf-kpi__accent { background:linear-gradient(90deg, #059669, #34d399); }
        .sf-kpi--blue .sf-kpi__accent { background:linear-gradient(90deg, #2563eb, #60a5fa); }
        .sf-kpi--amber .sf-kpi__accent { background:linear-gradient(90deg, #d97706, #fbbf24); }
        .sf-kpi--red .sf-kpi__accent { background:linear-gradient(90deg, #dc2626, #fb7185); }
        .sf-card {
            border:1px solid #dbe4f0; border-radius:18px; background:#fff; box-shadow:0 14px 36px rgba(15,23,42,.06); overflow:hidden;
        }
        .sf-card__header {
            display:flex; align-items:center; justify-content:space-between; gap:12px;
            padding:16px 18px; border-bottom:1px solid #e8eef6; background:linear-gradient(180deg, #fff 0%, #f8fbff 100%);
        }
        .sf-card__body { padding:18px; }
        .sf-title { margin:0; font-size:1rem; font-weight:900; color:#0f172a; }
        .sf-sub { margin:4px 0 0; font-size:.82rem; color:#64748b; }
        .sf-label { display:inline-block; margin-bottom:.4rem; font-size:.72rem; font-weight:900; letter-spacing:.12em; text-transform:uppercase; color:#64748b; }
        .sf-input { height:40px; border-radius:12px; border-color:#d7e0ea; box-shadow:none; }
        .sf-input:focus { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.12); }
        .sf-btn { min-height:40px; border-radius:12px; font-weight:800; }
        .sf-grid { display:grid; gap:14px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .sf-table-wrap { overflow:auto; }
        .sf-table thead th { background:#f8fbff; border-bottom:1px solid #e8eef6; font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:#64748b; }
        .sf-table td, .sf-table th { vertical-align:middle; }
        .sf-pill { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; font-size:.74rem; font-weight:800; }
        .sf-pill--success { background:#ecfdf5; color:#047857; }
        .sf-pill--warn { background:#fff7ed; color:#9a3412; }
        .sf-pill--danger { background:#fef2f2; color:#b91c1c; }
        .sf-pill--muted { background:#f1f5f9; color:#475569; }
        .sf-chart {
            height: 260px; display:grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap:14px; align-items:end;
        }
        .sf-bar { display:flex; flex-direction:column; align-items:stretch; gap:8px; height:100%; }
        .sf-bar__track {
            position:relative; min-height:160px; border-radius:16px; background:linear-gradient(180deg, #eff6ff, #f8fbff); border:1px solid #dbe4f0; overflow:hidden;
        }
        .sf-bar__fill {
            position:absolute; left:0; right:0; bottom:0; border-radius:16px 16px 0 0;
            background:linear-gradient(180deg, #2563eb, #38bdf8);
        }
        .sf-bar__label { font-size:.76rem; color:#64748b; font-weight:800; text-align:center; }
        .sf-bar__value { font-size:.8rem; font-weight:900; text-align:center; color:#0f172a; }
        .sf-list { display:grid; gap:10px; }
        .sf-list__item {
            display:grid; grid-template-columns: 1.6fr .8fr .8fr .7fr; gap:10px; align-items:center;
            padding:12px 14px; border:1px solid #e8eef6; border-radius:14px;
        }
        .sf-list__item:hover { background:#f8fbff; }
        .sf-muted { color:#64748b; font-size:.82rem; }
        .sf-scroll { max-height: 420px; overflow-y:auto; overflow-x:hidden; padding-right:6px; }
        @media (max-width: 1199px) {
            .sf-grid { grid-template-columns: 1fr; }
            .sf-chart { grid-template-columns: repeat(3, minmax(0, 1fr)); height:auto; }
            .sf-list__item { grid-template-columns: 1fr; }
            .sf-scroll { max-height: 360px; }
        }
        @media (max-width: 767.98px) {
            .sf-hero { padding:18px; border-radius:18px; }
            .sf-hero__title { font-size:1.3rem; }
            .sf-card__header { padding:14px 16px; flex-direction:column; align-items:flex-start; }
            .sf-card__body { padding:14px; }
            .sf-kpi__body { min-height:112px; padding:16px; }
            .sf-kpi__value { font-size:1.4rem; }
            .sf-chart { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>

    <div class="sf-dashboard">
        <section class="sf-hero">
            <div class="sf-hero__eyebrow">Visão do dono</div>
            <h1 class="sf-hero__title">Finanças das assinaturas com leitura de caixa e previsão mensal</h1>
            <p class="sf-hero__text">
                Aqui você vê quanto já entrou, quanto deve entrar até o fim do mês, o que está em atraso e quais empresas/plano estão puxando a receita.
                O foco é decisão rápida: cobrar, renovar, renegociar ou proteger o caixa.
            </p>
            <div class="sf-hero__chips">
                <span class="sf-chip"><i class="fas fa-wallet"></i> Receita realizada</span>
                <span class="sf-chip"><i class="fas fa-chart-line"></i> Previsão do mês</span>
                <span class="sf-chip"><i class="fas fa-bell"></i> Inadimplência</span>
                <span class="sf-chip"><i class="fas fa-filter"></i> Filtros executivos</span>
            </div>
        </section>

        <div class="row">
            <div class="col-md-3 col-6 mb-3">
                <div class="sf-kpi sf-kpi--green">
                    <div class="sf-kpi__accent"></div>
                    <div class="sf-kpi__body">
                        <div class="sf-kpi__icon"><i class="fas fa-check-circle"></i></div>
                        <p class="sf-kpi__label">Receita realizada</p>
                        <h3 class="sf-kpi__value"><?php echo e($moeda($receitaRealizadaMes)); ?></h3>
                        <small class="sf-kpi__meta">Pagamentos confirmados em <?php echo e($periodoAtual); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="sf-kpi sf-kpi--blue">
                    <div class="sf-kpi__accent"></div>
                    <div class="sf-kpi__body">
                        <div class="sf-kpi__icon"><i class="fas fa-calendar-check"></i></div>
                        <p class="sf-kpi__label">Previsão do mês</p>
                        <h3 class="sf-kpi__value"><?php echo e($moeda($receitaPrevistaMes)); ?></h3>
                        <small class="sf-kpi__meta">Baseado em vencimentos do mês atual</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="sf-kpi sf-kpi--amber">
                    <div class="sf-kpi__accent"></div>
                    <div class="sf-kpi__body">
                        <div class="sf-kpi__icon"><i class="fas fa-hourglass-half"></i></div>
                        <p class="sf-kpi__label">Próximo mês</p>
                        <h3 class="sf-kpi__value"><?php echo e($moeda($receitaPrevistaProximoMes)); ?></h3>
                        <small class="sf-kpi__meta">Recebimento já mapeado para o caixa futuro</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="sf-kpi sf-kpi--red">
                    <div class="sf-kpi__accent"></div>
                    <div class="sf-kpi__body">
                        <div class="sf-kpi__icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <p class="sf-kpi__label">Em atraso</p>
                        <h3 class="sf-kpi__value"><?php echo e($moeda($receitaEmAtraso)); ?></h3>
                        <small class="sf-kpi__meta">Valor que pede cobrança prioritária</small>
                    </div>
                </div>
            </div>
        </div>

        <section class="sf-card">
            <div class="sf-card__header">
                <div>
                    <h3 class="sf-title">Filtros de análise</h3>
                    <p class="sf-sub">Use os filtros para enxergar a receita por empresa, plano, status e meio de pagamento.</p>
                </div>
                <div>
                    <a href="<?php echo e(route('assinatura.financas')); ?>" class="btn btn-outline-secondary btn-sm sf-btn">
                        <i class="fas fa-sync mr-1"></i> Limpar
                    </a>
                </div>
            </div>
            <div class="sf-card__body">
                <form method="GET" action="<?php echo e(route('assinatura.financas')); ?>" class="row">
                    <div class="col-md-3 mb-3">
                        <label class="sf-label">Empresa</label>
                        <input type="text" name="empresa" value="<?php echo e($filtros['empresa']); ?>" class="form-control sf-input" placeholder="Nome da empresa">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="sf-label">Plano</label>
                        <select name="plano_id" class="form-control sf-input">
                            <option value="">Todos</option>
                            <?php $__currentLoopData = $planos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($plano->id); ?>" <?php echo e((string) $filtros['plano_id'] === (string) $plano->id ? 'selected' : ''); ?>>
                                    <?php echo e($plano->nome); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="sf-label">Status</label>
                        <select name="status" class="form-control sf-input">
                            <option value="">Todos</option>
                            <?php $__currentLoopData = $statusList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e($filtros['status'] === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="sf-label">Método</label>
                        <select name="metodo" class="form-control sf-input">
                            <option value="">Todos</option>
                            <?php $__currentLoopData = $metodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e($filtros['metodo'] === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="sf-label">Periodicidade</label>
                        <select name="periodicidade" class="form-control sf-input">
                            <option value="">Todas</option>
                            <?php $__currentLoopData = $periodicidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e($filtros['periodicidade'] === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary sf-btn w-100"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </section>

        <div class="row">
            <div class="col-lg-8 mb-3">
                <section class="sf-card h-100">
                    <div class="sf-card__header">
                        <div>
                            <h3 class="sf-title">Evolução da receita paga</h3>
                            <p class="sf-sub">Últimos <?php echo e($mesesEvolucao); ?> meses com base em pagamentos já confirmados.</p>
                        </div>
                    </div>
                    <div class="sf-card__body">
                        <form method="GET" action="<?php echo e(route('assinatura.financas')); ?>" class="row align-items-end mb-3">
                            <input type="hidden" name="empresa" value="<?php echo e($filtros['empresa']); ?>">
                            <input type="hidden" name="plano_id" value="<?php echo e($filtros['plano_id']); ?>">
                            <input type="hidden" name="status" value="<?php echo e($filtros['status']); ?>">
                            <input type="hidden" name="metodo" value="<?php echo e($filtros['metodo']); ?>">
                            <input type="hidden" name="periodicidade" value="<?php echo e($filtros['periodicidade']); ?>">
                            <div class="col-md-4 mb-3">
                                <label class="sf-label">Meses da evolução</label>
                                <select name="meses" class="form-control sf-input">
                                    <?php for($i = 1; $i <= 24; $i++): ?>
                                        <option value="<?php echo e($i); ?>" <?php echo e($mesesEvolucao === $i ? 'selected' : ''); ?>>
                                            <?php echo e($i); ?> <?php echo e($i === 1 ? 'mês' : 'meses'); ?>

                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="submit" class="btn btn-primary sf-btn">Atualizar evolução</button>
                            </div>
                        </form>
                        <div class="sf-chart">
                            <?php $max = max($valoresMensais ?: [1]); ?>
                            <?php $__currentLoopData = $labelsMensais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $valor = (float) ($valoresMensais[$i] ?? 0); ?>
                                <div class="sf-bar">
                                    <div class="sf-bar__track">
                                        <div class="sf-bar__fill" style="height: <?php echo e($max > 0 ? max(8, ($valor / $max) * 100) : 8); ?>%;"></div>
                                    </div>
                                    <div class="sf-bar__value"><?php echo e($moeda($valor)); ?></div>
                                    <div class="sf-bar__label"><?php echo e($label); ?></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-lg-4 mb-3">
                <section class="sf-card h-100">
                    <div class="sf-card__header">
                        <div>
                            <h3 class="sf-title">Leitura executiva</h3>
                            <p class="sf-sub">O que merece atenção agora.</p>
                        </div>
                    </div>
                    <div class="sf-card__body">
                        <div class="sf-list">
                            <div class="sf-list__item">
                                <div>
                                    <strong>Pagamentos do mês</strong>
                                    <div class="sf-muted"><?php echo e($pagamentosMes->count()); ?> lançamentos previstos</div>
                                </div>
                                <span class="sf-pill sf-pill--muted">Mês atual</span>
                                <span class="sf-muted">Realizado + previsto</span>
                                <span class="sf-muted"><?php echo e($moeda($receitaPrevistaMes)); ?></span>
                            </div>
                            <div class="sf-list__item">
                                <div>
                                    <strong>Carteira futura</strong>
                                    <div class="sf-muted"><?php echo e($pagamentosProximoMes->count()); ?> lançamentos no próximo mês</div>
                                </div>
                                <span class="sf-pill sf-pill--warn">Projeção</span>
                                <span class="sf-muted">Receita futura</span>
                                <span class="sf-muted"><?php echo e($moeda($receitaPrevistaProximoMes)); ?></span>
                            </div>
                            <div class="sf-list__item">
                                <div>
                                    <strong>Recebível em aberto</strong>
                                    <div class="sf-muted">Pendências que podem virar caixa até o vencimento.</div>
                                </div>
                                <span class="sf-pill sf-pill--danger">Atenção</span>
                                <span class="sf-muted">Em atraso</span>
                                <span class="sf-muted"><?php echo e($moeda($receitaEmAtraso)); ?></span>
                            </div>
                            <div class="sf-list__item">
                                <div>
                                    <strong>Potencial de cobrança</strong>
                                    <div class="sf-muted">Valores pendentes com vencimento a partir de hoje.</div>
                                </div>
                                <span class="sf-pill sf-pill--success">Oportunidade</span>
                                <span class="sf-muted">Receita</span>
                                <span class="sf-muted"><?php echo e($moeda($futuroRecebivel)); ?></span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <section class="sf-card">
            <div class="sf-card__header">
                <div>
                    <h3 class="sf-title">Assinaturas pagas e previsão operacional</h3>
                    <p class="sf-sub">Lista para decidir quem está pago, quem está pendente e qual valor ainda pode entrar no caixa.</p>
                </div>
                <span class="badge badge-primary"><?php echo e($pagamentosRecentes->total()); ?></span>
            </div>
            <div class="sf-card__body">
                <div class="sf-table-wrap">
                    <table class="table table-hover mb-0 sf-table">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Plano</th>
                                <th>Vencimento</th>
                                <th>Pago em</th>
                                <th>Método</th>
                                <th>Status</th>
                                <th class="text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $pagamentosRecentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($pagamento->empresa?->nome ?? 'Empresa'); ?></strong><br>
                                        <span class="sf-muted">#<?php echo e($pagamento->empresa_id); ?></span>
                                    </td>
                                    <td>
                                        <?php echo e($pagamento->empresaPlano?->plano?->nome ?? 'Plano'); ?><br>
                                        <span class="sf-muted"><?php echo e($pagamento->periodicidade_texto ?? ucfirst($pagamento->periodicidade ?? 'mensal')); ?></span>
                                    </td>
                                    <td><?php echo e(optional($pagamento->data_vencimento)->format('d/m/Y')); ?></td>
                                    <td><?php echo e(optional($pagamento->pago_em)->format('d/m/Y H:i') ?: '-'); ?></td>
                                    <td><?php echo e($pagamento->metodo_texto); ?></td>
                                    <td>
                                        <?php ($badge = $pagamento->status_badge); ?>
                                        <span class="badge <?php echo e($badge['class']); ?>"><?php echo e($badge['label']); ?></span>
                                    </td>
                                    <td class="text-right"><strong><?php echo e($moeda($pagamento->valor)); ?></strong></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="text-center text-muted py-4">
                                            Nenhuma assinatura encontrada com os filtros atuais.
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if($pagamentosRecentes->hasPages()): ?>
                <div class="card-footer py-2">
                    <?php echo e($pagamentosRecentes->links()); ?>

                </div>
            <?php endif; ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\assinatura\financas.blade.php ENDPATH**/ ?>