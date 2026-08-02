
<?php
    $showPercentualDisponibilidade = (int) ($dispResumo['percentual'] ?? 0);
    $showStatusDisponibilidade = (string) ($dispResumo['status_mes'] ?? 'off');

    $showDisponivel = $showStatusDisponibilidade === 'ok' && $showPercentualDisponibilidade >= 100;
    $showParcial = $showStatusDisponibilidade === 'partial'
        || ($showPercentualDisponibilidade > 0 && $showPercentualDisponibilidade < 100);

    $showLabelDisponibilidade = match (true) {
        $showDisponivel => 'Disponível',
        $showParcial => 'Disponibilidade parcial',
        default => 'Indisponível',
    };

    $showIconDisponibilidade = match (true) {
        $showDisponivel => 'fa-check-circle',
        $showParcial => 'fa-adjust',
        default => 'fa-clock',
    };

    $showClassDisponibilidade = match (true) {
        $showDisponivel => 'is-success',
        $showParcial => 'is-warning',
        default => 'is-danger',
    };

    $disponibilidadeAtiva = (int) ($dispReservas->count() ?? 0);
    $avariasPendentes = (int) $veiculo->veiculoDanos->whereNull('correcao_data')->count();
    $movimentacoesTotal = (int) $veiculo->movimentacoes->count();
    $osPendentes = (int) $veiculo->ordensServico->whereIn('status', [\App\Models\OrdemServico::STATUS_ABERTA, \App\Models\OrdemServico::STATUS_EM_ANDAMENTO])->count();
    $sinistrosAbertos = (int) $veiculo->sinistros->whereIn('status', ['aberto', 'em_analise', 'aguardando_documentos'])->count();
    $multasTotal = (int) $veiculo->multas->count();
    $multasVencidas = (int) $veiculo->multas->filter(function ($multa) {
        return in_array((string) ($multa->status_multa ?? ''), ['pendente', 'em_analise'], true)
            && ! empty($multa->data_vencimento)
            && \Carbon\Carbon::parse($multa->data_vencimento)->isPast();
    })->count();

    $manutencoesVencidas = (int) $veiculo->pendenciasManutencaoPreventiva->filter(function ($pendencia) use ($veiculo) {
        $vencidaPorData = ! empty($pendencia->data_prevista) && \Carbon\Carbon::parse($pendencia->data_prevista)->isPast();
        $vencidaPorKm = $pendencia->km_previsto !== null && (int) ($veiculo->kms ?? 0) >= (int) $pendencia->km_previsto;

        return in_array((string) ($pendencia->status ?? ''), ['pendente', 'atrasado'], true) && ($vencidaPorData || $vencidaPorKm);
    })->count();

    $documentosCadastrados = (int) $veiculo->documentacoesVeiculo->count();
    $documentosVencidos = (int) $veiculo->documentacoesVeiculo->flatMap->parcelas->filter(function ($parcela) {
        return in_array((string) ($parcela->status ?? ''), ['pendente', 'atrasado'], true)
            && ! empty($parcela->data_vencimento)
            && \Carbon\Carbon::parse($parcela->data_vencimento)->isPast();
    })->count();

    $countTooltips = [
        'disp' => 'Quantidade de reservas ou bloqueios ativos no período atual.',
        'avarias' => 'Quantidade de avarias ainda pendentes de correção.',
        'mov' => 'Quantidade total de movimentações registradas para este veículo.',
        'os' => 'Quantidade de ordens de serviço abertas ou em andamento.',
        'sinistros' => 'Quantidade de sinistros ainda não encerrados.',
        'multas' => 'Quantidade total de multas cadastradas para este veículo.',
        'manutencao' => 'Quantidade de manutenções preventivas vencidas.',
        'documentacao' => 'Quantidade total de documentos cadastrados para este veículo.',
    ];

    $badgeColor = function (int $count, int $alertCount = 0, string $alertType = 'red') {
        if ($alertCount > 0) {
            return $alertType === 'orange' ? 'vs-step__count--warn' : 'vs-step__count--alert';
        }

        return 'vs-step__count--muted';
    };
?>

