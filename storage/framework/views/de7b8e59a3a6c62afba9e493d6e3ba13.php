<?php
    $logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout');
    $profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'logout');
    $empresaSelecionada = (int) session('empresa_context_id', 0);
    $empresaSelecionadaNome = 'Todas as empresas';

    if (config('adminlte.usermenu_profile_url', false)) {
        $profile_url = Auth::user()->adminlte_profile_url();
    }

    if (config('adminlte.use_route_url', false)) {
        $profile_url = $profile_url ? route($profile_url) : '';
        $logout_url = $logout_url ? route($logout_url) : '';
    } else {
        $profile_url = $profile_url ? url($profile_url) : '';
        $logout_url = $logout_url ? url($logout_url) : '';
    }

    if (Auth::check() && Auth::user()->isSuperAdmin() && \Illuminate\Support\Facades\Schema::hasTable('empresas') && $empresaSelecionada > 0) {
        $empresaSelecionadaNome = (string) (\Illuminate\Support\Facades\DB::table('empresas')->where('id', $empresaSelecionada)->value('nome') ?? 'Empresa selecionada');
    } elseif (Auth::check() && !Auth::user()->isSuperAdmin()) {
        $empresaSelecionadaNome = Auth::user()->empresa?->nome ?? 'Empresa do usuário';
    }
?>

<li class="nav-item dropdown user-menu">

    
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        
        <?php if(config('adminlte.usermenu_image')): ?>
            <img src="<?php echo e(Auth::user()->adminlte_image()); ?>"
                 class="user-image img-circle elevation-2"
                 alt="<?php echo e(Auth::user()->name); ?>">
        <?php endif; ?>
        <span <?php if(config('adminlte.usermenu_image')): ?> class="d-none d-md-inline" <?php endif; ?>>
            <?php echo e(Auth::user()->name); ?>

        </span>
    </a>

    
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

        
        <?php if(!View::hasSection('usermenu_header') && config('adminlte.usermenu_header')): ?>
            <li class="user-header <?php echo e(config('adminlte.usermenu_header_class', 'bg-primary')); ?>

                <?php if(!config('adminlte.usermenu_image')): ?> h-auto <?php endif; ?>">
                <?php if(config('adminlte.usermenu_image')): ?>
                    <img src="<?php echo e(Auth::user()->adminlte_image()); ?>"
                         class="img-circle elevation-2"
                         alt="<?php echo e(Auth::user()->name); ?>">
                <?php endif; ?>
                <p class="<?php if(!config('adminlte.usermenu_image')): ?> mt-0 <?php endif; ?>">
                    <?php echo e(Auth::user()->name); ?>

                    <?php if(config('adminlte.usermenu_desc')): ?>
                        <small><?php echo e(Auth::user()->adminlte_desc()); ?></small>
                    <?php endif; ?>
                </p>
            </li>
        <?php else: ?>
            <?php echo $__env->yieldContent('usermenu_header'); ?>
        <?php endif; ?>

        
        <?php echo $__env->renderEach('adminlte::partials.navbar.dropdown-item', $adminlte->menu("navbar-user"), 'item'); ?>

        
        <?php if (! empty(trim($__env->yieldContent('usermenu_body')))): ?>
            <li class="user-body">
                <?php echo $__env->yieldContent('usermenu_body'); ?>
            </li>
        <?php endif; ?>

        <?php if(Auth::check() && Auth::user()->isSuperAdmin() && \Illuminate\Support\Facades\Schema::hasTable('empresas')): ?>
            <?php
                // Busca sem qualquer escopo de tenant para sempre listar todas as empresas
                $empresasMenu = \Illuminate\Support\Facades\DB::table('empresas')->orderBy('nome')->pluck('nome', 'id')->toArray();
            ?>

            <li class="user-body empresa-context-body">
                <div class="empresa-context-select">
                    <label>Empresa da sessão</label>
                    <div class="empresa-context-current">
                        <i class="fas fa-building mr-1"></i>
                        <span><?php echo e($empresaSelecionadaNome); ?></span>
                    </div>
                    <form method="POST" action="<?php echo e(route('empresa_context.update')); ?>" class="mb-0">
                        <?php echo csrf_field(); ?>
                        <select
                            name="empresa_id"
                            class="form-control form-control-sm"
                            onchange="this.form.submit()"
                            title="Empresa da sessão"
                        >
                            <option value="0" <?php echo e($empresaSelecionada === 0 ? 'selected' : ''); ?>>Todas as empresas</option>
                            <?php $__currentLoopData = $empresasMenu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresaId => $empresaNome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($empresaId); ?>" <?php echo e($empresaSelecionada === (int) $empresaId ? 'selected' : ''); ?>>
                                    <?php echo e($empresaNome); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </form>
                    <a href="<?php echo e(route('empresa_context.edit')); ?>" class="empresa-context-link">
                        Abrir tela de troca
                    </a>
                </div>
            </li>
        <?php endif; ?>

        
        <li class="user-footer">
            <?php if($profile_url): ?>
                <a href="<?php echo e($profile_url); ?>" class="nav-link btn btn-default btn-flat d-inline-block">
                    <i class="fa fa-fw fa-user text-lightblue"></i>
                    <?php echo e(__('adminlte::menu.profile')); ?>

                </a>
            <?php endif; ?>
            <a class="btn btn-default btn-flat float-right <?php if(!$profile_url): ?> btn-block <?php endif; ?>"
               href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-fw fa-power-off text-red"></i>
                <?php echo e(__('adminlte::adminlte.log_out')); ?>

            </a>
            <form id="logout-form" action="<?php echo e($logout_url); ?>" method="POST" style="display: none;">
                <?php if(config('adminlte.logout_method')): ?>
                    <?php echo e(method_field(config('adminlte.logout_method'))); ?>

                <?php endif; ?>
                <?php echo e(csrf_field()); ?>

            </form>
        </li>

    </ul>

</li>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/vendor/adminlte/partials/navbar/menu-item-dropdown-user-menu.blade.php ENDPATH**/ ?>