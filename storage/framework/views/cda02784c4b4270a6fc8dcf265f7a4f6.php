<?php $__env->startSection('title', "Editar {$empresa->nome}"); ?>

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
<div class="col-xl-10 col-lg-11 col-md-12 px-0">

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
    <?php endif; ?>

    <div class="card card-empresa shadow-sm border-0">
        <div class="card-header card-header-empresa border-0">
            <div>
                <h4 class="mb-1">Editar Empresa</h4>
                <p class="mb-0 text-muted">Altere as informacoes principais da sua empresa.</p>
            </div>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="<?php echo e(route('empresa.atualizar')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="empresa-section-title" style="font-size:.9rem;">Dados da Empresa</div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control form-control-sm" value="<?php echo e(old('nome', $empresa->nome)); ?>" required>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Slug</label>
                            <input type="text" class="form-control form-control-sm" value="<?php echo e($empresa->slug); ?>" readonly style="background:#f1f5f9;">
                            <small class="text-muted">Identificador unico gerado automaticamente.</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">CNPJ</label>
                            <input type="text" name="cnpj" class="form-control form-control-sm" value="<?php echo e(old('cnpj', $empresa->cnpj)); ?>">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">E-mail</label>
                            <input type="email" name="email" class="form-control form-control-sm" value="<?php echo e(old('email', $empresa->email)); ?>">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Telefone</label>
                            <input type="text" name="telefone" class="form-control form-control-sm" value="<?php echo e(old('telefone', $empresa->telefone)); ?>">
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="empresa-section-title" style="font-size:.9rem;">Identidade Visual</div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Cor de fundo do site</label>
                            <div class="d-flex align-items-center" style="gap:10px;">
                                <input type="color" name="cor_primaria" class="form-control p-1" value="<?php echo e(old('cor_primaria', $empresa->cor_primaria ?? '#2563eb')); ?>" style="width:50px;height:38px;cursor:pointer;">
                                <input type="text" class="form-control form-control-sm" value="<?php echo e($empresa->cor_primaria ?? '#2563eb'); ?>" readonly style="background:#f1f5f9;max-width:120px;">
                                <span class="small text-muted">Usada como fundo do site de apresentacao.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Cor da fonte do site</label>
                            <div class="d-flex align-items-center" style="gap:10px;">
                                <input type="color" name="cor_secundaria" class="form-control p-1" value="<?php echo e(old('cor_secundaria', $empresa->cor_secundaria ?? '#4f46e5')); ?>" style="width:50px;height:38px;cursor:pointer;">
                                <input type="text" class="form-control form-control-sm" value="<?php echo e($empresa->cor_secundaria ?? '#4f46e5'); ?>" readonly style="background:#f1f5f9;max-width:120px;">
                                <span class="small text-muted">Usada como cor do texto no site.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="empresa-section-title" style="font-size:.9rem;">Logo da Empresa</div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Logo</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
                                <label class="custom-file-label" for="logo">Escolher arquivo...</label>
                            </div>
                            <small class="text-muted">PNG, JPG ou WEBP. Max 2MB. Sera redimensionada automaticamente.</small>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center">
                        <?php $logoUrl = $empresa->logo_url; ?>
                        <?php if($logoUrl): ?>
                            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#f8fafc;display:inline-block;">
                                <img src="<?php echo e($logoUrl); ?>" alt="Logo" style="max-width:160px;max-height:80px;border-radius:6px;">
                            </div>
                            <div class="mt-2">
                                <label class="small text-muted d-block">Logo atual</label>
                            </div>
                        <?php else: ?>
                            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:20px;background:#f8fafc;display:inline-block;">
                                <i class="fas fa-image fa-3x text-muted" style="opacity:.4;"></i>
                                <p class="text-muted small mb-0 mt-1">Nenhuma logo</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center" style="gap:10px;flex-wrap:wrap;">
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Salvar alteracoes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<script>
document.getElementById('logo')?.addEventListener('change', function (e) {
    var label = this.nextElementSibling;
    if (label) label.textContent = e.target.files[0]?.name || 'Escolher arquivo...';
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('css'); ?>
<style>
.card-empresa { border-radius:14px; overflow:hidden; }
.card-header-empresa { background:linear-gradient(135deg,#0f172a,#1e293b); color:#fff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; padding:18px 22px; }
.card-header-empresa h4 { font-weight:700; font-size:1.05rem; }
.card-header-empresa .text-muted { color:rgba(255,255,255,.65) !important; }
.empresa-section-title { font-weight:700; font-size:.85rem; color:#0f172a; margin-bottom:1rem; padding-bottom:.5rem; border-bottom:1px solid #e2e8f0; }
.form-group-empresa { margin-bottom:1rem; }
input[type="color"] { cursor:pointer; border:1px solid #e2e8f0; border-radius:6px; padding:3px; }
input[type="color"]::-webkit-color-swatch-wrapper { padding:0; }
input[type="color"]::-webkit-color-swatch { border:none; border-radius:4px; }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/empresa/edit-admin.blade.php ENDPATH**/ ?>