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
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }

    .planning-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 18px rgba(0,0,0,0.05);
    }

    /* Team rows in main table */
    .team-row {
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .team-row:hover { background-color: #f0f7ff !important; }
    .team-row.expanded { background-color: #e8f4fd !important; }

    /* Expansion row */
    .expand-row { display: none; }
    .expand-row.open { display: table-row; }
    .expand-inner {
        background: #f8fafc;
        border-left: 4px solid #0284c7;
        border-radius: 0 0 8px 8px;
        padding: 16px 20px;
    }

    /* Member table inside expansion */
    .member-table th {
        background: #f1f5f9;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }
    .member-row-allocated { background: #f0fdf4 !important; }
    .member-row-allocated td { border-left: 3px solid #28a745; }

    /* Date input constraint indicator */
    input[type="date"]:invalid,
    input[type="date"].out-of-range {
        border-color: #dc3545 !important;
        background: #fff5f5;
    }
    .date-hint {
        font-size: 11px;
        color: #94a3b8;
        display: block;
        margin-top: 2px;
    }

    /* Period pills */
    .period-pill {
        display: inline-block;
        background: #e0f2fe;
        color: #0284c7;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .toggle-icon { transition: transform 0.2s ease; }
    .team-row.expanded .toggle-icon { transform: rotate(90deg); }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    // Build JS-safe data about existing allocations keyed by user_id
    $alocacoesJs = [];
    foreach ($alocacoes as $userId => $userAlocs) {
        foreach ($userAlocs as $aloc) {
            $cronId = $aloc->clockify_projeto_cronograma_id ?? 'all';
            $alocacoesJs[$userId][$cronId] = [
                'cronograma_id'=> $aloc->clockify_projeto_cronograma_id,
                'data_inicio'  => $aloc->data_inicio  ? $aloc->data_inicio->format('Y-m-d') : null,
                'data_fim'     => $aloc->data_fim     ? $aloc->data_fim->format('Y-m-d') : null,
                'horas_por_dia'=> $aloc->horas_por_dia ?? 8,
                'observacao'   => $aloc->observacao    ?? '',
            ];
        }
    }
?>


<div class="card planning-card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold text-dark mb-1">
                    <i class="fas fa-project-diagram text-primary mr-2"></i><?php echo e($projeto->nome); ?>

                </h5>
                <span class="text-muted text-sm">Cliente: <strong><?php echo e($projeto->cliente->nome ?? '—'); ?></strong></span>
            </div>
            <div class="d-flex flex-wrap align-items-center">
                <?php $__empty_1 = true; $__currentLoopData = $cronogramas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cron): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if($cron->data_inicio && $cron->data_fim): ?>
                        <span class="period-pill mr-2 mb-1">
                            <i class="far fa-calendar-alt mr-1"></i>
                            <?php echo e($cron->nome); ?>: <?php echo e($cron->data_inicio->format('d/m/Y')); ?> – <?php echo e($cron->data_fim->format('d/m/Y')); ?>

                        </span>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <span class="text-warning text-sm"><i class="fas fa-exclamation-triangle mr-1"></i>Nenhum cronograma com datas definidas</span>
                <?php endif; ?>
            </div>
            <a href="<?php echo e(route('clockify.projetos.show', $projeto->id)); ?>" class="btn btn-default btn-sm font-weight-bold text-secondary mt-2 mt-md-0">
                <i class="fas fa-arrow-left mr-1"></i> Voltar ao Projeto
            </a>
        </div>
    </div>
</div>

<?php if(!$dataInicioGlobal): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Atenção:</strong> Nenhum cronograma possui datas definidas. Defina as datas de início e fim nas etapas do projeto antes de planejar a equipe.
    </div>
<?php endif; ?>


<div class="card planning-card border-0">
    <div class="card-header bg-white py-3 border-bottom">
        <h3 class="card-title text-bold text-dark mb-0">
            <i class="fas fa-users text-primary mr-2"></i> Equipes disponíveis
            <small class="text-muted font-weight-normal ml-2">— clique em uma equipe para alocar membros</small>
        </h3>
    </div>
    <div class="card-body p-0">
        <form id="form-alocacao" action="<?php echo e(route('clockify.projetos.planejar-equipe.store', $projeto->id)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="global-min-date" value="<?php echo e($dataInicioGlobal); ?>">
            <input type="hidden" id="global-max-date" value="<?php echo e($dataFimGlobal); ?>">

            <table class="table table-hover mb-0" id="teams-table">
                <thead class="thead-light">
                    <tr class="text-xs text-uppercase">
                        <th class="pl-4" style="width:40px;"></th>
                        <th>Equipe / Time</th>
                        <th class="text-center">Membros</th>
                        <th class="text-center">Alocados neste Projeto</th>
                        <th class="text-center">Período coberto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $times; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $time): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $membrosAlocados = $time->users->filter(fn($u) => isset($alocacoesJs[$u->id]))->count();
                        ?>
                        
                        <tr class="team-row <?php echo e($membrosAlocados > 0 ? 'expanded' : ''); ?>"
                            data-team-id="<?php echo e($time->id); ?>"
                            onclick="toggleTeam(<?php echo e($time->id); ?>)">
                            <td class="pl-4 align-middle text-center">
                                <i class="fas fa-chevron-right toggle-icon text-primary"></i>
                            </td>
                            <td class="align-middle font-weight-bold text-dark">
                                <i class="fas fa-users text-secondary mr-2"></i><?php echo e($time->nome); ?>

                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-secondary px-2"><?php echo e($time->users->count()); ?></span>
                            </td>
                            <td class="align-middle text-center">
                                <?php if($membrosAlocados > 0): ?>
                                    <span class="badge badge-success px-2"><?php echo e($membrosAlocados); ?> alocado(s)</span>
                                <?php else: ?>
                                    <span class="text-muted text-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-center">
                                <?php if($dataInicioGlobal && $dataFimGlobal): ?>
                                    <small class="text-muted">
                                        <?php echo e(\Carbon\Carbon::parse($dataInicioGlobal)->format('d/m/Y')); ?>

                                        –
                                        <?php echo e(\Carbon\Carbon::parse($dataFimGlobal)->format('d/m/Y')); ?>

                                    </small>
                                <?php else: ?>
                                    <small class="text-warning">Sem datas</small>
                                <?php endif; ?>
                            </td>
                        </tr>

                        
                        <tr class="expand-row <?php echo e($membrosAlocados > 0 ? 'open' : ''); ?>" id="expand-<?php echo e($time->id); ?>">
                            <td colspan="5" class="p-0">
                                <div class="expand-inner">
                                    <?php if($time->users->count() === 0): ?>
                                        <p class="text-muted text-sm mb-0">
                                            <i class="fas fa-info-circle mr-1"></i>Esta equipe não possui membros cadastrados.
                                        </p>
                                    <?php else: ?>
                                        <table class="table table-sm member-table mb-0 bg-white rounded" style="border-radius:8px; overflow:hidden;">
                                            <thead>
                                                <tr>
                                                    <th style="width:50px;" class="text-center pl-3">Alocar</th>
                                                    <th>Membro</th>
                                                    <th style="width:180px;">Etapa do Projeto</th>
                                                    <th>Função / Tarefa</th>
                                                    <th style="width:100px;" class="text-center">Horas/Dia</th>
                                                    <th style="width:145px;">Data Início</th>
                                                    <th style="width:145px;">Data Fim</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__currentLoopData = $time->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $userAlocs = $alocacoesJs[$user->id] ?? null;
                                                        // Tenta pegar a alocação específica desta etapa ou a primeira disponível
                                                        $aloc = null;
                                                        if ($userAlocs) {
                                                            $aloc = reset($userAlocs); // Pega a primeira alocação encontrada
                                                        }
                                                        $isAllocated = $aloc !== null;
                                                        $key = $time->id . '_' . $idx;
                                                    ?>
                                                    <tr class="member-row <?php echo e($isAllocated ? 'member-row-allocated' : ''); ?>"
                                                        id="member-row-<?php echo e($key); ?>">
                                                        <td class="text-center align-middle pl-3">
                                                            <input type="hidden"
                                                                   name="alocacoes[<?php echo e($key); ?>][checked]"
                                                                   id="hidden-check-<?php echo e($key); ?>"
                                                                   value="<?php echo e($isAllocated ? '1' : '0'); ?>">
                                                            <input type="checkbox"
                                                                   class="member-checkbox"
                                                                   data-key="<?php echo e($key); ?>"
                                                                   <?php echo e($isAllocated ? 'checked' : ''); ?>

                                                                   onchange="toggleMember('<?php echo e($key); ?>', this)">
                                                        </td>
                                                        <td class="align-middle font-weight-bold text-dark">
                                                            <input type="hidden" name="alocacoes[<?php echo e($key); ?>][user_id]" value="<?php echo e($user->id); ?>">
                                                            <?php echo e($user->name ?? $user->nome); ?>

                                                        </td>
                                                        
                                                        <td class="align-middle">
                                                            <select name="alocacoes[<?php echo e($key); ?>][clockify_projeto_cronograma_id]"
                                                                    id="etapa-<?php echo e($key); ?>"
                                                                    class="form-control form-control-sm etapa-select"
                                                                    data-key="<?php echo e($key); ?>"
                                                                    onchange="onEtapaChange('<?php echo e($key); ?>')">
                                                                <option value="">— Todas as etapas —</option>
                                                                <?php $__currentLoopData = $cronogramas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cron): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <?php if($cron->data_inicio && $cron->data_fim): ?>
                                                                        <option value="<?php echo e($cron->id); ?>"
                                                                            data-inicio="<?php echo e($cron->data_inicio->format('Y-m-d')); ?>"
                                                                            data-fim="<?php echo e($cron->data_fim->format('Y-m-d')); ?>"
                                                                            <?php if($aloc && ($aloc['cronograma_id'] ?? null) == $cron->id): ?> selected <?php endif; ?>>
                                                                            <?php echo e($cron->nome); ?>

                                                                            (<?php echo e($cron->data_inicio->format('d/m')); ?>–<?php echo e($cron->data_fim->format('d/m/Y')); ?>)
                                                                        </option>
                                                                    <?php else: ?>
                                                                        <option value="<?php echo e($cron->id); ?>"
                                                                            <?php if($aloc && ($aloc['cronograma_id'] ?? null) == $cron->id): ?> selected <?php endif; ?>>
                                                                            <?php echo e($cron->nome); ?> (sem datas)
                                                                        </option>
                                                                    <?php endif; ?>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select>
                                                        </td>
                                                        <td class="align-middle">
                                                            <input type="text"
                                                                   name="alocacoes[<?php echo e($key); ?>][observacao]"
                                                                   class="form-control form-control-sm"
                                                                   placeholder="Ex: Dev Front-end, QA..."
                                                                   value="<?php echo e($aloc['observacao'] ?? ''); ?>">
                                                        </td>
                                                        <td class="align-middle">
                                                            <input type="number"
                                                                   name="alocacoes[<?php echo e($key); ?>][horas_por_dia]"
                                                                   class="form-control form-control-sm text-center font-weight-bold"
                                                                   step="0.5" min="0.5" max="24"
                                                                   value="<?php echo e($aloc['horas_por_dia'] ?? 8); ?>">
                                                        </td>
                                                        <td class="align-middle">
                                                            <input type="date"
                                                                   name="alocacoes[<?php echo e($key); ?>][data_inicio]"
                                                                   id="start-<?php echo e($key); ?>"
                                                                   class="form-control form-control-sm date-constrained"
                                                                   min="<?php echo e($dataInicioGlobal); ?>"
                                                                   max="<?php echo e($dataFimGlobal); ?>"
                                                                   <?php echo e($isAllocated ? 'required' : ''); ?>

                                                                   value="<?php echo e(($aloc && $aloc['data_inicio']) ? $aloc['data_inicio'] : ''); ?>"
                                                                   onchange="validateDateRange('<?php echo e($key); ?>')">
                                                            <?php if($dataInicioGlobal): ?>
                                                                <span class="date-hint">mín: <?php echo e(\Carbon\Carbon::parse($dataInicioGlobal)->format('d/m/Y')); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="align-middle">
                                                            <input type="date"
                                                                   name="alocacoes[<?php echo e($key); ?>][data_fim]"
                                                                   id="end-<?php echo e($key); ?>"
                                                                   class="form-control form-control-sm date-constrained"
                                                                   min="<?php echo e($dataInicioGlobal); ?>"
                                                                   max="<?php echo e($dataFimGlobal); ?>"
                                                                   <?php echo e($isAllocated ? 'required' : ''); ?>

                                                                   value="<?php echo e(($aloc && $aloc['data_fim']) ? $aloc['data_fim'] : ''); ?>"
                                                                   onchange="validateDateRange('<?php echo e($key); ?>')">
                                                            <?php if($dataFimGlobal): ?>
                                                                <span class="date-hint">máx: <?php echo e(\Carbon\Carbon::parse($dataFimGlobal)->format('d/m/Y')); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fa-3x mb-3"></i>
                                <p class="mb-0">Nenhuma equipe cadastrada ainda.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            
            <div class="d-flex justify-content-between align-items-center p-3 border-top bg-white" style="border-radius: 0 0 12px 12px;">
                <a href="<?php echo e(route('clockify.projetos.show', $projeto->id)); ?>" class="btn btn-default font-weight-bold text-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success font-weight-bold shadow-sm px-4">
                    <i class="fas fa-save mr-1"></i> Salvar Alocações
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
    const MIN_DATE = document.getElementById('global-min-date').value;
    const MAX_DATE = document.getElementById('global-max-date').value;

    // Toggle team expansion
    function toggleTeam(teamId) {
        const row     = document.querySelector(`[data-team-id="${teamId}"]`);
        const expRow  = document.getElementById(`expand-${teamId}`);
        const isOpen  = expRow.classList.contains('open');
        expRow.classList.toggle('open', !isOpen);
        row.classList.toggle('expanded', !isOpen);
    }

    // When user selects a stage: auto-fill dates with stage dates and update min/max constraints
    function onEtapaChange(key) {
        const select   = document.getElementById(`etapa-${key}`);
        const startEl  = document.getElementById(`start-${key}`);
        const endEl    = document.getElementById(`end-${key}`);
        const selected = select.options[select.selectedIndex];

        const inicio = selected.getAttribute('data-inicio') || MIN_DATE;
        const fim    = selected.getAttribute('data-fim')    || MAX_DATE;

        // Set min/max constraints to the chosen stage (or global if none)
        if (inicio) {
            startEl.min = inicio;
            endEl.min   = inicio;
        }
        if (fim) {
            startEl.max = fim;
            endEl.max   = fim;
        }

        // Auto-fill dates if fields are empty or if user explicitly picked a stage
        if (select.value) {
            if (inicio) startEl.value = inicio;
            if (fim)    endEl.value   = fim;
        } else {
            // Reset to global project limits
            startEl.min = MIN_DATE;
            startEl.max = MAX_DATE;
            endEl.min   = MIN_DATE;
            endEl.max   = MAX_DATE;
        }

        validateDateRange(key);
    }

    // Toggle member allocation
    function toggleMember(key, checkbox) {
        const hidden  = document.getElementById(`hidden-check-${key}`);
        const startEl = document.getElementById(`start-${key}`);
        const endEl   = document.getElementById(`end-${key}`);
        const row     = document.getElementById(`member-row-${key}`);

        hidden.value       = checkbox.checked ? '1' : '0';
        startEl.required   = checkbox.checked;
        endEl.required     = checkbox.checked;
        row.classList.toggle('member-row-allocated', checkbox.checked);
    }

    // Validate that picked dates are within selected stage (or project) range
    function validateDateRange(key) {
        const startEl = document.getElementById(`start-${key}`);
        const endEl   = document.getElementById(`end-${key}`);

        [startEl, endEl].forEach(el => {
            if (!el.value) { el.setCustomValidity(''); el.classList.remove('out-of-range'); return; }
            const d   = el.value;
            const min = el.min || MIN_DATE;
            const max = el.max || MAX_DATE;
            const out = (min && d < min) || (max && d > max);
            el.classList.toggle('out-of-range', out);
            el.setCustomValidity(out
                ? `Data fora do período permitido (${min ? formatDate(min) : '?'} – ${max ? formatDate(max) : '?'})`
                : '');
        });

        // End must be >= Start
        if (startEl.value && endEl.value && endEl.value < startEl.value) {
            endEl.setCustomValidity('Data de fim deve ser igual ou posterior ao início.');
            endEl.classList.add('out-of-range');
        }
    }

    function formatDate(ymd) {
        const [y, m, d] = ymd.split('-');
        return `${d}/${m}/${y}`;
    }

    // Block form submit if any date is out of range
    document.getElementById('form-alocacao').addEventListener('submit', function(e) {
        const invalids = this.querySelectorAll('.out-of-range');
        if (invalids.length > 0) {
            e.preventDefault();
            alert('Existem datas fora do período de planejamento. Corrija antes de salvar.');
            invalids[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Run initial validation on pre-filled dates
    document.querySelectorAll('.date-constrained').forEach(el => {
        if (el.value) {
            const key = el.id.replace(/^(start|end)-/, '');
            validateDateRange(key);
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/clockify/projetos_planejar_equipe.blade.php ENDPATH**/ ?>