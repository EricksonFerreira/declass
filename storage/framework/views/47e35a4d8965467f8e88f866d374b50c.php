<div class="vs-panel" data-step-panel="manutencao">
    <?php
        $planoGrupo = $planoManutencao ?? null;
        $itensPlano = $planoGrupo?->itens ?? collect();
    ?>

    <div class="vs-panel-head">
        <div>
            <span class="vs-panel-head__eyebrow">Etapa 07</span>
            <h4 class="vs-panel-head__title">Manutenção preventiva</h4>
            <p class="vs-panel-head__sub">Acompanhe execuções e pendências do plano de manutenção, sem misturar isso com ordem de serviço.</p>
        </div>
        <div class="vs-kpis">
            <div class="vs-kpi">
                <span>Execuções</span>
                <strong><?php echo e($veiculo->manutencoesPreventivas->count()); ?></strong>
            </div>
            <div class="vs-kpi">
                <span>Pendências</span>
                <strong><?php echo e($veiculo->pendenciasManutencaoPreventiva->whereIn('status', ['pendente','atrasado'])->count()); ?></strong>
            </div>
            <div class="vs-kpi">
                <span>Atrasadas</span>
                <strong><?php echo e($veiculo->pendenciasManutencaoPreventiva->where('status', 'atrasado')->count()); ?></strong>
            </div>
        </div>
    </div>

    <div class="vt-panel-actions">
        <a href="<?php echo e(route('manutencao.planos.index')); ?>" target="_blank"  class="btn-xp btn-xp--sm">
            <i class="fas fa-clipboard-list mr-1"></i>Ver planos de manutenção
        </a>
    </div>

    <?php if(! $planoGrupo): ?>
        <div class="empty-state"><i class="fas fa-tools"></i><p>Nenhum plano de manutenção cadastrado para o grupo deste veículo.</p></div>
    <?php elseif($itensPlano->isEmpty()): ?>
        <div class="empty-state"><i class="fas fa-tools"></i><p>Este plano não possui itens cadastrados.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover xp-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Item</th>
                        <th>Próxima data</th>
                        <th>Próximo KM</th>
                        <th>Status</th>
                        <th>Última execução</th>
                        <th>Histórico</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $itensPlano; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planoItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $ultimaExecucao = $veiculo->manutencoesPreventivas->where('item_id', $planoItem->item_id)->sortByDesc('data_execucao')->first();
                            $historicoExecucoes = $veiculo->manutencoesPreventivas->where('item_id', $planoItem->item_id)->sortByDesc('data_execucao');
                            $pendenciaAtual = $veiculo->pendenciasManutencaoPreventiva->where('item_id', $planoItem->item_id)->sortByDesc('id')->first();
                            $statusAtual = 'pendente';
                            $alertaVencido = false;
                            if ($pendenciaAtual) {
                                $statusAtual = $pendenciaAtual->status;
                                $alertaVencido =
                                    ($pendenciaAtual->data_prevista && \Carbon\Carbon::parse($pendenciaAtual->data_prevista)->isPast())
                                    || ($pendenciaAtual->km_previsto !== null && (int) $veiculo->kms >= (int) $pendenciaAtual->km_previsto);
                            } elseif ($ultimaExecucao) {
                                $statusAtual = 'concluida';
                            }
                        ?>
                        <tr>
                            <td><span class="badge badge-primary">Plano</span></td>
                            <td><?php echo e($planoItem->item?->nome ?? ('Item #'.$planoItem->item_id)); ?></td>
                            <td>
                                <?php echo e($pendenciaAtual?->data_prevista ? \Carbon\Carbon::parse($pendenciaAtual->data_prevista)->format('d/m/Y') : '—'); ?>

                                <?php if($alertaVencido): ?>
                                    <div><span class="badge badge-danger">Vencido</span></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo e($pendenciaAtual?->km_previsto !== null ? number_format((float) $pendenciaAtual->km_previsto, 0, ',', '.') : '—'); ?>

                                <?php if($alertaVencido): ?>
                                    <div><span class="badge badge-danger">KM atingido</span></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo e(in_array($statusAtual, ['pendente','atrasado']) ? 'warning' : 'success'); ?>">
                                    <?php echo e(ucfirst($statusAtual)); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($ultimaExecucao): ?>
                                    <?php echo e(\Carbon\Carbon::parse($ultimaExecucao->data_execucao)->format('d/m/Y')); ?>

                                    <div class="text-xs text-muted"><?php echo e(number_format((float) $ultimaExecucao->km_execucao, 0, ',', '.')); ?> km</div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-xs btn-outline-secondary" data-toggle="collapse" data-target="#historico-<?php echo e($planoItem->id); ?>" aria-expanded="false">
                                    <i class="fas fa-history mr-1"></i>Ver histórico
                                </button>
                            </td>
                            <td>
                                
                                    <form method="POST" action="<?php echo e(route('veiculo.manutencao.concluir', $veiculo->id)); ?>" class="mb-0 js-form-concluir-manutencao">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="item_id" value="<?php echo e($planoItem->item_id); ?>">
                                        <button type="button" class="btn btn-xs btn-success js-btn-concluir-manutencao"
                                            data-item-nome="<?php echo e(e($planoItem->item?->nome ?? ('Item #'.$planoItem->item_id))); ?>"
                                            data-proxima-data="<?php echo e($pendenciaAtual?->data_prevista ? \Carbon\Carbon::parse($pendenciaAtual->data_prevista)->format('d/m/Y') : ''); ?>"
                                            data-proximo-km="<?php echo e($pendenciaAtual?->km_previsto !== null ? number_format((float) $pendenciaAtual->km_previsto, 0, ',', '.') : ''); ?>"
                                            data-venceu="<?php echo e($alertaVencido ? '1' : '0'); ?>">
                                            <i class="fas fa-check mr-1"></i>Concluir
                                        </button>
                                    </form>
                                
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <?php $__currentLoopData = $itensPlano; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planoItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $historicoExecucoes = $veiculo->manutencoesPreventivas->where('item_id', $planoItem->item_id)->sortByDesc('data_execucao');
                    $pendenciasItem = $veiculo->pendenciasManutencaoPreventiva->where('item_id', $planoItem->item_id)->sortBy('id')->values();
                    $execucoesItem = $historicoExecucoes->sortBy('id')->values();
                    $linhasHistorico = collect();
                    $totalLinhas = max($pendenciasItem->count(), $execucoesItem->count());

                    for ($i = 0; $i < $totalLinhas; $i++) {
                        $pendencia = $pendenciasItem[$i] ?? null;
                        $execucao = $execucoesItem[$i] ?? null;

                        $linhasHistorico->push([
                            'data_prevista' => $pendencia?->data_prevista,
                            'data_realizada' => $execucao?->data_execucao,
                            'km_previsto' => $pendencia?->km_previsto,
                            'km_realizado' => $execucao?->km_execucao,
                            'status' => $execucao ? 'Concluída' : (($pendencia?->status ?? 'pendente') === 'atrasado' ? 'Atrasada' : 'Pendente'),
                        ]);
                    }
                ?>
                <div class="collapse mb-3" id="historico-<?php echo e($planoItem->id); ?>">
                    <div class="card card-body">
                        <h5 class="mb-3"><?php echo e($planoItem->item?->nome ?? ('Item #'.$planoItem->item_id)); ?></h5>

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <h6 class="text-sm font-weight-bold">Execuções</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Data prevista</th>
                                                <th>Data realizada</th>
                                                <th>KM previsto</th>
                                                <th>KM realizado</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $linhasHistorico; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($linha['data_prevista'] ? \Carbon\Carbon::parse($linha['data_prevista'])->format('d/m/Y') : '—'); ?></td>
                                                    <td><?php echo e($linha['data_realizada'] ? \Carbon\Carbon::parse($linha['data_realizada'])->format('d/m/Y') : '—'); ?></td>
                                                    <td><?php echo e($linha['km_previsto'] !== null ? number_format((float) $linha['km_previsto'], 0, ',', '.') . ' km' : '—'); ?></td>
                                                    <td><?php echo e($linha['km_realizado'] !== null ? number_format((float) $linha['km_realizado'], 0, ',', '.') . ' km' : '—'); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo e($linha['status'] === 'Concluída' ? 'success' : ($linha['status'] === 'Atrasada' ? 'warning' : 'secondary')); ?>">
                                                            <?php echo e($linha['status']); ?>

                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr><td colspan="5" class="text-center text-muted">Nenhuma execução registrada.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <h6 class="text-sm font-weight-bold">Pendências</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Previsão</th>
                                                <th>KM</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $pendenciasItem; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pendencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($pendencia->data_prevista ? \Carbon\Carbon::parse($pendencia->data_prevista)->format('d/m/Y') : '—'); ?></td>
                                                    <td><?php echo e($pendencia->km_previsto !== null ? number_format((float) $pendencia->km_previsto, 0, ',', '.') : '—'); ?></td>
                                                    <td><span class="badge badge-<?php echo e(in_array($pendencia->status, ['pendente','atrasado']) ? 'warning' : 'success'); ?>"><?php echo e(ucfirst($pendencia->status)); ?></span></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr><td colspan="3" class="text-center text-muted">Nenhuma pendência encontrada.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('js'); ?>
