
<div class="row">
    <div class="col-md-8">
        
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Dados do Plano</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('nome', $plano->nome ?? '')); ?>" required maxlength="80">
                            <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ordem</label>
                            <input type="number" name="ordem" class="form-control"
                                   value="<?php echo e(old('ordem', $plano->ordem ?? 0)); ?>" min="0">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end pb-3">
                        <div class="d-flex flex-column" style="gap:8px;">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="ativo" value="0">
                                <input type="checkbox" class="custom-control-input" id="ativo" name="ativo" value="1"
                                       <?php echo e(old('ativo', ($plano->ativo ?? true) ? '1' : '0') == '1' ? 'checked' : ''); ?>>
                                <label class="custom-control-label" for="ativo">Ativo</label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="destaque" value="0">
                                <input type="checkbox" class="custom-control-input" id="destaque" name="destaque" value="1"
                                       <?php echo e(old('destaque', ($plano->destaque ?? false) ? '1' : '0') == '1' ? 'checked' : ''); ?>>
                                <label class="custom-control-label" for="destaque">Destaque</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" class="form-control" rows="2"><?php echo e(old('descricao', $plano->descricao ?? '')); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor Mensal (R$) <span class="text-danger">*</span></label>
                            <input type="number" name="valor_mensal" step="0.01" min="0"
                                   class="form-control <?php $__errorArgs = ['valor_mensal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('valor_mensal', $plano->valor_mensal ?? '')); ?>" required>
                            <?php $__errorArgs = ['valor_mensal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor Anual (R$/mês) <span class="text-danger">*</span></label>
                            <input type="number" name="valor_anual" step="0.01" min="0"
                                   class="form-control <?php $__errorArgs = ['valor_anual'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('valor_anual', $plano->valor_anual ?? '')); ?>" required>
                            <small class="form-text text-muted">Valor mensal cobrado no plano anual (com desconto).</small>
                            <?php $__errorArgs = ['valor_anual'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Limite de Veículos</label>
                            <input type="number" name="limite_veiculos" min="1"
                                   class="form-control <?php $__errorArgs = ['limite_veiculos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('limite_veiculos', $plano->limite_veiculos ?? '')); ?>"
                                   placeholder="Vazio = ilimitado">
                            <?php $__errorArgs = ['limite_veiculos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Limite de Armazenamento (MB)</label>
                            <input type="number" name="limite_armazenamento_mb" min="1" step="1"
                                   class="form-control <?php $__errorArgs = ['limite_armazenamento_mb'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('limite_armazenamento_mb', $plano->limite_armazenamento_mb ?? '')); ?>"
                                   placeholder="Ex.: 5120 para 5 GB">
                            <?php $__errorArgs = ['limite_armazenamento_mb'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor veículo adicional (R$)</label>
                            <input type="number" name="valor_veiculo_adicional" step="0.01" min="0"
                                   class="form-control <?php $__errorArgs = ['valor_veiculo_adicional'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('valor_veiculo_adicional', $plano->valor_veiculo_adicional ?? '')); ?>"
                                   placeholder="Ex.: 3,00">
                            <?php $__errorArgs = ['valor_veiculo_adicional'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Quantidade de filiais</label>
                            <input type="number" name="quantidade_filiais" step="1" min="0"
                                   class="form-control <?php $__errorArgs = ['quantidade_filiais'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('quantidade_filiais', $plano->quantidade_filiais ?? '')); ?>"
                                   placeholder="Ex.: 1">
                            <?php $__errorArgs = ['quantidade_filiais'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor filial adicional (R$)</label>
                            <input type="number" name="valor_filial_adicional" step="0.01" min="0"
                                   class="form-control <?php $__errorArgs = ['valor_filial_adicional'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('valor_filial_adicional', $plano->valor_filial_adicional ?? '')); ?>"
                                   placeholder="Ex.: 29,00">
                            <?php $__errorArgs = ['valor_filial_adicional'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor armazenamento adicional (R$)</label>
                            <input type="number" name="valor_armazenamento_adicional" step="0.01" min="0"
                                   class="form-control <?php $__errorArgs = ['valor_armazenamento_adicional'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('valor_armazenamento_adicional', $plano->valor_armazenamento_adicional ?? '')); ?>"
                                   placeholder="Ex.: 5,00">
                            <?php $__errorArgs = ['valor_armazenamento_adicional'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Quantidade do armazenamento adicional</label>
                            <input type="number" name="quantidade_armazenamento_adicional" step="1" min="0"
                                   class="form-control <?php $__errorArgs = ['quantidade_armazenamento_adicional'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('quantidade_armazenamento_adicional', $plano->quantidade_armazenamento_adicional ?? '')); ?>"
                                   placeholder="Ex.: 5">
                            <?php $__errorArgs = ['quantidade_armazenamento_adicional'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php $__currentLoopData = [
                        ['site_vendas',   'globe',        'Site de Vendas Online'],
                        ['modulo_multas', 'file-invoice', 'Módulo de Multas'],
                        ['modulo_nfse',   'receipt',      'NFS-e Automática'],
                        ['multi_estacao', 'map-marker',   'Multi-Estação / Filiais'],
                        ['api_integracao','plug',         'API de Integração'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$campo, $icon, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-4 mb-2">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="<?php echo e($campo); ?>" value="0">
                                <input type="checkbox" class="custom-control-input" id="<?php echo e($campo); ?>"
                                       name="<?php echo e($campo); ?>" value="1"
                                       <?php echo e(old($campo, ($plano->$campo ?? false) ? '1' : '0') == '1' ? 'checked' : ''); ?>>
                                <label class="custom-control-label" for="<?php echo e($campo); ?>">
                                    <i class="fas fa-<?php echo e($icon); ?> mr-1 text-muted"></i> <?php echo e($label); ?>

                                </label>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        
        <div class="card card-outline card-secondary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Itens do Plano</h3>
                <button type="button" class="btn btn-xs btn-outline-primary" id="addItem">
                    <i class="fas fa-plus mr-1"></i> Adicionar
                </button>
            </div>
            <div class="card-body p-2" id="itensContainer">
                <?php $itensExistentes = old('itens', isset($plano) ? $plano->itens->toArray() : []); ?>
                <?php $__currentLoopData = $itensExistentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $itemValor = old("itens.$i.valor", $item['valor'] ?? '');
                    ?>
                    <div class="item-row mb-2" data-index="<?php echo e($i); ?>">
                        <input type="hidden" name="itens[<?php echo e($i); ?>][id]" value="<?php echo e($item['id'] ?? ''); ?>">
                        <div class="d-flex align-items-start" style="gap:8px;">
                            <div class="pt-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="itens[<?php echo e($i); ?>][incluso]" value="1"
                                           class="custom-control-input incluso-check"
                                           id="item_incluso_<?php echo e($i); ?>"
                                           <?php echo e((bool)($item['incluso'] ?? true) ? 'checked' : ''); ?>>
                                    <label class="custom-control-label" for="item_incluso_<?php echo e($i); ?>" title="Incluso"></label>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <input type="text" name="itens[<?php echo e($i); ?>][descricao]"
                                       class="form-control form-control-sm mb-1"
                                       value="<?php echo e($item['descricao'] ?? ''); ?>"
                                       placeholder="Nome do item" required>
                                <input type="number" name="itens[<?php echo e($i); ?>][valor]"
                                       class="form-control form-control-sm"
                                       value="<?php echo e($itemValor); ?>"
                                       placeholder="Preço (R$)"
                                       step="0.01"
                                       min="0">
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item mt-1" title="Remover">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="card-footer py-1">
                <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Informe o nome e o preço do item. Marque o checkbox para indicar que ele está incluso no plano.</small>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('js'); ?>
<script>
let itemIndex = <?php echo e(count($itensExistentes ?? [])); ?>;

document.getElementById('addItem').addEventListener('click', function () {
    const container = document.getElementById('itensContainer');
    const div = document.createElement('div');
    div.className = 'item-row mb-2';
    div.dataset.index = itemIndex;
    div.innerHTML = `
        <input type="hidden" name="itens[${itemIndex}][id]" value="">
        <div class="d-flex align-items-start" style="gap:8px;">
            <div class="pt-2">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="itens[${itemIndex}][incluso]" value="1" checked
                           class="custom-control-input incluso-check" id="item_incluso_${itemIndex}">
                    <label class="custom-control-label" for="item_incluso_${itemIndex}" title="Incluso"></label>
                </div>
            </div>
            <div class="flex-grow-1">
                <input type="text" name="itens[${itemIndex}][descricao]"
                       class="form-control form-control-sm mb-1" placeholder="Nome do item" required>
                <input type="number" name="itens[${itemIndex}][valor]"
                       class="form-control form-control-sm" placeholder="Preço (R$)" step="0.01" min="0">
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-item mt-1" title="Remover">
                <i class="fas fa-times"></i>
            </button>
        </div>
        `;
    container.appendChild(div);
    div.querySelector('input[type=text]').focus();
    itemIndex++;
});

document.getElementById('itensContainer').addEventListener('click', function (e) {
    const btn = e.target.closest('.remove-item');
    if (btn) {
        btn.closest('.item-row').remove();
        reindexarItens();
    }
});

function reindexarItens() {
    document.querySelectorAll('#itensContainer .item-row').forEach((row, i) => {
        row.dataset.index = i;
        row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/itens\[\d+\]/, `itens[${i}]`);
        });
    });
    itemIndex = document.querySelectorAll('#itensContainer .item-row').length;
}
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/plano/_form.blade.php ENDPATH**/ ?>