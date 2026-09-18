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
    <?php if (isset($component)) { $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4 = $attributes; } ?>
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PageTitle::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4)): ?>
<?php $attributes = $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4; ?>
<?php unset($__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ff7b3043c692896c2a650ccae21c5e4)): ?>
<?php $component = $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4; ?>
<?php unset($__componentOriginal2ff7b3043c692896c2a650ccae21c5e4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<!-- Incluir Select2 CSS e Tema Bootstrap 4 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }
    
    /* Clockify-like Tracker Bar */
    .tracker-bar-card {
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        border: none;
        border-left: 5px solid #28a745;
        transition: all 0.3s ease;
    }
    .tracker-bar-card.active-running {
        border-left-color: #dc3545;
        animation: pulse-border 2s infinite;
    }
    @keyframes pulse-border {
        0% { border-left-color: #dc3545; }
        50% { border-left-color: #ffc107; }
        100% { border-left-color: #dc3545; }
    }
    
    .tracker-input-desc {
        font-size: 16px;
        font-weight: 500;
        color: #1e293b;
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding-left: 0;
    }
    .tracker-input-desc::placeholder {
        color: #94a3b8;
    }
    
    /* Clean inputs inside past history table */
    .inline-tracker-input {
        background: transparent !important;
        border: 1px solid transparent !important;
        box-shadow: none !important;
        transition: all 0.2s ease;
        font-weight: 600;
        color: #1e293b;
        border-radius: 6px;
        font-size: 14px;
    }
    .inline-tracker-input:focus, .inline-tracker-input:hover {
        background: #fff !important;
        border-color: #cbd5e1 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }

    .inline-tracker-select {
        border: 1px solid transparent !important;
        background-color: transparent !important;
        font-weight: 700;
        cursor: pointer;
        border-radius: 6px;
        font-size: 13px;
        color: #475569;
        padding: 4px 8px;
    }
    .inline-tracker-select:focus, .inline-tracker-select:hover {
        background-color: #fff !important;
        border-color: #cbd5e1 !important;
    }

    /* Minimalist Select2 for inline table tags */
    .select2-container--bootstrap4 .select2-selection--multiple {
        background-color: transparent !important;
        border: 1px solid transparent !important;
        min-height: 32px;
    }
    .select2-container--bootstrap4.select2-container--focus .select2-selection--multiple,
    .select2-container--bootstrap4:hover .select2-selection--multiple {
        background-color: #fff !important;
        border-color: #cbd5e1 !important;
    }
    .select2-container--bootstrap4 .select2-selection__choice {
        background-color: #e2e8f0 !important;
        border: none !important;
        color: #334155 !important;
        font-weight: 600;
        font-size: 11px;
        border-radius: 4px;
        padding: 2px 6px !important;
    }
    
    /* Saving indicators */
    .saving-indicator {
        font-size: 12px;
        font-weight: 700;
        color: #0284c7;
        display: none;
        align-items: center;
        gap: 6px;
        background: #e0f2fe;
        padding: 6px 12px;
        border-radius: 20px;
    }

    /* History table design */
    .history-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
    }
    .history-table th {
        background-color: #f8fafc;
        color: #64748b;
        text-transform: uppercase;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #edf2f7;
    }
    .history-table td {
        vertical-align: middle !important;
        padding: 10px 12px !important;
    }

    /* Duration tag styling */
    .duration-badge {
        font-family: monospace;
        font-size: 14px;
        background-color: #f1f5f9;
        color: #334155;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 700;
        display: inline-block;
    }
    .duration-badge.running {
        background-color: #ecfdf5;
        color: #059669;
        animation: pulse-bg 1.5s infinite;
    }
    @keyframes pulse-bg {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }

    /* Responsividade da Tabela */
    @media (max-width: 991.98px) {
        .responsive-tracker-table, 
        .responsive-tracker-table thead, 
        .responsive-tracker-table tbody, 
        .responsive-tracker-table th, 
        .responsive-tracker-table td, 
        .responsive-tracker-table tr { 
            display: block; 
        }
        
        .responsive-tracker-table thead tr { 
            position: absolute;
            top: -9999px;
            left: -9999px;
        }
        
        .responsive-tracker-table tr { 
            border: 1px solid #e2e8f0; 
            margin-bottom: 15px;
            border-radius: 10px;
            background: #fff;
            padding: 10px 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        
        .responsive-tracker-table td { 
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important; 
            position: relative;
            padding: 8px 0 8px 30% !important; 
            text-align: left !important;
        }

        .responsive-tracker-table td:last-child {
            border-bottom: none !important;
            padding-bottom: 0 !important;
        }
        
        .responsive-tracker-table td::before { 
            position: absolute;
            top: 50%;
            left: 0;
            width: 25%; 
            padding-right: 10px; 
            white-space: nowrap;
            transform: translateY(-50%);
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            content: attr(data-label);
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <!-- Bloco de Timer (Ativo ou Novo) -->
    <div class="card shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <?php if($activeTracker): ?>
                <!-- Rastreador Ativo em Execução -->
                <form action="<?php echo e(route('clockify.tracker.stop')); ?>" method="POST" id="stop-tracker-form">
                    <?php echo csrf_field(); ?>
                    <div class="row align-items-center text-center text-md-left">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <input type="text" name="descricao" id="active-descricao" class="form-control form-control-lg border-0 shadow-none" 
                                placeholder="Em que você está trabalhando?" value="<?php echo e($activeTracker->descricao); ?>">
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <select name="clockify_projeto_id" id="active-projeto" class="form-control form-control-sm select2" style="width: 100%;">
                                <option value="">Sem Projeto</option>
                                <?php $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($proj->id); ?>" <?php if($activeTracker->clockify_projeto_id == $proj->id): echo 'selected'; endif; ?>><?php echo e($proj->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <select name="tags[]" id="active-tags" class="form-control form-control-sm select2" multiple data-placeholder="Tags...">
                                <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tag->id); ?>" <?php if($activeTracker->tags->contains($tag->id)): echo 'selected'; endif; ?>><?php echo e($tag->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 text-md-right mb-2 mb-md-0">
                            <h2 class="text-bold mb-0 text-success" id="live-timer" data-start="<?php echo e($activeTracker->inicio->toIso8601String()); ?>">
                                00:00:00
                            </h2>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-danger btn-block btn-lg font-weight-bold">
                                <i class="fas fa-stop mr-1"></i> PARAR
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <!-- Iniciar Novo Rastreador -->
                <form action="<?php echo e(route('clockify.tracker.start')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="row align-items-center text-center text-md-left">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <input type="text" name="descricao" class="form-control form-control-lg border-0 shadow-none" 
                                placeholder="Em que você está trabalhando?">
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <select name="clockify_projeto_id" class="form-control form-control-sm select2" style="width: 100%;">
                                <option value="">Selecione o Projeto...</option>
                                <?php $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($proj->id); ?>"><?php echo e($proj->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <select name="tags[]" class="form-control form-control-sm select2" multiple data-placeholder="Tags...">
                                <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tag->id); ?>"><?php echo e($tag->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold">
                                <i class="fas fa-play mr-1"></i> INICIAR
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toggle para Registro Manual -->
    <div class="card collapsed-card mb-4 shadow-sm" style="border-radius: 12px; border: none;">
        <div class="card-header bg-white py-2" data-card-widget="collapse" style="cursor: pointer; border-radius: 12px;">
            <h3 class="card-title text-sm text-bold text-muted mb-0">
                <i class="fas fa-keyboard mr-2 text-primary"></i> Adicionar Registro Manualmente
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool text-primary">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body bg-white" style="border-top: 1px solid #f1f5f9;">
            <form action="<?php echo e(route('clockify.tracker.manual')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="form-group mb-0">
                            <label class="text-xs text-muted text-bold">Descrição <span class="text-danger">*</span></label>
                            <input type="text" name="descricao" class="form-control form-control-sm" placeholder="Ex: Ajustes de CSS" required>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="form-group mb-0">
                            <label class="text-xs text-muted text-bold">Projeto <span class="text-danger">*</span></label>
                            <select name="clockify_projeto_id" class="form-control form-control-sm select2" required>
                                <option value="">Selecione o projeto</option>
                                <?php $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($proj->id); ?>"><?php echo e($proj->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3 mb-md-0">
                        <div class="form-group mb-0">
                            <label class="text-xs text-muted text-bold">Início</label>
                            <input type="datetime-local" name="inicio" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3 mb-md-0">
                        <div class="form-group mb-0">
                            <label class="text-xs text-muted text-bold">Fim</label>
                            <input type="datetime-local" name="fim" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group mb-0">
                            <label class="d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-sm btn-primary btn-block font-weight-bold" style="margin-top: 2px;">Salvar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Histórico de Registros de Tempo -->
    <div class="card history-card">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h3 class="card-title text-bold text-dark mb-0"><i class="fas fa-history text-secondary mr-2"></i> Histórico de Tempo</h3>
            <!-- Filter Form -->
            <form method="GET" action="<?php echo e(route('clockify.tracker.index')); ?>" class="row g-3 align-items-end" style="flex-wrap: nowrap; margin: 0;">
                <div class="col-md-3">
                    <label class="form-label text-xs text-muted">Projeto</label>
                    <select name="project_id" class="form-control form-control-sm select2" style="width:100%">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($proj->id); ?>" <?php if(request('project_id') == $proj->id): echo 'selected'; endif; ?>><?php echo e($proj->nome); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-xs text-muted">Tags</label>
                    <select name="tags[]" class="form-control form-control-sm select2" multiple style="width:100%" data-placeholder="Tags...">
                        <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($tag->id); ?>" <?php if(in_array($tag->id, request('tags', []))): echo 'selected'; endif; ?>><?php echo e($tag->nome); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-xs text-muted">De</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo e(request('start_date')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-xs text-muted">Até</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo e(request('end_date')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-xs text-muted">Palavra‑chave</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-12 d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-primary btn-sm me-2"><i class="fas fa-search me-1"></i>Buscar</button>
                    <a href="<?php echo e(route('clockify.tracker.index')); ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-sync me-1"></i>Reset</a>
                </div>
            </form>
            <div class="saving-indicator" id="global-saving-indicator">
                <i class="fas fa-sync fa-spin"></i> Salvando...
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm mb-0 responsive-tracker-table history-table">
                    <thead>
                        <tr>
                            <th class="pl-3">Descrição</th>
                            <th>Projeto</th>
                            <th>Tags</th>
                            <th class="text-center" style="width: 230px;">Período</th>
                            <th class="text-center" style="width: 120px;">Duração</th>
                            <th style="width: 100px;" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $pastTrackers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tracker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr data-tracker-id="<?php echo e($tracker->id); ?>">
                                <td class="align-middle p-1 pl-3" data-label="Descrição">
                                    <input type="text" class="form-control form-control-sm inline-tracker-input tracker-inline-desc" 
                                           data-id="<?php echo e($tracker->id); ?>" value="<?php echo e($tracker->descricao); ?>" placeholder="Sem descrição">
                                </td>
                                <td class="align-middle p-1" data-label="Projeto">
                                    <select class="form-control form-control-sm inline-tracker-select tracker-inline-project" data-id="<?php echo e($tracker->id); ?>" style="min-width: 150px;">
                                        <option value="">Sem Projeto</option>
                                        <?php $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($proj->id); ?>" <?php if($tracker->clockify_projeto_id == $proj->id): echo 'selected'; endif; ?>><?php echo e($proj->nome); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td class="align-middle p-1" data-label="Tags" style="min-width: 180px;">
                                    <select class="form-control form-control-sm select2 tracker-inline-tags" multiple data-id="<?php echo e($tracker->id); ?>" data-placeholder="Adicionar tags...">
                                        <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($tag->id); ?>" <?php if($tracker->tags->contains($tag->id)): echo 'selected'; endif; ?>><?php echo e($tag->nome); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td class="text-center align-middle text-sm text-muted" data-label="Período">
                                    <i class="far fa-calendar-alt mr-1 text-xs"></i> <?php echo e($tracker->inicio->format('d/m/Y H:i')); ?> às <?php echo e($tracker->fim ? $tracker->fim->format('H:i') : '-'); ?>

                                </td>
                                <td class="text-center align-middle" data-label="Duração">
                                    <span class="duration-badge <?php echo e(!$tracker->fim ? 'running' : ''); ?>">
                                        <?php echo e($tracker->formatted_duration); ?>

                                    </span>
                                </td>
                                <td class="align-middle text-center" data-label="Ações">
                                    <form action="<?php echo e(route('clockify.tracker.destroy', $tracker->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir este registro de tempo?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-xs btn-outline-danger border-0"><i class="fa fa-trash"></i> Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="far fa-clock fa-2x mb-3 text-muted"></i><br>
                                    Nenhum registro de tempo encontrado. Comece a rastrear agora!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix bg-white border-top">
            <?php echo e($pastTrackers->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const savingIndicator = document.getElementById('global-saving-indicator');

            // Inicialização do Select2 com visual Bootstrap 4 e coerência visual
            function initSelect2() {
                if ($.fn.select2) {
                    $('.select2').select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownAutoWidth: true
                    });
                }
            }

            initSelect2();

            // Autocomplete update function for past trackers
            var trackerUpdateUrlTemplate = "<?php echo e(route('clockify.tracker.update', ['id' => '__ID__'])); ?>";

            function updatePastTrackerInline(trackerId, dataObj) {
                savingIndicator.style.display = 'flex';

                $.ajax({
                    url: trackerUpdateUrlTemplate.replace('__ID__', trackerId),
                    type: "POST",
                    data: Object.assign({
                        _token: "<?php echo e(csrf_token()); ?>",
                        _method: "PUT"
                    }, dataObj),
                    success: function() {
                        console.log(`Tracker ${trackerId} updated successfully`);
                    },
                    error: function(err) {
                        console.error('Error updating past tracker:', err);
                    },
                    complete: function() {
                        setTimeout(() => {
                            savingIndicator.style.display = 'none';
                        }, 500);
                    }
                });
            }

            // Description Inline change with Debounce
            let typingTimer;
            $('.tracker-inline-desc').on('input', function() {
                const id = $(this).attr('data-id');
                const val = $(this).val();
                
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    updatePastTrackerInline(id, { descricao: val });
                }, 800);
            });

            // Project Inline change
            $('.tracker-inline-project').on('change', function() {
                const id = $(this).attr('data-id');
                const val = $(this).val();
                updatePastTrackerInline(id, { clockify_projeto_id: val });
            });

            // Tags Inline change
            $('.tracker-inline-tags').on('change', function() {
                const id = $(this).attr('data-id');
                const val = $(this).val();
                updatePastTrackerInline(id, { tags: val });
            });

            // Function to autosave active tracker fields
            function autosaveActiveTracker() {
                if (!$('#active-descricao').length) return; // No active tracker running
                
                let descricao = $('#active-descricao').val();
                let project_id = $('#active-projeto').val();
                let tags = $('#active-tags').val();

                $.ajax({
                    url: "<?php echo e(route('clockify.tracker.active.update')); ?>",
                    type: "POST",
                    data: {
                        _token: "<?php echo e(csrf_token()); ?>",
                        _method: "PATCH",
                        descricao: descricao,
                        clockify_projeto_id: project_id,
                        tags: tags
                    },
                    success: function(response) {
                        console.log('Autosave active tracker: success');
                    },
                    error: function(err) {
                        console.error('Autosave active tracker: error', err);
                    }
                });
            }

            // Debounce for description input typing (active tracker)
            let activeTypingTimer;
            $('#active-descricao').on('input', function() {
                clearTimeout(activeTypingTimer);
                activeTypingTimer = setTimeout(autosaveActiveTracker, 750);
            });

            // Change event for project and tags dropdown select (active tracker)
            $('#active-projeto, #active-tags').on('change', function() {
                autosaveActiveTracker();
            });

            // Live timer increment
            var timerEl = document.getElementById('live-timer');
            if (timerEl) {
                var startTime = new Date(timerEl.getAttribute('data-start'));
                var TIMER_WARN_HOURS = 8;
                var warnedLongTimer = false;

                setInterval(function() {
                    var now = new Date();
                    var diffMs = now - startTime;
                    if (diffMs < 0) diffMs = 0;

                    var diffSecs = Math.floor(diffMs / 1000);
                    var hours = Math.floor(diffSecs / 3600);
                    var minutes = Math.floor((diffSecs / 60) % 60);
                    var seconds = diffSecs % 60;

                    var formatted =
                        (hours < 10 ? '0' + hours : hours) + ':' +
                        (minutes < 10 ? '0' + minutes : minutes) + ':' +
                        (seconds < 10 ? '0' + seconds : seconds);

                    timerEl.innerText = formatted;

                    if (!warnedLongTimer && diffSecs >= TIMER_WARN_HOURS * 3600) {
                        warnedLongTimer = true;
                        timerEl.classList.remove('text-success');
                        timerEl.classList.add('text-warning');
                        $('#stop-tracker-form').after(
                            '<div class="alert alert-warning d-flex align-items-center mb-0 mt-3" id="timer-long-warn" role="alert">' +
                            '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                            '<div>Seu cronômetro está rodando há mais de ' + TIMER_WARN_HOURS + 'h. Esqueceu de parar?</div>' +
                            '<button type="button" class="close ml-auto" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>' +
                            '</div>'
                        );
                    }
                }, 1000);
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/clockify/tracker.blade.php ENDPATH**/ ?>