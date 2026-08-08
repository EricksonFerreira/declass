<div class="vs-panel" data-step-panel="documentacao">
    <div class="vs-panel-head">
        <div>
            <span class="vs-panel-head__eyebrow">Etapa 08</span>
            <h4 class="vs-panel-head__title">Documentação</h4>
            <p class="vs-panel-head__sub">IPVA, licenciamento e seguros organizados por tipo, com meses, vencimentos e status.</p>
        </div>
        <div class="vs-kpis">
            <div class="vs-kpi">
                <span>Tipos cadastrados</span>
                <strong><?php echo e($veiculo->documentacoesVeiculo->pluck('tipo')->unique()->count()); ?></strong>
            </div>
            <div class="vs-kpi">
                <span>Parcelas abertas</span>
                <strong><?php echo e($veiculo->documentacoesVeiculo->flatMap->parcelas->where('status', 'pendente')->count()); ?></strong>
            </div>
            <div class="vs-kpi">
                <span>Documentos cadastrados</span>
                <strong><?php echo e($veiculo->documentacoesVeiculo->count()); ?></strong>
            </div>
        </div>
    </div>

    <div class="vt-panel-actions">
        <a href="<?php echo e(route('veiculo.documentacao')); ?>" target="_blank" class="btn-xp btn-xp--sm">
            <i class="fas fa-file-alt mr-1"></i>Abrir documentação
        </a>
        <a href="<?php echo e(route('veiculo.documentacao.create', ['veiculo_id' => $veiculo->id])); ?>" target="_blank" class="btn-xp btn-xp--sm btn-xp--outline">
            <i class="fas fa-file-alt mr-1"></i>Adicionar documentação
        </a>
    </div>

    <?php
        $tipos = [
            'ipva' => 'IPVA',
            'licenciamento' => 'Licenciamento',
            'seguro' => 'Seguros',
        ];
        $documentacoesPorTipo = $veiculo->documentacoesVeiculo->groupBy('tipo');
    ?>

    <div class="doc-accordion">
        <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo => $rotulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $documentos = $documentacoesPorTipo->get($tipo, collect());
            ?>

            <div class="doc-group">
                <div class="doc-group__head">
                    <div>
                        <strong><?php echo e($rotulo); ?></strong>
                        <span><?php echo e($documentos->count()); ?> documento(s) cadastrados</span>
                    </div>

                    <?php if($documentos->isNotEmpty()): ?>
                        <span class="badge badge-success">Ativo</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Sem itens</span>
                    <?php endif; ?>
                </div>

                <div class="doc-group__body">
                    <?php $__empty_1 = true; $__currentLoopData = $documentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $collapseId = 'doc-op-' . $tipo . '-' . $doc->id;
                            $statusCount = $doc->parcelas->countBy('status');
                        ?>

                        <div class="doc-card">
                            <button class="doc-card__trigger" type="button" data-toggle="collapse" data-target="#<?php echo e($collapseId); ?>" aria-expanded="false" aria-controls="<?php echo e($collapseId); ?>">
                                <div class="doc-card__main">
                                    <div class="doc-card__title"><?php echo e($doc->descricao ?: $rotulo); ?></div>
                                    <div class="doc-card__meta">
                                        <?php echo e($doc->parcelas_total); ?> parcela(s) | Valor da parcela: R$ <?php echo e(number_format((float) $doc->valor_parcela, 2, ',', '.')); ?>

                                    </div>
                                </div>

                                <div class="doc-card__side">
                                    <span class="badge badge-<?php echo e($doc->status === 'ativo' ? 'success' : 'secondary'); ?>">
                                        <?php echo e(ucfirst($doc->status)); ?>

                                    </span>
                                    <i class="fas fa-chevron-down doc-card__icon"></i>
                                </div>
                            </button>

                            <div class="collapse" id="<?php echo e($collapseId); ?>">
                                <div class="doc-card__collapse">
                                    <div class="doc-card__summary">
                                        <span class="doc-pill">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            <?php echo e($statusCount->get('pendente', 0)); ?> pendente(s)
                                        </span>
                                        <span class="doc-pill">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            <?php echo e($statusCount->get('pago', 0)); ?> paga(s)
                                        </span>
                                        <span class="doc-pill">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            <?php echo e($statusCount->get('atrasado', 0)); ?> atrasada(s)
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 vehicle-subtable">
                                            <thead>
                                                <tr>
                                                    <th>Parcela</th>
                                                    <th>Mês</th>
                                                    <th>Vencimento</th>
                                                    <th>Pagamento</th>
                                                    <th>Status</th>
                                                    <th class="text-right">Ação</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__empty_2 = true; $__currentLoopData = $doc->parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parcela): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                    <?php
                                                        $parcelaStatusClass = [
                                                            'pago' => 'status-badge--green',
                                                            'atrasado' => 'status-badge--red',
                                                            'pendente' => 'status-badge--amber',
                                                        ][(string) $parcela->status] ?? 'status-badge--gray';
                                                    ?>
                                                    <tr>
                                                        <td class="font-weight-bold"><?php echo e($parcela->numero_parcela); ?>/<?php echo e($doc->parcelas_total); ?></td>
                                                        <td><?php echo e(optional($parcela->data_vencimento)->format('m/Y') ?? '-'); ?></td>
                                                        <td><?php echo e(optional($parcela->data_vencimento)->format('d/m/Y') ?? '-'); ?></td>
                                                        <td><?php echo e($parcela->data_pagamento ? $parcela->data_pagamento->format('d/m/Y') : '-'); ?></td>
                                                        <td>
                                                            <span class="status-badge <?php echo e($parcelaStatusClass); ?>">
                                                                <?php echo e(ucfirst((string) $parcela->status)); ?>

                                                            </span>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php if($parcela->status !== 'pago'): ?>
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-xs btn-xp btn-xp--sm btn-pagar-parcela-documentacao"
                                                                    data-toggle="modal"
                                                                    data-target="#modalPagarParcelaDocumentacao"
                                                                    data-action="<?php echo e(route('veiculo.documentacao.parcela.pagar', $parcela->id)); ?>"
                                                                    data-parcela-label="<?php echo e($doc->descricao ?: $rotulo); ?> - Parcela <?php echo e($parcela->numero_parcela); ?>/<?php echo e($doc->parcelas_total); ?>"
                                                                >
                                                                    <i class="fas fa-credit-card mr-1"></i>Pagar
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="text-muted small">Pago</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">Nenhuma parcela gerada.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-muted py-2">Nenhum item cadastrado.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if (! $__env->hasRenderedOnce('38b3a307-cc8d-4a78-af5f-1ee30b7df7a7')): $__env->markAsRenderedOnce('38b3a307-cc8d-4a78-af5f-1ee30b7df7a7'); ?>
        <style>
            .doc-accordion {
                display: grid;
                gap: 12px;
            }

            .doc-group {
                border: 1px solid #e2e9f4;
                border-radius: 16px;
                overflow: hidden;
                background: #fff;
                box-shadow: 0 2px 12px rgba(12, 20, 40, .05);
            }

            .doc-group__head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 16px;
                padding: 14px 16px;
                background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
                border-bottom: 1px solid #e7edf6;
            }

            .doc-group__head strong {
                display: block;
                color: #0c1428;
                font-size: .92rem;
                font-weight: 800;
            }

            .doc-group__head span {
                display: block;
                color: #64748b;
                font-size: .76rem;
                margin-top: 2px;
            }

            .doc-group__body {
                padding: 12px;
            }

            .doc-card {
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                overflow: hidden;
                background: #fff;
                margin-bottom: 10px;
            }

            .doc-card:last-child {
                margin-bottom: 0;
            }

            .doc-card__trigger {
                width: 100%;
                border: 0;
                background: #fff;
                padding: 14px 16px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                text-align: left;
            }

            .doc-card__main {
                min-width: 0;
            }

            .doc-card__title {
                font-weight: 800;
                color: #0f172a;
                font-size: .94rem;
            }

            .doc-card__meta {
                color: #64748b;
                font-size: .78rem;
                margin-top: 3px;
            }

            .doc-card__side {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-shrink: 0;
            }

            .doc-card__icon {
                color: #64748b;
                transition: transform .18s ease;
            }

            .doc-card__trigger[aria-expanded="true"] .doc-card__icon {
                transform: rotate(180deg);
            }

            .doc-card__collapse {
                padding: 0 16px 16px;
                border-top: 1px solid #eef2f7;
                background: #f8fafc;
            }

            .doc-card__summary {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                padding: 14px 0 12px;
            }

            .doc-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 7px 11px;
                border-radius: 999px;
                background: #fff;
                border: 1px solid #dbe4f0;
                color: #1e293b;
                font-size: .76rem;
                font-weight: 700;
            }

            .vehicle-subtable thead th {
                font-size: .72rem;
                text-transform: uppercase;
                letter-spacing: .06em;
                color: #475569;
                background: #fff;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                padding: 4px 10px;
                border-radius: 999px;
                font-size: .73rem;
                font-weight: 700;
                letter-spacing: .02em;
            }

            .status-badge--green { background: #dcfce7; color: #166534; }
            .status-badge--amber { background: #fef3c7; color: #92400e; }
            .status-badge--red { background: #fee2e2; color: #991b1b; }
            .status-badge--gray { background: #f1f5f9; color: #475569; }
        </style>
    <?php endif; ?>

    <div class="modal fade" id="modalPagarParcelaDocumentacao" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content vehicle-modal vehicle-modal--alert">
                <form id="formPagarParcelaDocumentacao" method="POST" action="">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header vehicle-modal__header border-bottom-0">
                        <div class="d-flex align-items-start">
                            <div class="vehicle-modal__icon mr-3">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div>
                                <div class="vehicle-modal__eyebrow">Documentação do veículo</div>
                                <h5 class="modal-title mb-1 vehicle-modal__title">Registrar pagamento</h5>
                                <small class="vehicle-modal__subtitle" id="modalPagarParcelaDocumentacaoLabel">Confirme a data do pagamento da parcela.</small>
                            </div>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body vehicle-modal__body">
                        <div class="form-group mb-0">
                            <label for="modal_data_pagamento_documentacao" class="vehicle-modal__label">Data do pagamento</label>
                            <input type="date" class="form-control vehicle-modal__control" id="modal_data_pagamento_documentacao" name="data_pagamento" value="<?php echo e(now()->toDateString()); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer vehicle-modal__footer border-top-0 d-flex justify-content-between">
                        <button type="button" class="btn btn-xp btn-xp--ghost btn-xp--sm" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-xp btn-xp--sm">
                            <i class="fas fa-check mr-1"></i> Confirmar pagamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (! $__env->hasRenderedOnce('b8dbd2a7-4ddc-427a-a08d-54b26034c257')): $__env->markAsRenderedOnce('b8dbd2a7-4ddc-427a-a08d-54b26034c257'); ?>
        <script>
            (function () {
                function bindPagamentoDocumentacaoModal() {
                    var form = document.getElementById('formPagarParcelaDocumentacao');
                    var inputData = document.getElementById('modal_data_pagamento_documentacao');
                    var label = document.getElementById('modalPagarParcelaDocumentacaoLabel');
                    var modal = document.getElementById('modalPagarParcelaDocumentacao');

                    if (!form || !inputData || !label) {
                        return;
                    }

                    document.querySelectorAll('.btn-pagar-parcela-documentacao').forEach(function (button) {
                        button.addEventListener('click', function () {
                            var action = button.getAttribute('data-action');
                            var parcelaLabel = button.getAttribute('data-parcela-label') || 'Parcela';

                            if (action) {
                                form.setAttribute('action', action);
                            }

                            inputData.value = new Date().toISOString().slice(0, 10);
                            label.textContent = parcelaLabel + '. Informe a data do pagamento.';
                        });
                    });

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();

                        var submitButton = form.querySelector('button[type="submit"]');
                        var originalHtml = submitButton ? submitButton.innerHTML : '';

                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';
                        }

                        fetch(form.getAttribute('action'), {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            },
                            body: new FormData(form)
                        })
                            .then(function (response) {
                                return response.json().then(function (payload) {
                                    return { ok: response.ok, payload: payload };
                                });
                            })
                            .then(function (result) {
                                if (!result.ok || !result.payload || !result.payload.success) {
                                    throw new Error(result.payload?.message || 'Não foi possível registrar o pagamento.');
                                }

                                if (window.jQuery && modal && $('#modalPagarParcelaDocumentacao').modal) {
                                    $('#modalPagarParcelaDocumentacao').modal('hide');
                                }

                                var message = result.payload.message || 'Pagamento registrado com sucesso.';

                                if (window.Swal && typeof window.Swal.fire === 'function') {
                                    window.Swal.fire({
                                        icon: 'success',
                                        title: 'Sucesso',
                                        text: message,
                                        confirmButtonText: 'Recarregar página',
                                        allowOutsideClick: false
                                    }).then(function () {
                                        window.location.reload();
                                    });
                                } else {
                                    window.location.reload();
                                }
                            })
                            .catch(function (error) {
                                if (window.Swal && typeof window.Swal.fire === 'function') {
                                    window.Swal.fire({
                                        icon: 'error',
                                        title: 'Erro',
                                        text: error.message || 'Falha ao registrar o pagamento.'
                                    });
                                } else {
                                    alert(error.message || 'Falha ao registrar o pagamento.');
                                }
                            })
                            .finally(function () {
                                if (submitButton) {
                                    submitButton.disabled = false;
                                    submitButton.innerHTML = originalHtml;
                                }
                            });
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bindPagamentoDocumentacaoModal);
                } else {
                    bindPagamentoDocumentacaoModal();
                }
            })();
        </script>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/components/operacional/etapas/documentacao.blade.php ENDPATH**/ ?>