<section class="vs-shell vs-shell--compact mb-4" aria-labelledby="vehicle-operation-title">
    <header class="vs-compact-head">
        <div>
            <span class="vs-shell__eyebrow">Operação e histórico</span>
            <h3 class="vs-shell__title" id="vehicle-operation-title">Acompanhamento do veículo</h3>
        </div>

        <span class="vs-badge-soft <?php echo e($showClassDisponibilidade); ?>">
            <i class="fas <?php echo e($showIconDisponibilidade); ?>"></i>
            <?php echo e($showLabelDisponibilidade); ?> · <?php echo e($showPercentualDisponibilidade); ?>%
        </span>
    </header>

    
    <nav class="vs-steps vs-steps--compact" id="vsSteps" aria-label="Seções operacionais do veículo">
        <button class="vs-step is-active" type="button" data-step="disp">
            <span class="vs-step__icon"><i class="fas fa-calendar-check"></i></span>
            <span class="vs-step__content"><strong>Disponibilidade</strong></span>
            <span class="vs-step__count <?php echo e($disponibilidadeAtiva > 0 ? 'vs-step__count--alert' : 'vs-step__count--success'); ?>" title="<?php echo e($countTooltips['disp']); ?>" data-toggle="tooltip">
                <?php echo e($disponibilidadeAtiva); ?>

            </span>
        </button>

        <button class="vs-step" type="button" data-step="avarias">
            <span class="vs-step__icon"><i class="fas fa-car-crash"></i></span>
            <span class="vs-step__content"><strong>Avarias</strong></span>
            <span class="vs-step__count <?php echo e($avariasPendentes > 0 ? 'vs-step__count--alert' : 'vs-step__count--success'); ?>" title="<?php echo e($countTooltips['avarias']); ?>" data-toggle="tooltip">
                <?php echo e($avariasPendentes); ?>

            </span>
        </button>

        <button class="vs-step" type="button" data-step="mov">
            <span class="vs-step__icon"><i class="fas fa-route"></i></span>
            <span class="vs-step__content"><strong>Movimentações</strong></span>
            <span class="vs-step__count vs-step__count--muted" title="<?php echo e($countTooltips['mov']); ?>" data-toggle="tooltip"><?php echo e($movimentacoesTotal); ?></span>
        </button>

        <button class="vs-step" type="button" data-step="os">
            <span class="vs-step__icon"><i class="fas fa-tools"></i></span>
            <span class="vs-step__content"><strong>Ordens de serviço</strong></span>
            <span class="vs-step__count <?php echo e($osPendentes > 0 ? 'vs-step__count--warn' : 'vs-step__count--success'); ?>" title="<?php echo e($countTooltips['os']); ?>" data-toggle="tooltip">
                <?php echo e($osPendentes); ?>

            </span>
        </button>

        <button class="vs-step" type="button" data-step="sinistros">
            <span class="vs-step__icon"><i class="fas fa-shield-alt"></i></span>
            <span class="vs-step__content"><strong>Sinistros</strong></span>
            <span class="vs-step__count <?php echo e($sinistrosAbertos > 0 ? 'vs-step__count--alert' : 'vs-step__count--success'); ?>" title="<?php echo e($countTooltips['sinistros']); ?>" data-toggle="tooltip">
                <?php echo e($sinistrosAbertos); ?>

            </span>
        </button>

        <button class="vs-step" type="button" data-step="multas">
            <span class="vs-step__icon"><i class="fas fa-exclamation-triangle"></i></span>
            <span class="vs-step__content"><strong>Multas</strong></span>
            <span class="vs-step__count <?php echo e($multasVencidas > 0 ? 'vs-step__count--alert' : 'vs-step__count--muted'); ?>" title="<?php echo e($countTooltips['multas']); ?>" data-toggle="tooltip">
                <?php echo e($multasTotal); ?>

            </span>
        </button>

        <button class="vs-step" type="button" data-step="manutencao">
            <span class="vs-step__icon"><i class="fas fa-tools"></i></span>
            <span class="vs-step__content"><strong>Manut. Preventiva</strong></span>
            <span class="vs-step__count <?php echo e($manutencoesVencidas > 0 ? 'vs-step__count--alert' : 'vs-step__count--success'); ?>" title="<?php echo e($countTooltips['manutencao']); ?>" data-toggle="tooltip">
                <?php echo e($manutencoesVencidas); ?>

            </span>
        </button>

        <button class="vs-step" type="button" data-step="documentacao">
            <span class="vs-step__icon"><i class="fas fa-file-alt"></i></span>
            <span class="vs-step__content"><strong>Documentação</strong></span>
            <span class="vs-step__count <?php echo e($documentosVencidos > 0 ? 'vs-step__count--alert' : 'vs-step__count--muted'); ?>" title="<?php echo e($countTooltips['documentacao']); ?>" data-toggle="tooltip">
                <?php echo e($documentosCadastrados); ?>

            </span>
        </button>
    </nav>

    <?php echo $__env->make('veiculo.components.operacional.etapas.disponibilidade', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('veiculo.components.operacional.etapas.avarias', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('veiculo.components.operacional.etapas.ordens-servico', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('veiculo.components.operacional.etapas.movimentacoes', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('veiculo.components.operacional.etapas.sinistros', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('veiculo.components.operacional.etapas.multas', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('veiculo.components.operacional.etapas.manutencao-preventiva', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('veiculo.components.operacional.etapas.documentacao', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</section>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/components/operacional/index.blade.php ENDPATH**/ ?>