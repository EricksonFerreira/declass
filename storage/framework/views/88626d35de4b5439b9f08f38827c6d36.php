<?php
    $empresaTemaAtual = null;
    $empresaSelecionada = 0;

    $contrasteParaHex = function (?string $hex, string $fallback = '#FFFFFF'): string {
        $hex = trim((string) ($hex ?? ''));
        if ($hex === '') {
            return $fallback;
        }

        if ($hex[0] === '#') {
            $hex = substr($hex, 1);
        }

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return $fallback;
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // sRGB -> linear
        $rl = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $gl = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $bl = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        $luminancia = 0.2126 * $rl + 0.7152 * $gl + 0.0722 * $bl;

        // Se fundo for claro, texto escuro; se fundo for escuro, texto branco.
        return $luminancia > 0.58 ? '#0F172A' : '#FFFFFF';
    };

    if (Auth::check() && \Illuminate\Support\Facades\Schema::hasTable('empresas')) {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            // Empresa da sessão tem prioridade; fallback = empresa do próprio usuário
            $empresaSelecionada = (int) session('empresa_context_id', 0);
            $empresaIdTema = $empresaSelecionada > 0
                ? $empresaSelecionada
                : (int) ($user->empresa_id ?? 0);
        } else {
            // Demais usuários: sempre a empresa própria
            $empresaIdTema = (int) ($user->empresa_id ?? 0);
            $empresaSelecionada = $empresaIdTema;
        }

        if ($empresaIdTema > 0) {
            $empresaTemaAtual = (object) \Illuminate\Support\Facades\DB::table('empresas')
                ->select(['id', 'nome', 'logo_path', 'cor_primaria', 'cor_secundaria', 'cor_terciaria'])
                ->where('id', $empresaIdTema)
                ->first();
        }
    }
?>

<?php
    $corPrimaria = $empresaTemaAtual->cor_primaria ?? '#FFFFFF';
    $corSecundaria = $empresaTemaAtual->cor_secundaria ?? '#F8FAFC';
    $corTerciaria = $empresaTemaAtual->cor_terciaria ?? '#CBD5E1';

    $corPrimariaContraste = $contrasteParaHex($corPrimaria);
    $corSecundariaContraste = $contrasteParaHex($corSecundaria);
    $corTerciariaContraste = $contrasteParaHex($corTerciaria);
?>

