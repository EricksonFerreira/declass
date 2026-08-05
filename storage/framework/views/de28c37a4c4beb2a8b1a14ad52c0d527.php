<div class="row">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-soft mb-0">
            <div class="sum-card">
                <div class="sum-ico"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <div class="sum-label">Reserva</div>
                    <div class="sum-value">#<?php echo e($reserva->id); ?></div>
                    <div class="sum-meta"><?php echo e($codigoReserva); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-soft mb-0">
            <div class="sum-card">
                <div class="sum-ico"><i class="fas fa-car"></i></div>
                <div>
                    <div class="sum-label">Veículo</div>
                    <div class="sum-value"><?php echo e($veiculo?->placa_licenca ?? $veiculo?->placa ?? '—'); ?></div>
                    <div class="sum-meta"><?php echo e($veiculo?->nome ?? $veiculo?->modelo?->nome ?? 'Veículo'); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-soft mb-0">
            <div class="sum-card">
                <div class="sum-ico"><i class="fas fa-user"></i></div>
                <div>
                    <div class="sum-label">Cliente</div>
                    <div class="sum-value"><?php echo e($cliente?->nome ?? $cliente?->name ?? 'Cliente'); ?></div>
                    <div class="sum-meta"><?php echo e($cliente?->telefone ?? $cliente?->phone ?? $cliente?->cpf ?? 'Sem dados'); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-soft mb-0">
            <div class="sum-card">
                <div class="sum-ico"><i class="fas fa-dollar-sign"></i></div>
                <div>
                    <div class="sum-label">Resultado da revisão</div>
                    <div class="sum-value">R$ <?php echo e(number_format($resumoFinanceiro['resultado_revisao'] ?? $resumoFinanceiro['total_cobrancas'] ?? 0, 2, ',', '.')); ?></div>
                    <span class="mini-chip <?php echo e(($resumoFinanceiro['resultado_revisao'] ?? $resumoFinanceiro['total_cobrancas'] ?? 0) > 0 ? 'mini-chip--warn' : 'mini-chip--ok'); ?>">
                        <?php echo e(($resumoFinanceiro['resultado_revisao'] ?? $resumoFinanceiro['total_cobrancas'] ?? 0) > 0 ? 'Soma das cobranças' : 'Sem cobrança'); ?>

                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/revisao_checkout_summary_cards.blade.php ENDPATH**/ ?>