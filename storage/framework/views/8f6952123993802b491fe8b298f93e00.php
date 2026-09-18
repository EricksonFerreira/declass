<div class="d-flex flex-row align-items-end mr-2">
    <label for="perPage" class="text-xs font-weight-normal mr-2" style="margin:0px;color:#6c757d">Itens Página</label>
    <select name="perPage" id="perPage" class="form-control form-control-sm p-0" onchange="this.form.submit()"
        style="
    padding:0px;height:1.3rem;width:3rem">
        <?php $__currentLoopData = $perPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perPage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($perPage); ?>" <?php echo e($perPage == $selectedPerPage ? 'selected' : ''); ?>><?php echo e($perPage); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/components/per-page-selector.blade.php ENDPATH**/ ?>