<script>
(function () {
    function bindSwal() {
        const forms = document.querySelectorAll('.js-form-concluir-manutencao');
        if (!forms.length || !window.Swal || typeof window.Swal.fire !== 'function') {
            return false;
        }

        forms.forEach(function (form) {
            const btn = form.querySelector('.js-btn-concluir-manutencao');
            if (!btn || btn.dataset.bound === '1') return;
            btn.dataset.bound = '1';

            btn.addEventListener('click', function () {
                const itemNome = btn.dataset.itemNome || 'Manutenção';
                const proximaData = btn.dataset.proximaData || '—';
                const proximoKm = btn.dataset.proximoKm || '—';
                const venceu = btn.dataset.venceu === '1';

                window.Swal.fire({
                    icon: venceu ? 'warning' : 'success',
                    title: venceu ? 'Manutenção vencida' : 'Concluir manutenção?',
                    html: `
                        <div class="text-left">
                            <div><strong>Item:</strong> ${itemNome}</div>
                            <div><strong>Próxima data:</strong> ${proximaData}</div>
                            <div><strong>Próximo KM:</strong> ${proximoKm}</div>
                            ${venceu ? '<div class="text-danger mt-2 font-weight-bold">Este item já passou do prazo ou da quilometragem prevista.</div>' : ''}
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check mr-1"></i>Concluir',
                    cancelButtonText: 'Cancelar',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        return true;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            if (!bindSwal()) {
                setTimeout(bindSwal, 250);
            }
        });
    } else if (!bindSwal()) {
        setTimeout(bindSwal, 250);
    }
})();
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/components/operacional/etapas/manutencao-preventiva.blade.php ENDPATH**/ ?>