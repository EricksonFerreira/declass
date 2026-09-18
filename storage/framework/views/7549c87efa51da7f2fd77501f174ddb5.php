<div class="page-title d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem;">
    <div>
        <h3 class="mb-0 font-weight-bold"><?php echo e($title); ?></h3>
        <?php if(!empty($subtitle)): ?>
            <div class="text-muted" style="font-size:.86rem;"><?php echo e($subtitle); ?></div>
        <?php endif; ?>
    </div>

    <div class="d-flex align-items-center flex-wrap" style="gap:.35rem;">
        <?php echo e($slot); ?>


        <?php if(isset($createUrl) && $createUrl && (!isset($createCan) || (auth()->check() && auth()->user()->hasPermissao($createCan)))): ?>
            <a href="<?php echo e($createUrl); ?>" class="btn btn-xs btn-primary btn-create-resource"><i class="fa fa-plus" style="font-size:10px"></i> Adicionar</a>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\page-title.blade.php ENDPATH**/ ?>