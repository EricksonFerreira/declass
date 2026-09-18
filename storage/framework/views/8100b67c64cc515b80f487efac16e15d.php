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
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo,'subtitle' => 'Acesse rapidamente as principais áreas do sistema'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
<?php echo $__env->make('partials.home-cards-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $secoes = [
        [
            'titulo' => 'Registrar tempo',
            'cards' => [
                ['Rastreador', 'fas fa-stopwatch', 'clockify.tracker.index', 'Cronometre ou lance horas manualmente', '#0d9488'],
                ['Timesheet', 'fas fa-table', 'clockify.timesheet', 'Sua grade semanal de apontamentos', '#0891b2'],
                ['Calendário', 'fas fa-calendar-alt', 'clockify.calendario.index', 'Apontamentos e alocações por dia', '#2563eb'],
            ],
        ],
        [
            'titulo' => 'Painel',
            'cards' => [
                ['Visão Geral', 'fas fa-tachometer-alt', 'dashboard', 'Visão geral com gráficos e KPIs', '#2563eb'],
                ['Produtividade', 'fas fa-chart-line', 'home.produtividade', 'Horas e evolução da equipe', '#0d9488'],
                ['Equipe', 'fas fa-users', 'home.equipe', 'Quem está rastreando, status e alertas', '#16a34a'],
                ['Projetos', 'fas fa-project-diagram', 'home.projetos', 'Saúde e horas por projeto', '#0284c7'],
            ],
        ],
        [
            'titulo' => 'Relatórios',
            'cards' => [
                ['Relatórios', 'fas fa-file-alt', 'relatorios.index', 'Resumos e visões de horas', '#7c3aed'],
                ['Consumo × Estimado', 'fas fa-chart-bar', 'relatorios.consumo', 'Horas consumidas vs estimadas', '#b45309'],
                ['Exportar CSV', 'fas fa-file-export', 'relatorios.export', 'Baixe os dados em planilha', '#475569'],
            ],
        ],
        [
            'titulo' => 'Gerenciar',
            'cards' => [
                ['Projetos', 'fas fa-project-diagram', 'clockify.projetos.index', 'Cadastro e status dos projetos', '#0284c7'],
                ['Planejar Equipe', 'fas fa-users-cog', 'clockify.projetos.planejar-equipe-geral', 'Distribua a equipe nos projetos', '#4f46e5'],
                ['Clientes', 'fas fa-user-friends', 'clockify.clientes.index', 'Carteira de clientes', '#0891b2'],
                ['Equipe', 'fas fa-users', 'clockify.times.index', 'Times e membros', '#16a34a'],
                ['Folgas e Férias', 'fas fa-plane', 'clockify.timeoff.index', 'Férias e folgas da equipe', '#7c3aed'],
                ['Funções', 'fas fa-briefcase', 'clockify.funcoes.index', 'Funções e custo por hora', '#b45309'],
                ['Tags', 'fas fa-tags', 'clockify.tags.index', 'Etiquetas de classificação', '#db2777'],
                ['Usuários', 'fas fa-user-cog', 'usuario.index', 'Colaboradores e permissões', '#475569'],
            ],
        ],
        [
            'titulo' => 'Ajuda',
            'cards' => [
                ['Central de Ajuda', 'fas fa-question-circle', 'assinatura.ajuda', 'Dúvidas frequentes e guias', '#0891b2'],
            ],
        ],
    ];
?>

<div class="home-launcher">
    <?php $__currentLoopData = $secoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $secao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="home-section">
            <div class="home-section-title"><?php echo e($secao['titulo']); ?></div>
            <div class="row">
                <?php $__currentLoopData = $secao['cards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-6 col-md-4 col-lg-3 mb-2">
                        <a href="<?php echo e(route($card[2])); ?>" class="home-card" style="--card-cor: <?php echo e($card[4]); ?>;">
                            <span class="home-card-icone"><i class="<?php echo e($card[1]); ?>"></i></span>
                            <span class="home-card-texto">
                                <span class="home-card-nome"><?php echo e($card[0]); ?></span>
                                <p class="home-card-desc"><?php echo e($card[3]); ?></p>
                            </span>
                            <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('acessar-superadmin')): ?>
        <div class="home-section">
            <div class="home-section-title">Configurações</div>
            <div class="row">
                <div class="col-6 col-md-4 col-lg-3 mb-2">
                    <a href="<?php echo e(route('empresa.index')); ?>" class="home-card" style="--card-cor: #dc2626;">
                        <span class="home-card-icone"><i class="fas fa-building"></i></span>
                        <span class="home-card-texto">
                            <span class="home-card-nome">Empresas</span>
                            <p class="home-card-desc">Tenants cadastrados na plataforma</p>
                        </span>
                        <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3 mb-2">
                    <a href="<?php echo e(route('comandos.index')); ?>" class="home-card" style="--card-cor: #0f172a;">
                        <span class="home-card-icone"><i class="fas fa-terminal"></i></span>
                        <span class="home-card-texto">
                            <span class="home-card-nome">Comandos</span>
                            <p class="home-card-desc">Manutenção e comandos do sistema</p>
                        </span>
                        <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\home.blade.php ENDPATH**/ ?>