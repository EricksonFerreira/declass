<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'modalId',
    'title',
    'buscarUrl',
    'triggerId' => null,
    'targetSelectId' => null,
    'targetDisplayId' => null,
    'emptyText' => 'Nenhum registro encontrado.',
    'placeholder' => 'Digite para pesquisar...',
    'extraParams' => [],
    'afterSelect' => null,
    'showMarcaColumn' => false,
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
    'modalId',
    'title',
    'buscarUrl',
    'triggerId' => null,
    'targetSelectId' => null,
    'targetDisplayId' => null,
    'emptyText' => 'Nenhum registro encontrado.',
    'placeholder' => 'Digite para pesquisar...',
    'extraParams' => [],
    'afterSelect' => null,
    'showMarcaColumn' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="modal fade vc-entity-search-modal" id="<?php echo e($modalId); ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo e($modalId); ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="<?php echo e($modalId); ?>Label">
                    <i class="fas fa-search mr-2"></i><?php echo e($title); ?>

                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-filter mr-1"></i> Pesquisa</h6>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body pb-2">
                        <div class="row">
                            <div class="col-md-8 form-group mb-2">
                                <label class="small mb-1">Nome</label>
                                <input type="text" id="<?php echo e($modalId); ?>_q" class="form-control form-control-sm" placeholder="<?php echo e($placeholder); ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end form-group mb-2">
                                <button type="button" class="btn btn-primary btn-sm btn-block" id="<?php echo e($modalId); ?>_btnBuscar">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end form-group mb-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="<?php echo e($modalId); ?>_btnLimpar">
                                    <i class="fas fa-eraser mr-1"></i> Limpar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="<?php echo e($modalId); ?>_loading" class="text-center d-none py-3">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Buscando...
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 90px;">ID</th>
                                <th>Nome</th>
                                <?php if($showMarcaColumn): ?>
                                    <th>Marca</th>
                                <?php endif; ?>
                                <th>Detalhes</th>
                                <th style="width: 120px;" class="text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="<?php echo e($modalId); ?>_lista">
                            <tr>
                                <td colspan="<?php echo e($showMarcaColumn ? 5 : 4); ?>" class="text-muted text-center py-4">
                                    Use a busca para encontrar um registro.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="<?php echo e($modalId); ?>_vazio" class="text-center text-muted py-4 d-none">
                    <?php echo e($emptyText); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('43f9fdd8-1049-471a-8059-5217b0d21a1b')): $__env->markAsRenderedOnce('43f9fdd8-1049-471a-8059-5217b0d21a1b'); ?>
    <script>
        window.VeiculoBuscaModalGenerico = window.VeiculoBuscaModalGenerico || (function () {
            const configuracoes = {};

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function getConfig(modalId) {
                return configuracoes[modalId] || null;
            }

            function getParams(config) {
                const params = {
                    q: (document.getElementById(config.modalId + '_q')?.value || '').trim(),
                };

                (config.extraParams || []).forEach(function (item) {
                    if (!item || !item.name || !item.selector) {
                        return;
                    }

                    const field = document.querySelector(item.selector);
                    const value = field ? String(field.value || '').trim() : '';
                    if (value !== '') {
                        params[item.name] = value;
                    }
                });

                return params;
            }

            function renderRows(config, items) {
                const tbody = document.getElementById(config.modalId + '_lista');
                const vazio = document.getElementById(config.modalId + '_vazio');

                if (!tbody) {
                    return;
                }

                config.items = Array.isArray(items) ? items : [];

                if (!config.items.length) {
                    tbody.innerHTML = '';
                    if (vazio) {
                        vazio.classList.remove('d-none');
                    }
                    return;
                }

                if (vazio) {
                    vazio.classList.add('d-none');
                }

                tbody.innerHTML = config.items.map(function (item, index) {
                    const extra = item.extra ? escapeHtml(item.extra) : '<span class="text-muted">—</span>';
                    const marca = item.marca_nome ? escapeHtml(item.marca_nome) : '<span class="text-muted">—</span>';

                    return `
                        <tr>
                            <td class="align-middle">${escapeHtml(item.id)}</td>
                            <td class="align-middle font-weight-semibold">${escapeHtml(item.nome)}</td>
                            ${config.showMarcaColumn ? `<td class="align-middle">${marca}</td>` : ''}
                            <td class="align-middle">${extra}</td>
                            <td class="align-middle text-right">
                                <button type="button" class="btn btn-primary btn-sm btn-selecionar-entidade" data-index="${index}">
                                    <i class="fas fa-check mr-1"></i> Selecionar
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            function setLoading(modalId, loading) {
                const loadingEl = document.getElementById(modalId + '_loading');
                const lista = document.getElementById(modalId + '_lista');
                if (loadingEl) {
                    loadingEl.classList.toggle('d-none', !loading);
                }
                if (lista) {
                    lista.classList.toggle('opacity-50', loading);
                }
            }

            function selecionar(config, item) {
                if (!item) {
                    return;
                }

                if (config.targetSelectId) {
                    const hidden = document.getElementById(config.targetSelectId);
                    if (hidden) {
                        hidden.value = item.id;
                        try {
                            $(hidden).trigger('change');
                        } catch (e) {
                            hidden.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                }

                if (config.targetDisplayId) {
                    const display = document.getElementById(config.targetDisplayId);
                    if (display) {
                        display.value = item.nome || '';
                    }
                }

                if (config.afterSelect && typeof window[config.afterSelect] === 'function') {
                    window[config.afterSelect](item, config);
                }

                $('#' + config.modalId).modal('hide');
            }

            function buscar(modalId) {
                const config = getConfig(modalId);
                if (!config) {
                    return;
                }

                setLoading(modalId, true);

                $.get(config.buscarUrl, getParams(config))
                    .done(function (resp) {
                        renderRows(config, resp.items || resp.veiculos || []);
                    })
                    .fail(function () {
                        renderRows(config, []);
                    })
                    .always(function () {
                        setLoading(modalId, false);
                    });
            }

            return {
                init: function (config) {
                    configuracoes[config.modalId] = config;

                    const $modal = $('#' + config.modalId);
                    const $btnBuscar = $('#' + config.modalId + '_btnBuscar');
                    const $btnLimpar = $('#' + config.modalId + '_btnLimpar');
                    const $campoBusca = $('#' + config.modalId + '_q');

                    if (config.triggerId) {
                        $('#' + config.triggerId).on('click', function () {
                            $modal.modal('show');
                        });
                    }

                    $modal.on('shown.bs.modal', function () {
                        $campoBusca.trigger('focus');
                        buscar(config.modalId);
                    });

                    $btnBuscar.on('click', function () {
                        buscar(config.modalId);
                    });

                    $btnLimpar.on('click', function () {
                        $campoBusca.val('');
                        buscar(config.modalId);
                    });

                    $campoBusca.on('keydown', function (event) {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            buscar(config.modalId);
                        }
                    });

                    $modal.on('click', '.btn-selecionar-entidade', function () {
                        const index = Number($(this).data('index'));
                        const cfg = getConfig(config.modalId);
                        const item = cfg?.items?.[index];
                        selecionar(cfg, item);
                    });
                },
                buscar: buscar,
            };
        })();
    </script>
<?php endif; ?>

<?php if($triggerId): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.VeiculoBuscaModalGenerico) {
                return;
            }

            window.VeiculoBuscaModalGenerico.init({
                modalId: <?php echo json_encode($modalId, 15, 512) ?>,
                title: <?php echo json_encode($title, 15, 512) ?>,
                buscarUrl: <?php echo json_encode($buscarUrl, 15, 512) ?>,
                triggerId: <?php echo json_encode($triggerId, 15, 512) ?>,
                targetSelectId: <?php echo json_encode($targetSelectId, 15, 512) ?>,
                targetDisplayId: <?php echo json_encode($targetDisplayId, 15, 512) ?>,
                emptyText: <?php echo json_encode($emptyText, 15, 512) ?>,
                placeholder: <?php echo json_encode($placeholder, 15, 512) ?>,
                extraParams: <?php echo json_encode($extraParams, 15, 512) ?>,
                afterSelect: <?php echo json_encode($afterSelect, 15, 512) ?>,
                showMarcaColumn: <?php echo json_encode($showMarcaColumn, 15, 512) ?>,
            });
        });
    </script>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/veiculo/busca-entidade-modal.blade.php ENDPATH**/ ?>