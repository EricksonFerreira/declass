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
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo,'subtitle' => $subtitulo] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

<?php $__env->startSection('content'); ?>
    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm" style="border-radius:12px; border: 1px solid #e2e8f0;">
                <div class="card-body p-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text" id="faq-search" class="form-control border-left-0" placeholder="Digite sua dúvida para pesquisar em todos os módulos..." style="border-radius: 0 8px 8px 0; border-color: #cbd5e1; height: 48px; font-size: 1.05rem;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grouped FAQs Container -->
    <div class="row">
        <!-- Categories Sidebar -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm" style="border-radius:12px; overflow:hidden; border: 1px solid #e2e8f0;">
                <div class="card-header bg-light py-3">
                    <h6 class="font-weight-bold text-dark m-0">
                        <i class="fas fa-list mr-2"></i> Módulos do Sistema
                    </h6>
                </div>
                <div class="list-group list-group-flush" id="faq-categories-tab" role="tablist">
                    <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $catSlug = Str::slug($category); ?>
                        <a class="list-group-item list-group-item-action <?php if($loop->first): ?> active <?php endif; ?> d-flex align-items-center justify-content-between py-3" 
                           id="cat-<?php echo e($catSlug); ?>-tab" data-toggle="pill" href="#cat-<?php echo e($catSlug); ?>" 
                           role="tab" aria-controls="cat-<?php echo e($catSlug); ?>" aria-selected="<?php echo e($loop->first ? 'true' : 'false'); ?>" style="gap: 10px;">
                            <span>
                                <i class="fas fa-folder mr-2 text-secondary"></i>
                                <strong><?php echo e($category); ?></strong>
                            </span>
                            <span class="badge badge-secondary badge-pill cat-badge" data-initial-count="<?php echo e(count($items)); ?>"><?php echo e(count($items)); ?></span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <!-- Category Content -->
        <div class="col-md-9">
            <div class="tab-content" id="faq-categories-content">
                <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $catSlug = Str::slug($category); ?>
                    <div class="tab-pane fade <?php if($loop->first): ?> show active <?php endif; ?>" id="cat-<?php echo e($catSlug); ?>" role="tabpanel" aria-labelledby="cat-<?php echo e($catSlug); ?>-tab">
                        <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
                            <div class="card-header py-3 bg-white border-bottom">
                                <h5 class="font-weight-bold text-dark m-0">
                                    <i class="fas fa-book-open mr-2 text-blue"></i>
                                    <?php echo e($category); ?>

                                </h5>
                            </div>
                            <div class="card-body p-3 bg-light">
                                <div class="accordion" id="accordion-<?php echo e($catSlug); ?>">
                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $collapseId = $item['id'] ?? ('faq-' . $catSlug . '-' . $j);
                                            $headingId  = $collapseId . '-h';
                                        ?>
                                        <div class="card mb-2 border-0 shadow-sm faq-item-card" style="border-radius:10px; overflow:hidden;">
                                            <div class="card-header py-3 border-0" id="<?php echo e($headingId); ?>" style="background:#ffffff;">
                                                <button class="btn btn-link p-0 text-left w-100 d-flex align-items-center justify-content-between"
                                                        type="button" data-toggle="collapse" data-target="#<?php echo e($collapseId); ?>"
                                                        aria-expanded="false" aria-controls="<?php echo e($collapseId); ?>">
                                                    <span class="font-weight-bold text-dark" style="font-size: 0.95rem; line-height: 1.4;">
                                                        <i class="fas fa-question-circle mr-2 text-primary"></i><?php echo e($item['q']); ?>

                                                    </span>
                                                    <i class="fas fa-chevron-down text-muted btn-chevron" style="transition: transform 0.2s;"></i>
                                                </button>
                                            </div>
                                            <div id="<?php echo e($collapseId); ?>" class="collapse faq-collapse"
                                                 aria-labelledby="<?php echo e($headingId); ?>" data-parent="#accordion-<?php echo e($catSlug); ?>">
                                                <div class="card-body border-top bg-light text-secondary">
                                                    <div class="markdown-body"><?php echo $item['aHtml']; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            
            <!-- No Results Alert -->
            <div id="no-results-alert" class="alert alert-warning text-center shadow-sm p-4 d-none" style="border-radius: 12px;">
                <i class="fas fa-search mr-2 style='font-size: 1.5rem;'"></i>
                <h5 class="font-weight-bold mt-2">Nenhum resultado encontrado</h5>
                <p class="text-muted mb-0">Tente buscar por termos mais genéricos ou palavras diferentes.</p>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
    <style>
        #faq-categories-tab .list-group-item.active {
            background-color: var(--primary, #007bff);
            border-color: var(--primary, #007bff);
            color: #fff !important;
        }
        #faq-categories-tab .list-group-item.active i {
            color: #fff !important;
        }
        .markdown-body h1 { font-size: 1.35rem; margin-top: 0; }
        .markdown-body h2 { font-size: 1.1rem; margin-top: 1.15rem; }
        .markdown-body h3 { font-size: 1.0rem; margin-top: 1rem; }
        .markdown-body pre { background: #0b1220; color: #e2e8f0; padding: .75rem; border-radius: .5rem; }
        .markdown-body code { background: rgba(15, 23, 42, .06); padding: .1rem .25rem; border-radius: .25rem; }
        .markdown-body table { width: 100%; margin-bottom: 1rem; }
        .markdown-body table th, .markdown-body table td { border: 1px solid #e5e7eb; padding: .4rem .5rem; }
        .markdown-body blockquote { border-left: 3px solid #cbd5e1; padding-left: .75rem; color: #475569; }
        .markdown-body hr { border-top: 1px solid #e5e7eb; }
        
        /* Rotates chevron when opened */
        .btn[aria-expanded="true"] .btn-chevron {
            transform: rotate(180deg);
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('faq-search');
            const noResultsAlert = document.getElementById('no-results-alert');
            const faqCards = document.querySelectorAll('.faq-item-card');
            const categoryLinks = document.querySelectorAll('#faq-categories-tab .list-group-item');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            // Keep track of which category tab was active before searching
            let lastActiveTabId = document.querySelector('#faq-categories-tab .list-group-item.active').getAttribute('href');

            // Store original tabs click behavior to update lastActiveTabId
            $('#faq-categories-tab a').on('click', function() {
                if (searchInput.value.trim() === '') {
                    lastActiveTabId = $(this).attr('href');
                }
            });

            searchInput.addEventListener('input', function() {
                const query = searchInput.value.toLowerCase().trim();
                
                if (query === '') {
                    // Hide no results alert
                    noResultsAlert.classList.add('d-none');
                    
                    // Show all cards, restore collapse
                    faqCards.forEach(card => {
                        card.style.display = '';
                        const collapse = card.querySelector('.faq-collapse');
                        if (collapse) {
                            $(collapse).collapse('hide');
                        }
                    });
                    
                    // Show all category links and reset counts
                    categoryLinks.forEach(link => {
                        link.style.display = '';
                        const badge = link.querySelector('.cat-badge');
                        if (badge) {
                            badge.textContent = badge.getAttribute('data-initial-count');
                            badge.className = 'badge badge-secondary badge-pill cat-badge';
                        }
                    });
                    
                    // Restore only the last active tab pane
                    tabPanes.forEach(pane => {
                        if ('#' + pane.id === lastActiveTabId) {
                            pane.classList.add('show', 'active');
                        } else {
                            pane.classList.remove('show', 'active');
                        }
                    });
                    
                    // Set active link visually
                    categoryLinks.forEach(link => {
                        if (link.getAttribute('href') === lastActiveTabId) {
                            link.classList.add('active');
                        } else {
                            link.classList.remove('active');
                        }
                    });
                    
                    return;
                }
                
                // Track total matches
                let totalMatches = 0;
                const matchesPerCategory = {};
                
                faqCards.forEach(card => {
                    const questionText = card.querySelector('button span').textContent.toLowerCase();
                    const answerText = card.querySelector('.markdown-body').textContent.toLowerCase();
                    const parentPane = card.closest('.tab-pane');
                    const categoryId = parentPane.id;
                    
                    if (!matchesPerCategory[categoryId]) {
                        matchesPerCategory[categoryId] = 0;
                    }
                    
                    if (questionText.includes(query) || answerText.includes(query)) {
                        card.style.display = '';
                        matchesPerCategory[categoryId]++;
                        totalMatches++;
                        
                        // Auto expand matching cards
                        const collapse = card.querySelector('.faq-collapse');
                        if (collapse) {
                            $(collapse).collapse('show');
                        }
                    } else {
                        card.style.display = 'none';
                        const collapse = card.querySelector('.faq-collapse');
                        if (collapse) {
                            $(collapse).collapse('hide');
                        }
                    }
                });
                
                // Display "No Results" alert if totalMatches is 0
                if (totalMatches === 0) {
                    noResultsAlert.classList.remove('d-none');
                } else {
                    noResultsAlert.classList.add('d-none');
                }
                
                // Update tabs list
                categoryLinks.forEach(link => {
                    const targetPaneId = link.getAttribute('href').replace('#', '');
                    const matchCount = matchesPerCategory[targetPaneId] || 0;
                    const badge = link.querySelector('.cat-badge');
                    
                    if (matchCount > 0) {
                        link.style.display = '';
                        if (badge) {
                            badge.textContent = matchCount;
                            badge.className = 'badge badge-primary badge-pill cat-badge';
                        }
                    } else {
                        link.style.display = 'none';
                    }
                });
                
                // Show all panes that contain search matches, hide others
                let firstVisiblePaneId = null;
                tabPanes.forEach(pane => {
                    const matchCount = matchesPerCategory[pane.id] || 0;
                    if (matchCount > 0) {
                        pane.classList.add('show', 'active');
                        if (!firstVisiblePaneId) {
                            firstVisiblePaneId = pane.id;
                        }
                    } else {
                        pane.classList.remove('show', 'active');
                    }
                });

                // Maintain correct visual selection state for the tabs sidebar
                categoryLinks.forEach(link => {
                    const targetPaneId = link.getAttribute('href').replace('#', '');
                    if (targetPaneId === firstVisiblePaneId) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/assinatura/ajuda.blade.php ENDPATH**/ ?>