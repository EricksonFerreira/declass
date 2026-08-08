
                <div class="vs-panel is-active" data-step-panel="disp">
                    <div class="vs-panel-head">
                        <div>
                            <span class="vs-panel-head__eyebrow">Etapa 01</span>
                            <h4 class="vs-panel-head__title">Disponibilidade do veículo</h4>
                            <p class="vs-panel-head__sub">Visualize reservas, movimentações e bloqueios por período com foco em entendimento rápido.</p>
                        </div>
                        <div class="vs-kpis">
                            <div class="vs-kpi">
                                <span>Status atual</span>
                                <strong><?php echo e($showLabelDisponibilidade); ?> · <?php echo e($showPercentualDisponibilidade); ?>%</strong>
                            </div>
                            <div class="vs-kpi">
                                <span>Reservas totais</span>
                                <strong><?php echo e($veiculo->reservas->count()); ?></strong>
                            </div>
                            <div class="vs-kpi">
                                <span>Bloqueio entre reservas</span>
                                <strong><?php echo e($reservaBloqueioHoras ?? 0); ?>h</strong>
                            </div>
                        </div>
                    </div>

                    <div class="vs-info-banner">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            Use os filtros do calendário abaixo para navegar por mês, estação e tipo de ocupação.
                            Os botões de legenda continuam funcionando dentro do calendário.
                        </div>
                    </div>

                    <?php echo $__env->make('veiculo.components.disponibilidade', [
                        'veiculo'             => $veiculo,
                        'dispMes'             => $dispMes ?? null,
                        'dispAno'             => $dispAno ?? null,
                        'dispEstacaoId'       => $dispEstacaoId ?? 0,
                        'dispEstacoes'        => $dispEstacoes ?? [],
                        'dispDiasNoMes'       => $dispDiasNoMes ?? null,
                        'dispFirstDow'        => $dispFirstDow ?? null,
                        'dispGrade'           => $dispGrade ?? [],
                        'dispStats'           => $dispStats ?? [],
                        'dispReservas'        => $dispReservas ?? collect(),
                        'dispMovimentacoes'   => $dispMovimentacoes ?? collect(),
                        'reservaBloqueioHoras'=> $reservaBloqueioHoras ?? 0,
                    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/components/operacional/etapas/disponibilidade.blade.php ENDPATH**/ ?>