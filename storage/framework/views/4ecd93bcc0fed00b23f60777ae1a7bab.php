<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'usuarios'  => collect(),   // Collection de User com id, nome, email, numero_identificacao
    'modalId'   => 'modalUsuarioMov',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'usuarios'  => collect(),   // Collection de User com id, nome, email, numero_identificacao
    'modalId'   => 'modalUsuarioMov',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#1e3a8a,#1d4ed8);color:#fff">
                <div class="d-flex align-items-center" style="gap:10px">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Selecionar Usuário</h5>
                        <small style="opacity:.75">Responsável por esta movimentação</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                
                <div class="card card-body py-2 mb-3 border-0 bg-light">
                    <div class="form-row align-items-end">
                        <div class="col-md-4 form-group mb-0">
                            <label class="small mb-1 font-weight-600">Buscar por nome</label>
                            <input type="text" id="<?php echo e($modalId); ?>_nome"
                                   class="form-control form-control-sm"
                                   placeholder="Ex: João Silva…"
                                   oninput="filtrarModalUsuario('<?php echo e($modalId); ?>')">
                        </div>
                        <div class="col-md-4 form-group mb-0">
                            <label class="small mb-1 font-weight-600">Buscar por e-mail</label>
                            <input type="text" id="<?php echo e($modalId); ?>_email"
                                   class="form-control form-control-sm"
                                   placeholder="Ex: joao@email.com…"
                                   oninput="filtrarModalUsuario('<?php echo e($modalId); ?>')">
                        </div>
                        <div class="col-md-2 form-group mb-0">
                            <label class="small mb-1 font-weight-600">ID</label>
                            <input type="number" id="<?php echo e($modalId); ?>_id"
                                   class="form-control form-control-sm"
                                   placeholder="#"
                                   oninput="filtrarModalUsuario('<?php echo e($modalId); ?>')">
                        </div>
                        <div class="col-md-2 form-group mb-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                    onclick="['_nome','_email','_id'].forEach(function(s){var el=document.getElementById('<?php echo e($modalId); ?>'+s);if(el)el.value='';});filtrarModalUsuario('<?php echo e($modalId); ?>')">
                                <i class="fas fa-times mr-1"></i>Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-2" id="<?php echo e($modalId); ?>_count"><?php echo e($usuarios->count()); ?> usuários disponíveis</p>

                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:60px">ID</th>
                                <th>Usuário</th>
                                <th>E-mail</th>
                                <th style="width:120px">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="<?php echo e($modalId); ?>_lista">
                            <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="mv-usuario-row"
                                data-id="<?php echo e($u->id); ?>"
                                data-nome="<?php echo e(mb_strtolower($u->nome ?? '')); ?>"
                                data-email="<?php echo e(mb_strtolower($u->email ?? '')); ?>">
                                <td><span class="badge badge-secondary">#<?php echo e($u->id); ?></span></td>
                                <td>
                                    <strong><?php echo e($u->nome ?? '—'); ?></strong>
                                    <?php if($u->numero_identificacao): ?>
                                    <br><small class="text-muted"><?php echo e($u->numero_identificacao); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo e($u->email ?? '—'); ?></small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary btn-selecionar-usuario"
                                            data-id="<?php echo e($u->id); ?>" data-nome="<?php echo e($u->nome); ?>">
                                        <i class="fas fa-check mr-1"></i>Selecionar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div id="<?php echo e($modalId); ?>_vazio" class="text-center text-muted py-4" style="display:none">
                    <i class="fas fa-user-slash fa-2x mb-2"></i>
                    <p>Nenhum usuário encontrado.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('b115b18b-d456-41f1-a57c-3c7e4685fe61')): $__env->markAsRenderedOnce('b115b18b-d456-41f1-a57c-3c7e4685fe61'); ?>
<?php $__env->startPush('js'); ?>
<script>
window.filtrarModalUsuario = function(modalId) {
    var nome  = (document.getElementById(modalId + '_nome')?.value  || '').toLowerCase().trim();
    var email = (document.getElementById(modalId + '_email')?.value || '').toLowerCase().trim();
    var id    = (document.getElementById(modalId + '_id')?.value    || '').trim();
    var rows  = document.querySelectorAll('#' + modalId + '_lista .mv-usuario-row');
    var visible = 0;
    rows.forEach(function(r) {
        var matchNome  = !nome  || (r.dataset.nome  || '').includes(nome);
        var matchEmail = !email || (r.dataset.email || '').includes(email);
        var matchId    = !id    || String(r.dataset.id) === id;
        var show = matchNome && matchEmail && matchId;
        r.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    var count = document.getElementById(modalId + '_count');
    if (count) count.textContent = visible + ' usuários disponíveis';
    var vazio = document.getElementById(modalId + '_vazio');
    if (vazio) vazio.style.display = visible === 0 ? '' : 'none';
};
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\movimentacao\modal-usuario.blade.php ENDPATH**/ ?>