<style>
    :root {
        --empresa-cor-primaria: <?php echo e($corPrimaria); ?>;
        --empresa-cor-secundaria: <?php echo e($corSecundaria); ?>;
        --empresa-cor-terciaria: <?php echo e($corTerciaria); ?>;

        --empresa-cor-primaria-contraste: <?php echo e($corPrimariaContraste); ?>;
        --empresa-cor-secundaria-contraste: <?php echo e($corSecundariaContraste); ?>;
        --empresa-cor-terciaria-contraste: <?php echo e($corTerciariaContraste); ?>;
    }

    /* Sidebar search: keep typed match text readable on light results panel */
    .main-sidebar .sidebar-search-results .search-title,
    .main-sidebar .sidebar-search-results .search-path {
        color: #111827 !important;
    }

    .main-sidebar .sidebar-search-results .search-title .text-light,
    .main-sidebar .sidebar-search-results .search-path .text-light,
    .main-sidebar .sidebar-search-results .search-title strong,
    .main-sidebar .sidebar-search-results .search-path strong {
        color: #000 !important;
        font-weight: 700 !important;
    }

    .user-menu .dropdown-menu {
        border: 1px solid #e2e8f0;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        border-radius: 14px;
        padding: 0.35rem 0.4rem;
    }

    .user-menu .dropdown-menu .user-footer {
        border-top: 1px solid #e2e8f0;
        padding: 12px;
        background: #f8fafc;
        border-radius: 0 0 12px 12px;
    }

    .user-menu .dropdown-menu .user-footer .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    .user-menu .user-footer .empresa-context-select {
        margin-top: 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px;
    }

    .user-menu .empresa-context-body {
        padding: 12px 12px 0;
    }

    .user-menu .empresa-context-select label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #64748b;
        margin-bottom: 6px;
        display: block;
        font-weight: 700;
    }

    .user-menu .empresa-context-current {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: 100%;
        margin-bottom: 8px;
        padding: 8px 10px;
        border-radius: 10px;
        background: #f8fafc;
        color: #0f172a;
        font-weight: 700;
        font-size: 13px;
    }

    .user-menu .empresa-context-select select {
        border-radius: 10px;
        font-size: 13px;
    }

    .user-menu .empresa-context-link {
        display: inline-flex;
        margin-top: 8px;
        font-size: 12px;
        font-weight: 600;
        color: var(--empresa-cor-primaria, #2563eb);
    }

    .user-menu .empresa-context-link:hover {
        text-decoration: underline;
    }
</style>

<?php if($empresaTemaAtual): ?>
    <style>
        body:not(.login-page) .content-wrapper {
            background-image: linear-gradient(
                135deg,
                color-mix(in srgb, var(--empresa-cor-primaria) 16%, white 84%) 0%,
                color-mix(in srgb, var(--empresa-cor-secundaria) 14%, white 86%) 55%,
                color-mix(in srgb, var(--empresa-cor-terciaria) 12%, white 88%) 100%
            );
            background-attachment: fixed;
        }

        body:not(.logipn-page) .main-header.navbar {
            background: var(--empresa-cor-primaria) !important;
            border-bottom: 2px solid color-mix(in srgb, var(--empresa-cor-terciaria) 60%, #0f172a 40%);
        }

        body:not(.login-page) .main-header.navbar .nav-link,
        body:not(.login-page) .main-header.navbar .navbar-nav .nav-link i {
            background: var(--empresa-cor-primaria) !important;
            color: var(--empresa-cor-primaria-contraste, #fff) !important;
            align-items: center;
        }

        body:not(.login-page) .main-sidebar {
            background: var(--empresa-cor-primaria) !important;
        }

        body:not(.login-page) .main-sidebar .brand-text,
        body:not(.login-page) .main-sidebar .nav-sidebar .nav-link,
        body:not(.login-page) .main-sidebar .nav-header,
        body:not(.login-page) .main-sidebar .user-panel .info,
        body:not(.login-page) .main-sidebar .user-panel .info a {
            color: var(--empresa-cor-primaria-contraste, #fff) !important;
        }

        body:not(.login-page) .main-sidebar .nav-sidebar .nav-link.active,
        body:not(.login-page) .main-sidebar .nav-sidebar .nav-link:hover {
            background: color-mix(in srgb, var(--empresa-cor-terciaria) 35%, transparent 65%) !important;
            color: var(--empresa-cor-primaria-contraste, #fff) !important;
        }

        body:not(.login-page) .main-sidebar .nav-item.menu-open > .nav-treeview > .nav-item > .nav-link {
            background: color-mix(in srgb, var(--empresa-cor-secundaria) 16%, transparent 84%) !important;
            border-radius: .35rem;
            margin: 2px 6px;
        }

        body:not(.login-page) .main-sidebar .nav-item.menu-open > .nav-treeview > .nav-item > .nav-link.active {
            background: color-mix(in srgb, var(--empresa-cor-terciaria) 55%, transparent 45%) !important;
            font-weight: 700;
        }

        body:not(.login-page) .main-header.navbar {
            border-bottom: 2px solid color-mix(in srgb, var(--empresa-cor-primaria) 35%, #dbe5f0 65%);
        }

        .empresa-context-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 260px;
            padding: .3rem .6rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--empresa-cor-primaria) 75%, #000 25%);
            color: var(--empresa-cor-primaria-contraste, #fff);
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
        }

        .empresa-context-chip img {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            object-fit: contain;
            background: var(--empresa-cor-primaria);
            border: 1px solid color-mix(in srgb, var(--empresa-cor-secundaria) 25%, transparent 75%);
            padding: 1px;
        }

        .empresa-context-chip .empresa-chip-inicial {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--empresa-cor-secundaria);
            color: var(--empresa-cor-primaria);
            font-weight: 700;
        }
    </style>
<?php endif; ?>

<nav class="main-header navbar
    <?php echo e(config('adminlte.classes_topnav_nav', 'navbar-expand')); ?>

    <?php echo e(config('adminlte.classes_topnav', 'navbar-white navbar-light')); ?>">

    
    <ul class="navbar-nav">
        
        <?php echo $__env->make('adminlte::partials.navbar.menu-item-left-sidebar-toggler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        
        <?php echo $__env->renderEach('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item'); ?>

        
        <?php echo $__env->yieldContent('content_top_nav_left'); ?>
    </ul>
    
    <ul class="navbar-nav ml-auto">
        <?php if($empresaTemaAtual): ?>
            <?php
                $empresaLogoPath = is_string($empresaTemaAtual->logo_path ?? null)
                    ? trim($empresaTemaAtual->logo_path)
                    : '';
            ?>
            <li class="nav-item d-flex align-items-center mr-2">
                <span class="empresa-context-chip" title="<?php echo e($empresaTemaAtual->nome); ?>">
                    <?php if($empresaLogoPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaLogoPath)): ?>
                        <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($empresaLogoPath)); ?>" alt="Logo empresa">
                    <?php else: ?>
                        <span class="empresa-chip-inicial"><?php echo e(strtoupper(mb_substr($empresaTemaAtual->nome, 0, 1))); ?></span>
                    <?php endif; ?>
                    <span class="text-truncate"><?php echo e($empresaTemaAtual->nome); ?></span>
                    <?php if(Auth::user()->isSuperAdmin() && $empresaSelecionada === 0): ?>
                        <small style="opacity:.65;font-size:.75em;margin-left:2px">(Todas)</small>
                    <?php endif; ?>
                </span>
            </li>
        <?php endif; ?>

        
        <?php echo $__env->yieldContent('content_top_nav_right'); ?>

        
        <?php echo $__env->renderEach('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item'); ?>

        
        <?php if(Auth::user()): ?>
            <?php if(config('adminlte.usermenu_enabled')): ?>
                <?php echo $__env->make('adminlte::partials.navbar.menu-item-dropdown-user-menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php else: ?>
                <?php echo $__env->make('adminlte::partials.navbar.menu-item-logout-link', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>
        <?php endif; ?>

        
        <?php if(config('adminlte.right_sidebar')): ?>
            <?php echo $__env->make('adminlte::partials.navbar.menu-item-right-sidebar-toggler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
    </ul>

</nav>


<?php if(auth()->guard()->check()): ?>
<?php if (isset($component)) { $__componentOriginal385f409254683bd28210b83aa680c449 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal385f409254683bd28210b83aa680c449 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding-bubble','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding-bubble'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal385f409254683bd28210b83aa680c449)): ?>
<?php $attributes = $__attributesOriginal385f409254683bd28210b83aa680c449; ?>
<?php unset($__attributesOriginal385f409254683bd28210b83aa680c449); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal385f409254683bd28210b83aa680c449)): ?>
<?php $component = $__componentOriginal385f409254683bd28210b83aa680c449; ?>
<?php unset($__componentOriginal385f409254683bd28210b83aa680c449); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/vendor/adminlte/partials/navbar/navbar.blade.php ENDPATH**/ ?>