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
<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    
    /* Clean inputs inside past history table */
    .inline-tracker-input {
        background: transparent !important;
        border: 1px solid transparent !important;
        box-shadow: none !important;
        transition: all 0.2s ease;
        font-weight: 600;
        color: #1e293b;
        border-radius: 6px;
    }
    .inline-tracker-input:focus {
        background: #fff !important;
        border-color: #cbd5e1 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }

    .inline-tracker-select {
        border-color: transparent !important;
        background-color: transparent !important;
        font-weight: 700;
        cursor: pointer;
        border-radius: 6px;
    }
    .inline-tracker-select:focus, .inline-tracker-select:hover {
        background-color: #fff !important;
        border-color: #cbd5e1 !important;
    }

    /* Small badge saving status */
    .saving-indicator {
        font-size: 11px;
        font-weight: 700;
        color: #0ea5e9;
        display: none;
        align-items: center;
        gap: 4px;
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
    }

    /* Responsividade da Tabela de Tracker */
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

        /* Estilo para inputs e select mobile */
        .responsive-tracker-table td .form-control {
            display: inline-block;
            width: 100%;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <!-- Bloco de Timer (Ativo ou Novo) -->
    <div class="card card-outline card-success shadow-sm mb-4">
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
    <div class="card collapsed-card mb-4 shadow-sm">
        <div class="card-header bg-light py-2">
            <h3 class="card-title text-sm text-bold text-muted" data-card-widget="collapse" style="cursor: pointer;">
                <i class="fas fa-plus mr-1"></i> Adicionar Registro Manualmente
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('clockify.tracker.manual')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="form-group mb-0">
                            <label>Descrição</label>
                            <input type="text" name="descricao" class="form-control form-control-sm" placeholder="Ex: Ajustes de CSS">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <div class="form-group mb-0">
                            <label>Projeto</label>
                            <select name="clockify_projeto_id" class="form-control form-control-sm select2">
                                <option value="">Sem Projeto</option>
                                <?php $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($proj->id); ?>"><?php echo e($proj->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <div class="form-group mb-0">
                            <label>Início</label>
                            <input type="datetime-local" name="inicio" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <div class="form-group mb-0">
                            <label>Fim</label>
                            <input type="datetime-local" name="fim" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group mb-0">
                            <label class="d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-sm btn-success btn-block" style="margin-top: 2px;">Salvar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Histórico de Registros de Tempo -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header position-relative">
            <h3 class="card-title text-sm text-bold text-muted">Histórico de Tempo</h3>
            <div class="saving-indicator" id="global-saving-indicator">
                <i class="fas fa-sync fa-spin"></i> Salvando alterações...
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm mb-0 responsive-tracker-table">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Projeto</th>
                            <th>Tags</th>
                            <th class="text-center" style="width: 200px;">Período</th>
                            <th class="text-center" style="width: 100px;">Duração</th>
                            <th style="width: 100px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $pastTrackers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tracker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr data-tracker-id="<?php echo e($tracker->id); ?>">
                                <td class="align-middle p-1" data-label="Descrição">
                                    <input type="text" class="form-control form-control-sm inline-tracker-input tracker-inline-desc" 
                                           data-id="<?php echo e($tracker->id); ?>" value="<?php echo e($tracker->descricao); ?>" placeholder="Sem descrição">
                                </td>
                                <td class="align-middle p-1" data-label="Projeto">
                                    <select class="form-control form-control-sm inline-tracker-select tracker-inline-project" data-id="<?php echo e($tracker->id); ?>" style="min-width: 140px;">
                                        <option value="">Sem Projeto</option>
                                        <?php $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($proj->id); ?>" <?php if($tracker->clockify_projeto_id == $proj->id): echo 'selected'; endif; ?>><?php echo e($proj->nome); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td class="align-middle p-1" data-label="Tags" style="min-width: 150px;">
                                    <select class="form-control form-control-sm select2 tracker-inline-tags" multiple data-id="<?php echo e($tracker->id); ?>" data-placeholder="Tags...">
                                        <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($tag->id); ?>" <?php if($tracker->tags->contains($tag->id)): echo 'selected'; endif; ?>><?php echo e($tag->nome); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td class="text-center align-middle text-sm text-muted" data-label="Período">
                                    <?php echo e($tracker->inicio->format('d/m/Y H:i')); ?> às <?php echo e($tracker->fim ? $tracker->fim->format('H:i') : '-'); ?>

                                </td>
                                <td class="text-center align-middle font-weight-bold text-secondary" data-label="Duração">
                                    <?php echo e($tracker->formatted_duration); ?>

                                </td>
                                <td class="align-middle text-center" data-label="Ações">
                                    <form action="<?php echo e(route('clockify.tracker.destroy', $tracker->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir este registro de tempo?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-xs btn-default text-danger"><i class="fa fa-trash"></i> Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhum registro de tempo encontrado. Comece a rastrear agora!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            <?php echo e($pastTrackers->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(document).ready(function() {
            const savingIndicator = document.getElementById('global-saving-indicator');

            // Autocomplete update function for past trackers
            function updatePastTrackerInline(trackerId, dataObj) {
                savingIndicator.style.display = 'flex';

                $.ajax({
                    url: `/admin/clockify/tracker/${trackerId}`,
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
                        }, 600);
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
                }, 1000);
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/clockify/tracker.blade.php ENDPATH**/ ?>