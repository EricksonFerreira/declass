

<?php $__env->startSection('title', 'Assinatura - ' . $assinatura->empresa->nome); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => [
        new \App\DTO\BreadCrumbItem('Home', route('home')),
        new \App\DTO\BreadCrumbItem('Assinaturas', route('assinatura.index')),
        new \App\DTO\BreadCrumbItem($assinatura->empresa->nome),
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    <div class="container-fluid px-0">
        <div class="module-card mb-3">
            <div class="module-card-header">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Assinatura &bull; Detalhes</div>
                        <h1 class="module-header-title"><?php echo e($assinatura->empresa->nome); ?></h1>
                        <p class="module-header-sub">
                            <span class="badge <?php echo e($assinatura->status_badge['class']); ?> mr-1"><?php echo e($assinatura->status_badge['label']); ?></span>
                            Plano <strong><?php echo e($assinatura->plano->nome); ?></strong> &bull; <?php echo e($assinatura->periodicidade_texto); ?>

                        </p>
                    </div>
                </div>
                <div class="module-header-right">
                    <a href="<?php echo e(route('assinatura.index')); ?>" class="btn-xp btn-xp--outline-light btn-xp--sm">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-12 mb-3">
                <div class="card card-outline card-info h-100">
                    <div class="card-body">
                        <div class="text-muted text-uppercase small font-weight-bold mb-1">Projetos</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="h2 mb-0 font-weight-bold"><?php echo e(number_format($qtdProjetos ?? 0, 0, ',', '.')); ?></div>
                                <small class="text-muted">cadastrados na empresa</small>
                            </div>
                            <i class="fas fa-folder-open text-info" style="font-size:2rem; opacity:.2;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-12 mb-3">
                <div class="card card-outline card-success h-100">
                    <div class="card-body">
                        <div class="text-muted text-uppercase small font-weight-bold mb-1">Usuários</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="h2 mb-0 font-weight-bold"><?php echo e(number_format($qtdUsuarios ?? 0, 0, ',', '.')); ?></div>
                                <small class="text-muted">na equipe</small>
                                <div class="small text-success font-weight-bold mt-1">
                                    Plano permite: <?php echo e($assinatura->plano->limite_usuarios_texto); ?>

                                </div>
                            </div>
                            <i class="fas fa-users text-success" style="font-size:2rem; opacity:.2;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-12 mb-3">
                <div class="card card-outline card-warning h-100">
                    <div class="card-body">
                        <div class="text-muted text-uppercase small font-weight-bold mb-1">Armazenamento</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="h4 mb-0 font-weight-bold"><?php echo e($armazenamentoUsadoTexto ?? '0 B'); ?></div>
                                <small class="text-muted">de <?php echo e($armazenamentoLimiteTexto ?? '0 B'); ?></small>
                            </div>
                            <i class="fas fa-database text-warning" style="font-size:2rem; opacity:.2;"></i>
                        </div>
                        <div class="progress mt-2" style="height:8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo e((int) ($armazenamentoPercentual ?? 0)); ?>%;" aria-valuenow="<?php echo e((int) ($armazenamentoPercentual ?? 0)); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted d-block mt-1"><?php echo e((int) ($armazenamentoPercentual ?? 0)); ?>% utilizado</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-id-card mr-1"></i> Dados da assinatura</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted pl-3">Plano</td><td class="pr-3 font-weight-bold"><?php echo e($assinatura->plano->nome); ?></td></tr>
                            <tr><td class="text-muted pl-3">Periodicidade</td><td class="pr-3"><?php echo e($assinatura->periodicidade_texto); ?></td></tr>
                            <tr><td class="text-muted pl-3">Valor</td><td class="pr-3 font-weight-bold">R$ <?php echo e(number_format($assinatura->valor_cobrado, 2, ',', '.')); ?></td></tr>
                            <?php
                                $storageMb = $assinatura->plano->limite_armazenamento_mb ?? 0;
                                $storageTxt = $storageMb >= 1024 ? number_format($storageMb / 1024, 1, ',', '.') . ' GB' : $storageMb . ' MB';
                            ?>
                            <tr><td class="text-muted pl-3">Armazenamento</td><td class="pr-3"><?php echo e($storageTxt); ?></td></tr>
                            <tr><td class="text-muted pl-3">Método</td><td class="pr-3 text-capitalize"><?php echo e(str_replace('_', ' ', $assinatura->metodo_cobranca)); ?></td></tr>
                            <tr><td class="text-muted pl-3">Início</td><td class="pr-3"><?php echo e($assinatura->data_inicio->format('d/m/Y')); ?></td></tr>
                            <tr><td class="text-muted pl-3">Vencimento</td><td class="pr-3"><?php echo e($assinatura->data_vencimento->format('d/m/Y')); ?></td></tr>
                            <tr><td class="text-muted pl-3">Criado por</td><td class="pr-3"><?php echo e($assinatura->criadoPor?->nome ?? '—'); ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-outline card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="fas fa-receipt mr-1"></i> Pagamentos</h3>
                        <div class="d-flex" style="gap:6px;">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalPagamento" onclick="abrirNovoPagamento()">
                                <i class="fas fa-plus mr-1"></i> Registrar pagamentos
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="pl-3">Vencimento</th>
                                    <th>Método</th>
                                    <th class="text-right">Valor</th>
                                    <th class="text-center">Status</th>
                                    <th>Pago em</th>
                                    <th>Ref.</th>
                                    <th>Observação</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $assinatura->pagamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="pl-3 align-middle"><?php echo e($pag->data_vencimento->format('d/m/Y')); ?></td>
                                        <td class="align-middle"><?php echo e($pag->metodo_texto); ?></td>
                                        <td class="align-middle text-right font-weight-bold">R$ <?php echo e(number_format($pag->valor, 2, ',', '.')); ?></td>
                                        <td class="align-middle text-center">
                                            <span class="badge <?php echo e($pag->status_badge['class']); ?>"><?php echo e($pag->status_badge['label']); ?></span>
                                        </td>
                                        <td class="align-middle text-muted"><?php echo e($pag->pago_em ? $pag->pago_em->format('d/m/Y H:i') : '—'); ?></td>
                                        <td class="align-middle text-muted"><?php echo e($pag->referencia_interna ?? '—'); ?></td>
                                        <td class="align-middle text-muted" style="max-width:220px;">
                                            <span class="d-inline-block text-truncate" style="max-width:220px;" title="<?php echo e(data_get($pag->metadados, 'observacao') ?? '—'); ?>">
                                                <?php echo e(data_get($pag->metadados, 'observacao') ?? '—'); ?>

                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button
                                                type="button"
                                                class="btn btn-xs btn-outline-secondary mr-1 mb-1"
                                                data-id="<?php echo e($pag->id); ?>"
                                                data-action="<?php echo e(route('assinatura.pagamento.atualizar', $pag)); ?>"
                                                data-valor="<?php echo e($pag->valor); ?>"
                                                data-metodo="<?php echo e($pag->metodo); ?>"
                                                data-vencimento="<?php echo e($pag->data_vencimento->format('Y-m-d')); ?>"
                                                data-status="<?php echo e($pag->status); ?>"
                                                data-pago-em="<?php echo e($pag->pago_em?->format('Y-m-d') ?? ''); ?>"
                                                data-gateway-payment-id="<?php echo e($pag->gateway_payment_id); ?>"
                                                data-observacao="<?php echo e(e(data_get($pag->metadados, 'observacao'))); ?>"
                                                onclick="abrirEditar(this); return false;"
                                            >
                                                <i class="fas fa-pen"></i> Editar
                                            </button>
                                            <?php if($pag->status !== 'pago'): ?>
                                                <form action="<?php echo e(route('assinatura.pagamento.excluir', $pag)); ?>" method="POST" class="d-inline js-form-excluir-pagamento">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-xs btn-outline-danger js-btn-excluir-pagamento mb-1">
                                                        <i class="fas fa-trash"></i> Remover
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Nenhum pagamento registrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPagamento" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="formPagamento" action="<?php echo e(route('assinatura.pagamento.registrar', $assinatura)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" id="formPagamentoMethod" value="">
                    <input type="hidden" name="pagamento_id" id="pagamentoId" value="">
                    <div class="modal-header bg-dark">
                        <h5 class="modal-title text-white"><i class="fas fa-money-bill mr-1"></i> Registrar pagamentos</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border small mb-3 py-2 px-3">
                            <strong>Fluxo rápido:</strong> gere várias parcelas pendentes de uma vez. Cada linha pode ter valor, vencimento e método próprios.
                        </div>

                        <div class="row align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="font-weight-bold mb-1">Parcelas</label>
                                <input type="number" id="qtdParcelas" class="form-control" min="1" value="1">
                            </div>
                            <div class="col-md-3">
                                <label class="font-weight-bold mb-1">Vencimento inicial</label>
                                <input type="date" id="dataInicialParcelas" class="form-control" value="<?php echo e(now()->addMonth()->toDateString()); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="font-weight-bold mb-1">Valor base</label>
                                <input type="number" id="valorBaseParcelas" class="form-control" step="0.01" min="0.01" value="<?php echo e($assinatura->valor_cobrado); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-primary btn-block" id="btnGerarParcelas">
                                    <i class="fas fa-sync-alt mr-1"></i>Gerar linhas
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0" id="parcelasPreview">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:70px;">#</th>
                                        <th>Vencimento</th>
                                        <th>Valor</th>
                                        <th>Método</th>
                                        <th>Status</th>
                                        <th>Pago em</th>
                                        <th>Obs.</th>
                                        <th style="width:60px;" class="text-center">Remover</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Clique em "Gerar linhas" para começar.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success mr-auto" id="btnAddLinha">
                            <i class="fas fa-plus mr-1"></i>Adicionar linha
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarPagamento" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formEditarPagamento" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <div class="modal-header bg-dark">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-pen mr-1"></i> Editar pagamento
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Valor (R$)</label>
                                    <input type="number" name="valor" step="0.01" min="0.01" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Método</label>
                                    <select name="metodo" class="form-control" required>
                                        <option value="boleto">Boleto</option>
                                        <option value="pix">PIX</option>
                                        <option value="cartao">Cartão</option>
                                        <option value="dinheiro">Dinheiro</option>
                                        <option value="transferencia">Transferência</option>
                                        <option value="manual">Manual</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Status</label>
                                    <select name="status" class="form-control js-status-pagamento" required>
                                        <option value="pendente">Pendente</option>
                                        <option value="pago">Pago</option>
                                        <option value="vencido">Vencido</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Vencimento</label>
                                    <input type="date" name="data_vencimento" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Pago em</label>
                                    <input type="date" name="pago_em" class="form-control js-pago-em" disabled>
                                    <small class="text-muted js-pago-em-help">Obrigatório quando o status for Pago.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Comprovante / Gateway</label>
                                    <input type="text" name="gateway_payment_id" class="form-control">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Observação</label>
                                    <input type="text" name="observacao" class="form-control" maxlength="500" placeholder="Opcional">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Salvar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('.js-form-excluir-pagamento');

            forms.forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    if (!window.Swal || typeof window.Swal.fire !== 'function') {
                        if (window.confirm('Remover este pagamento?')) {
                            form.submit();
                        }
                        return;
                    }

                    window.Swal.fire({
                        title: 'Remover pagamento?',
                        text: 'Esta ação não pode ser desfeita.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, remover',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });

        function addMonths(date, months) {
            const d = new Date(date.getTime());
            const day = d.getDate();
            d.setMonth(d.getMonth() + months);
            if (d.getDate() !== day) {
                d.setDate(0);
            }
            return d;
        }

        function toInputDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function buildRow(index, total, vencimento, valor, dados = {}) {
            return `
                <tr>
                    <td class="align-middle text-center">${index + 1}/${total}</td>
                    <td><input type="date" name="parcelas[${index}][data_vencimento]" class="form-control form-control-sm" value="${vencimento}" required></td>
                    <td><input type="number" name="parcelas[${index}][valor]" class="form-control form-control-sm" step="0.01" min="0.01" value="${valor}" required></td>
                    <td>
                        <select name="parcelas[${index}][metodo]" class="form-control form-control-sm" required>
                            <option value="boleto">Boleto</option>
                            <option value="pix">PIX</option>
                            <option value="cartao">Cartão</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="transferencia">Transferência</option>
                            <option value="manual">Manual</option>
                        </select>
                    </td>
                    <td>
                        <select name="parcelas[${index}][status]" class="form-control form-control-sm js-status-parcela" required>
                            <option value="pendente" selected>Pendente</option>
                            <option value="pago">Pago</option>
                            <option value="vencido">Vencido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </td>
                    <td>
                        <input type="date" name="parcelas[${index}][pago_em]" class="form-control form-control-sm js-pago-em-parcela" value="${dados.pago_em || ''}" disabled>
                    </td>
                    <td><input type="text" name="parcelas[${index}][observacao]" class="form-control form-control-sm" value="${dados.observacao || ''}" placeholder="Opcional"></td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-xs btn-outline-danger js-remove-row">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
        }

        function preencherModal(dados, titulo, action, method) {
            const form = document.getElementById('formPagamento');
            if (!form) return;

            const payload = dados || {};
            document.querySelector('#modalPagamento .modal-title').innerHTML = '<i class="fas fa-money-bill mr-1"></i> ' + (titulo || 'Registrar pagamentos');
            form.action = action || <?php echo json_encode(route('assinatura.pagamento.registrar', $assinatura), 512) ?>;
            document.getElementById('formPagamentoMethod').value = method || '';
            document.getElementById('pagamentoId').value = payload.id || '';

            const tbody = document.querySelector('#parcelasPreview tbody');
            tbody.innerHTML = buildRow(0, 1, payload.vencimento || '<?php echo e(now()->addMonth()->toDateString()); ?>', payload.valor || '<?php echo e($assinatura->valor_cobrado); ?>', payload);
            const row = tbody.querySelector('tr');
            if (row) {
                row.querySelector('select[name$="[metodo]"]').value = payload.metodo || 'boleto';
                row.querySelector('select[name$="[status]"]').value = payload.status || 'pendente';
                row.querySelector('input[name$="[pago_em]"]').value = payload.pago_em || '';
                row.querySelector('input[name$="[observacao]"]').value = payload.observacao || '';
            }
        }

        function abrirNovoPagamento() {
            preencherModal({}, 'Registrar pagamentos', <?php echo json_encode(route('assinatura.pagamento.registrar', $assinatura), 512) ?>);
            document.getElementById('formPagamentoMethod').value = '';
        }

        function syncParcelaPagoEm(row) {
            if (!row) return;
            const status = row.querySelector('.js-status-parcela');
            const pagoEm = row.querySelector('.js-pago-em-parcela');
            if (!status || !pagoEm) return;

            const isPago = status.value === 'pago';
            pagoEm.required = isPago;
            pagoEm.disabled = !isPago;
            if (isPago) {
                pagoEm.classList.add('border-warning');
            } else {
                pagoEm.classList.remove('border-warning');
                pagoEm.value = '';
            }
        }

        function abrirEditar(button) {
            if (!button) return;
            const form = document.getElementById('formEditarPagamento');
            if (!form) return;
            const statusSelect = form.querySelector('.js-status-pagamento');
            const pagoEmInput = form.querySelector('.js-pago-em');

            form.action = button.dataset.action || '';
            form.querySelector('input[name="valor"]').value = button.dataset.valor || '';
            form.querySelector('select[name="metodo"]').value = button.dataset.metodo || 'boleto';
            statusSelect.value = button.dataset.status || 'pendente';
            form.querySelector('input[name="data_vencimento"]').value = button.dataset.vencimento || '';
            pagoEmInput.value = button.dataset.pagoEm || '';
            form.querySelector('input[name="gateway_payment_id"]').value = button.dataset.gatewayPaymentId || '';
            form.querySelector('input[name="observacao"]').value = button.dataset.observacao || '';

            const syncPagoEm = function () {
                const isPago = statusSelect.value === 'pago';
                pagoEmInput.required = isPago;
                pagoEmInput.disabled = !isPago;
                if (isPago) {
                    pagoEmInput.classList.add('border-warning');
                } else {
                    pagoEmInput.classList.remove('border-warning');
                    pagoEmInput.value = '';
                }
            };

            statusSelect.onchange = syncPagoEm;
            syncPagoEm();

            $('#modalEditarPagamento').modal('show');

            preencherModal({
                action: button.dataset.action || '',
                valor: button.dataset.valor || '',
                metodo: button.dataset.metodo || 'boleto',
                vencimento: button.dataset.vencimento || '',
                status: button.dataset.status || 'pendente',
                pago_em: button.dataset.pagoEm || '',
                gateway_payment_id: button.dataset.gatewayPaymentId || '',
                observacao: button.dataset.observacao || '',
            }, 'Editar pagamento', button.dataset.action || '', 'PATCH');
        }

        function abrirConfirmar(dados) {
            preencherModal({
                valor: dados.valor,
                metodo: dados.metodo,
                vencimento: dados.vencimento,
                status: 'pago',
                pago_em: '<?php echo e(now()->toDateString()); ?>',
            }, 'Confirmar pagamento', <?php echo json_encode(route('assinatura.pagamento.registrar', $assinatura), 512) ?>);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const formEditar = document.getElementById('formEditarPagamento');
            const statusSelect = formEditar?.querySelector('.js-status-pagamento');
            const pagoEmInput = formEditar?.querySelector('.js-pago-em');
            const helpPagoEm = formEditar?.querySelector('.js-pago-em-help');
            const formPagamento = document.getElementById('formPagamento');

            if (formEditar && statusSelect && pagoEmInput) {
                const syncPagoEm = function () {
                    const isPago = statusSelect.value === 'pago';
                    pagoEmInput.required = isPago;
                    pagoEmInput.disabled = !isPago;
                    if (helpPagoEm) {
                        helpPagoEm.textContent = isPago
                            ? 'Obrigatório quando o status for Pago.'
                            : 'Opcional enquanto o status não for Pago.';
                    }
                    if (!isPago) {
                        pagoEmInput.value = '';
                    }
                };

                statusSelect.addEventListener('change', syncPagoEm);
                syncPagoEm();

                formEditar.addEventListener('submit', function (event) {
                    if (statusSelect.value === 'pago' && !pagoEmInput.value) {
                        event.preventDefault();

                        if (window.Swal && typeof window.Swal.fire === 'function') {
                            window.Swal.fire({
                                icon: 'warning',
                                title: 'Data de pagamento obrigatória',
                                text: 'Selecione a data de pagamento antes de salvar como Pago.',
                                confirmButtonColor: '#0d6efd',
                            });
                        } else {
                            alert('Selecione a data de pagamento antes de salvar como Pago.');
                        }

                        pagoEmInput.focus();
                    }
                });
            }

            const tbody = document.querySelector('#parcelasPreview tbody');
            const btnGerar = document.getElementById('btnGerarParcelas');
            const btnAdd = document.getElementById('btnAddLinha');

            function renderRows(total) {
                const vencimentoInicial = document.getElementById('dataInicialParcelas').value;
                const valorBase = document.getElementById('valorBaseParcelas').value || '<?php echo e($assinatura->valor_cobrado); ?>';
                const baseDate = vencimentoInicial ? new Date(vencimentoInicial + 'T00:00:00') : new Date();
                const rows = [];
                for (let i = 0; i < total; i++) {
                    rows.push(buildRow(i, total, toInputDate(addMonths(baseDate, i)), valorBase));
                }
                tbody.innerHTML = rows.join('');
            }

            btnGerar?.addEventListener('click', function () {
                const total = Math.max(1, parseInt(document.getElementById('qtdParcelas').value || '1', 10));
                renderRows(total);
            });

            btnAdd?.addEventListener('click', function () {
                const rows = tbody.querySelectorAll('tr');
                const index = rows.length;
                const vencimentoInicial = document.getElementById('dataInicialParcelas').value || '<?php echo e(now()->addMonth()->toDateString()); ?>';
                const valorBase = document.getElementById('valorBaseParcelas').value || '<?php echo e($assinatura->valor_cobrado); ?>';
                const baseDate = new Date(vencimentoInicial + 'T00:00:00');
                tbody.insertAdjacentHTML('beforeend', buildRow(index, index + 1, toInputDate(addMonths(baseDate, index)), valorBase));
                const updatedRows = tbody.querySelectorAll('tr');
                updatedRows.forEach((row, idx) => {
                    const cell = row.querySelector('td');
                    if (cell) cell.textContent = `${idx + 1}/${updatedRows.length}`;
                    syncParcelaPagoEm(row);
                });
            });

            tbody.addEventListener('click', function (event) {
                const btn = event.target.closest('.js-remove-row');
                if (!btn) return;
                const row = btn.closest('tr');
                if (row) row.remove();
                const rows = tbody.querySelectorAll('tr');
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Nenhuma parcela adicionada.</td></tr>';
                    return;
                }
                rows.forEach((row, idx) => {
                    const cell = row.querySelector('td');
                    if (cell) cell.textContent = `${idx + 1}/${rows.length}`;
                });
            });

            tbody.addEventListener('change', function (event) {
                const target = event.target;
                if (target && target.classList.contains('js-status-parcela')) {
                    syncParcelaPagoEm(target.closest('tr'));
                }
            });

            formPagamento?.addEventListener('submit', function (event) {
                const rows = tbody.querySelectorAll('tr');
                let invalidRow = null;

                rows.forEach(function (row) {
                    const status = row.querySelector('.js-status-parcela');
                    const pagoEm = row.querySelector('.js-pago-em-parcela');
                    if (status && pagoEm && status.value === 'pago' && !pagoEm.value && !invalidRow) {
                        invalidRow = row;
                    }
                });

                if (invalidRow) {
                    event.preventDefault();

                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        window.Swal.fire({
                            icon: 'warning',
                            title: 'Data de pagamento obrigatória',
                            text: 'Preencha a data de pagamento nas linhas marcadas como Pago.',
                            confirmButtonColor: '#0d6efd',
                        });
                    } else {
                        alert('Preencha a data de pagamento nas linhas marcadas como Pago.');
                    }

                    invalidRow.querySelector('.js-pago-em-parcela')?.focus();
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <style>
        .container-fluid { max-width: 100%; }
        .module-card { border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(12,20,40,.10); }
        .module-card-header { background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%); padding: 24px 28px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .module-header-left { display:flex; align-items:center; gap:16px; }
        .module-header-icon { width:52px; height:52px; border-radius:14px; background: rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center; font-size:1.4rem; color:#60a5fa; }
        .module-header-eyebrow { font-size:.62rem; font-weight:700; letter-spacing:.18em; text-transform:uppercase; color:rgba(148,163,184,.8); margin-bottom:3px; }
        .module-header-title { font-size:1.45rem; font-weight:800; color:#fff; margin:0 0 3px; }
        .module-header-sub { margin:0; font-size:.82rem; color:rgba(148,163,184,.9); }
        .module-header-right { display:flex; gap:8px; }
        .btn-xp { display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .9rem; border-radius:999px; font-size:.8rem; font-weight:700; text-decoration:none; background:rgba(255,255,255,.15); color:#fff; border:none; }
        .btn-xp:hover { background:rgba(255,255,255,.25); color:#fff; text-decoration:none; }
        .btn-xp--sm { padding:.25rem .7rem; font-size:.76rem; }
        .btn-xp--outline-light { background:transparent; border:1px solid rgba(255,255,255,.4); }
        .assinatura-grid { display:grid; grid-template-columns: 1fr 2fr; gap:20px; }
        .assinatura-table-wrap { overflow-x:auto; }

        @media (max-width: 991.98px) {
            .module-card-header {
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
            }

            .module-header-left {
                width: 100%;
            }

            .module-header-right {
                width: 100%;
                justify-content: flex-start;
            }

            .assinatura-grid {
                grid-template-columns: 1fr;
            }

            .module-header-title {
                font-size: 1.2rem;
                line-height: 1.25;
            }
        }

        @media (max-width: 767.98px) {
            .module-header-left {
                gap: 12px;
            }

            .module-header-icon {
                width: 44px;
                height: 44px;
                font-size: 1.1rem;
                flex: 0 0 44px;
            }

            .module-header-sub {
                font-size: .76rem;
            }

            .btn-xp--sm {
                width: 100%;
                justify-content: center;
            }

            .module-header-right {
                flex-direction: column;
            }

            .card-header .d-flex {
                flex-direction: column;
                align-items: stretch !important;
            }

            .card-header .btn {
                width: 100%;
            }

            .table-responsive {
                border: 0;
            }

            .table-responsive table {
                min-width: 760px;
            }

            .card-body.p-0.table-responsive {
                overflow-x: auto;
            }

            .table td,
            .table th {
                white-space: nowrap;
            }
        }
    </style>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\assinatura\show.blade.php ENDPATH**/ ?>