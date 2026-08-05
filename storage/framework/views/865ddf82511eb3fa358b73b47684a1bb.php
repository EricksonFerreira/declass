<div class="minha-reserva-card" id="minhaReservaCard">
    <div id="minhaReservaSkeletonOverlay" class="minha-reserva-skeleton-overlay" style="display:none">
        <div class="minha-reserva-skeleton-box">
            <div class="skl-line skl-line--sm skl-line--40"></div>
            <div class="skl-line skl-line--lg skl-line--70 mt-2"></div>
        </div>
        <div class="minha-reserva-skeleton-box mt-3">
            <div class="skl-line skl-line--sm skl-line--30"></div>
            <div class="skl-line skl-line--md skl-line--90 mt-2"></div>
            <div class="skl-line skl-line--md skl-line--80 mt-1"></div>
        </div>
        <div class="minha-reserva-skeleton-box mt-3">
            <div class="skl-line skl-line--sm skl-line--25"></div>
            <div class="skl-line skl-line--md skl-line--60 mt-2"></div>
            <div class="skl-line skl-line--md skl-line--50 mt-1"></div>
            <div class="skl-line skl-line--md skl-line--70 mt-1"></div>
        </div>
        <div class="minha-reserva-skeleton-box mt-3">
            <div class="skl-line skl-line--xl skl-line--100"></div>
        </div>
        <div class="skl-spinner mt-3">
            <i class="fas fa-circle-notch fa-spin"></i>
            <span>Carregando reserva...</span>
        </div>
    </div>
    <div class="minha-reserva-card__header">
        <div class="minha-reserva-card__title">
            <span class="minha-reserva-card__icon"><i class="fas fa-receipt"></i></span>
            <div>
                <div class="minha-reserva-card__eyebrow">Resumo</div>
                <div class="minha-reserva-card__name">Minha Reserva</div>
            </div>
        </div>
    </div>

    <div class="minha-reserva-card__section" id="minhaReserva_periodo_section" style="display:none">
        <div class="minha-reserva-card__section-title">
            <i class="fas fa-route"></i>
            <span>Período</span>
        </div>
        <ul class="minha-reserva-list">
            <li class="minha-reserva-list__item" style="flex-direction:column;align-items:flex-start;gap:1px">
                <span class="minha-reserva-list__label" style="color:#6b7280;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em">
                    <i class="fas fa-map-marker-alt mr-1" style="color:#3b82f6"></i>Retirada
                </span>
                <span id="minhaReserva_estacao_retirada" style="font-size:.8rem;color:#1e40af;font-weight:600">—</span>
                <span id="minhaReserva_data_retirada" style="font-size:.78rem;color:#374151">—</span>
            </li>
            <li class="minha-reserva-list__item" style="flex-direction:column;align-items:flex-start;gap:1px;margin-top:4px">
                <span class="minha-reserva-list__label" style="color:#6b7280;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em">
                    <i class="fas fa-map-marker-alt mr-1" style="color:#10b981"></i>Devolução
                </span>
                <span id="minhaReserva_estacao_devolucao" style="font-size:.8rem;color:#065f46;font-weight:600">—</span>
                <span id="minhaReserva_data_devolucao" style="font-size:.78rem;color:#374151">—</span>
            </li>
        </ul>
    </div>

    <div class="minha-reserva-card__section">
        <div class="minha-reserva-card__section-title">
            <i class="fas fa-wallet"></i>
            <span>Valores</span>
        </div>
        <ul class="minha-reserva-list">
            <li class="minha-reserva-list__item d-none" id="minhaReserva_grupo_li">
                <span class="minha-reserva-list__label" style="color:#6b7280"><i class="fas fa-layer-group mr-1"></i>Grupo</span>
                <span class="minha-reserva-list__value" id="minhaReserva_grupo_nome" style="font-size:.82rem;color:#374151;text-align:right;max-width:60%">—</span>
            </li>
            <li class="minha-reserva-list__item">
                <span class="minha-reserva-list__label">Tarifa base</span>
                <span class="minha-reserva-list__value" id="tarifa_base">0</span>
            </li>
            <li class="minha-reserva-list__item d-none" id="minhaReserva_desconto_li"
                style="background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:5px 8px;margin-top:2px">
                <span class="minha-reserva-list__label" style="color:#16a34a;font-weight:600">
                    <i class="fas fa-tag mr-1"></i>Desconto
                    <span id="minhaReserva_cupom_badge" class="badge badge-xs ml-1" style="background:#16a34a;color:#fff;font-size:.68rem;letter-spacing:.04em"></span>
                </span>
                <span class="minha-reserva-list__value" style="color:#16a34a;font-weight:700" id="desconto">0%</span>
            </li>
            <li class="minha-reserva-list__item d-none" id="minhaReserva_taxa_coleta_li"
                style="flex-direction:column;align-items:flex-start;gap:2px;background:#fef3c7;border:1px solid #f59e0b;border-radius:6px;padding:6px 8px;margin-top:4px">
                <div class="d-flex justify-content-between w-100">
                    <span class="minha-reserva-list__label" style="color:#92400e;font-weight:700">
                        <i class="fas fa-exclamation-triangle mr-1"></i>* Retirada fora do expediente
                    </span>
                    <span class="minha-reserva-list__value" style="color:#92400e;font-weight:700" id="minhaReserva_taxa_coleta_val">R$ 0,00</span>
                </div>
                <small style="color:#b45309;font-size:.74rem">Horário selecionado está fora do horário de funcionamento da estação de retirada.</small>
            </li>
            <li class="minha-reserva-list__item d-none" id="minhaReserva_taxa_entrega_li"
                style="flex-direction:column;align-items:flex-start;gap:2px;background:#fef3c7;border:1px solid #f59e0b;border-radius:6px;padding:6px 8px;margin-top:4px">
                <div class="d-flex justify-content-between w-100">
                    <span class="minha-reserva-list__label" style="color:#92400e;font-weight:700">
                        <i class="fas fa-exclamation-triangle mr-1"></i>* Devolução fora do expediente
                    </span>
                    <span class="minha-reserva-list__value" style="color:#92400e;font-weight:700" id="minhaReserva_taxa_entrega_val">R$ 0,00</span>
                </div>
                <small style="color:#b45309;font-size:.74rem">Horário selecionado está fora do horário de funcionamento da estação de devolução.</small>
            </li>
        </ul>
    </div>

    <div class="minha-reserva-card__section" data-reserva-step="3">
        <div class="minha-reserva-card__section-title">
            <i class="fas fa-puzzle-piece"></i>
            <span>Opcionais</span>
        </div>
        <ul class="minha-reserva-list" id="minhaReserva_opcionals_ul">
            <li class="minha-reserva-list__empty">Selecione opcionais para aparecerem aqui.</li>
        </ul>
    </div>

    <div class="minha-reserva-card__total">
        <div class="minha-reserva-card__total-left">
            <div class="minha-reserva-card__total-label">Total</div>
            <div class="minha-reserva-card__total-hint" id="quantidade_dias">/ 0 dias</div>
        </div>
        <div class="minha-reserva-card__total-right">
            <div id="minhaReserva_valor_sem_desconto" class="d-none" style="font-size:.78rem;color:#9ca3af;text-decoration:line-through;text-align:right;margin-bottom:1px"></div>
            <div class="minha-reserva-card__total-value" id="valor_total_reserva">0</div>
            <div class="minha-reserva-card__total-sub">
                <span id="valor_diario_reserva">0</span> <span class="muted">/ por dia</span>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/components/card_valores.blade.php ENDPATH**/ ?>