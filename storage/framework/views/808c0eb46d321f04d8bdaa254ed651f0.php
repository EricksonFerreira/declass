
                <div class="vs-panel" data-step-panel="multas">
                    <div class="vs-panel-head">
                        <div>
                            <span class="vs-panel-head__eyebrow">Etapa 06</span>
                            <h4 class="vs-panel-head__title">Multas e infrações</h4>
                            <p class="vs-panel-head__sub">Use filtros mais ricos por status, órgão, auto, período, valor e desconto.</p>
                        </div>
                        <div class="vs-kpis">
                            <div class="vs-kpi">
                                <span>Pendentes</span>
                                <strong><?php echo e($veiculo->multas->whereIn('status_multa', ['pendente','em_analise'])->count()); ?></strong>
                            </div>
                            <div class="vs-kpi">
                                <span>Pagas</span>
                                <strong><?php echo e($veiculo->multas->where('status_multa', 'paga')->count()); ?></strong>
                            </div>
                            <div class="vs-kpi">
                                <span>Total</span>
                                <strong><?php echo e($veiculo->multas->count()); ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="vs-filter-wrap">
                        <div class="vs-filter-top">
                            <div class="vs-filter-search">
                                <i class="fas fa-search"></i>
                                <input type="text" id="mtf_busca" class="vs-search-input" placeholder="Buscar por órgão, auto, local ou reserva..." oninput="filtrarMt()">
                            </div>

                            <div class="vs-filter-actions">
                                <button type="button" class="vs-chip is-active" data-chip-group="multas" onclick="chipMt(this, 'todos')">Todas</button>
                                <button type="button" class="vs-chip" data-chip-group="multas" onclick="chipMt(this, 'pendente')">Pendentes</button>
                                <button type="button" class="vs-chip" data-chip-group="multas" onclick="chipMt(this, 'paga')">Pagas</button>
                                <button type="button" class="vs-chip" data-chip-group="multas" onclick="chipMt(this, 'recorrida')">Recorridas</button>
                                <button type="button" class="vs-chip" data-chip-group="multas" onclick="chipMt(this, 'com_desconto')">Com desconto</button>
                                <button type="button" class="btn-xp btn-xp--sm btn-xp--outline" onclick="toggleFiltrosAvancados('multas')">
                                    <i class="fas fa-sliders-h mr-1"></i>Filtros avançados
                                </button>
                            </div>
                        </div>

                        <div class="vs-advanced-filters" id="vsAdvanced-multas">
                            <div class="vs-filter-grid">
                                <div class="vs-filter-field">
                                    <label>Status da Multa</label>
                                    <select id="mtf_status" class="vt-select" onchange="filtrarMt()">
                                        <option value="">Todos</option>
                                        <?php $__currentLoopData = \App\Models\Multa::STATUS_MULTA; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($k); ?>"><?php echo e($v); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="vs-filter-field">
                                    <label>Status Desconto</label>
                                    <select id="mtf_desconto" class="vt-select" onchange="filtrarMt()">
                                        <option value="">Todos</option>
                                        <?php $__currentLoopData = \App\Models\Multa::STATUS_DESCONTO; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($k); ?>"><?php echo e($v); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="vs-filter-field">
                                    <label>Órgão Autuador</label>
                                    <input type="text" id="mtf_orgao" class="vt-input" placeholder="Ex: DETRAN, PRF..." oninput="filtrarMt()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Nº Auto</label>
                                    <input type="text" id="mtf_auto" class="vt-input" placeholder="Número do auto" oninput="filtrarMt()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Infração de</label>
                                    <input type="date" id="mtf_data_de" class="vt-input" onchange="filtrarMt()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Infração até</label>
                                    <input type="date" id="mtf_data_ate" class="vt-input" onchange="filtrarMt()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Valor mín. (R$)</label>
                                    <input type="number" id="mtf_valor_min" class="vt-input" placeholder="0.00" step="0.01" oninput="filtrarMt()">
                                </div>

                                <div class="vs-filter-field">
                                    <label>Valor máx. (R$)</label>
                                    <input type="number" id="mtf_valor_max" class="vt-input" placeholder="9999.99" step="0.01" oninput="filtrarMt()">
                                </div>

                                <div class="vs-filter-field vs-filter-field--check">
                                    <label class="vs-check">
                                        <input type="checkbox" id="mtf_com_desconto" onchange="filtrarMt()">
                                        <span>Mostrar apenas multas com desconto aplicado</span>
                                    </label>
                                </div>
                            </div>

                            <div class="vs-filter-footer">
                                <div class="vs-filter-result" id="mtf_result"></div>
                                <div class="vs-filter-footer__actions">
                                    <button type="button" class="vt-btn-clear" onclick="limparFiltroMt()">
                                        <i class="fas fa-times"></i> Limpar
                                    </button>
                                    <button type="button" class="vt-btn-filter" onclick="filtrarMt()">
                                        <i class="fas fa-search"></i> Aplicar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vt-panel-actions">
                        <a target="_blank" href="<?php echo e(route('multa.create', ['veiculo_id' => $veiculo->id])); ?>" class="btn-xp btn-xp--sm">
                            <i class="fas fa-plus mr-1"></i>Cadastrar multa
                        </a>
                        <a target="_blank" href="<?php echo e(route('multa.index', ['veiculo_id' => $veiculo->id])); ?>" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-list mr-1"></i>Ver todas
                        </a>
                    </div>

                    <?php if($veiculo->multas->isEmpty()): ?>
                        <div class="empty-state"><i class="fas fa-receipt"></i><p>Nenhuma multa registrada.</p></div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover xp-table" id="tabelaMt">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Data Infração</th>
                                    <th>Órgão</th>
                                    <th>Nº Auto</th>
                                    <th>Local</th>
                                    <th>Vencimento</th>
                                    <th>Pontos</th>
                                    <th class="text-right">Valor Orig.</th>
                                    <th class="text-right">Desconto</th>
                                    <th class="text-right">Valor Final</th>
                                    <th>Status Multa</th>
                                    <th>Status Desc.</th>
                                    <th>Reserva</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $veiculo->multas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $mtBadge = match($mt->status_multa) {
                                        'paga','deferida' => 'success',
                                        'cancelada'       => 'secondary',
                                        'recorrida'       => 'info',
                                        'indeferida'      => 'danger',
                                        default           => 'warning',
                                    };
                                ?>
                                <tr
                                    data-mt-status="<?php echo e($mt->status_multa); ?>"
                                    data-mt-desconto="<?php echo e($mt->status_desconto); ?>"
                                    data-mt-orgao="<?php echo e(strtolower($mt->orgao_autuador ?? '')); ?>"
                                    data-mt-auto="<?php echo e(strtolower($mt->numero_auto ?? '')); ?>"
                                    data-mt-data="<?php echo e($mt->data_hora_infracao ? $mt->data_hora_infracao->format('Y-m-d') : ''); ?>"
                                    data-mt-valor="<?php echo e((float)($mt->valor_final ?? 0)); ?>"
                                    data-mt-has-desconto="<?php echo e($mt->valor_desconto !== null ? 1 : 0); ?>"
                                >
                                    <td><span class="xp-id">#<?php echo e($mt->id); ?></span></td>
                                    <td><?php echo e($mt->data_hora_infracao ? $mt->data_hora_infracao->format('d/m/Y H:i') : '—'); ?></td>
                                    <td><?php echo e($mt->orgao_autuador ?? '—'); ?></td>
                                    <td><?php echo e($mt->numero_auto ?? '—'); ?></td>
                                    <td class="obs-cell"><?php echo e(\Illuminate\Support\Str::limit($mt->local_infracao ?? '—', 30)); ?></td>
                                    <td><?php echo e($mt->data_vencimento ? $mt->data_vencimento->format('d/m/Y') : '—'); ?></td>
                                    <td><?php echo e($mt->pontos ?? '—'); ?></td>
                                    <td class="text-right">R$ <?php echo e(number_format((float)$mt->valor_original,2,',','.')); ?></td>
                                    <td class="text-right"><?php echo e($mt->valor_desconto !== null ? 'R$ '.number_format((float)$mt->valor_desconto,2,',','.') : '—'); ?></td>
                                    <td class="text-right font-weight-bold">R$ <?php echo e(number_format((float)$mt->valor_final,2,',','.')); ?></td>
                                    <td><span class="badge badge-<?php echo e($mtBadge); ?>"><?php echo e($mt->label_status_multa); ?></span></td>
                                    <td><span class="badge badge-light text-dark"><?php echo e($mt->label_status_desconto); ?></span></td>
                                    <td>
                                        <?php if($mt->reserva?->codigoReserva?->codigo): ?>
                                            <a href="<?php echo e(route('reserva.show', $mt->reserva->codigoReserva->codigo)); ?>" class="xp-link">#<?php echo e($mt->reserva->codigoReserva->codigo); ?></a>
                                        <?php else: ?> <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo e(route('multa.show', $mt->id)); ?>" class="btn-xp btn-xp--sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/components/operacional/etapas/multas.blade.php ENDPATH**/ ?>