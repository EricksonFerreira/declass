<div class="minha-reserva-card">
    <div class="minha-reserva-card__header">
        <div class="minha-reserva-card__title">
            <span class="minha-reserva-card__icon"><i class="fas fa-receipt"></i></span>
            <div>
                <div class="minha-reserva-card__eyebrow">Resumo</div>
                <div class="minha-reserva-card__name">Minha Reserva</div>
            </div>
        </div>
    </div>

    <div class="minha-reserva-card__section">
        <div class="minha-reserva-card__section-title">
            <i class="fas fa-wallet"></i>
            <span>Valores</span>
        </div>
        <ul class="minha-reserva-list">
            <li class="minha-reserva-list__item">
                <span class="minha-reserva-list__label">Tarifa base</span>
                <span class="minha-reserva-list__value" id="tarifa_base">0</span>
            </li>
            <li class="minha-reserva-list__item">
                <span class="minha-reserva-list__label">Desconto</span>
                <span class="minha-reserva-list__value" id="desconto">0 %</span>
            </li>
            <li class="minha-reserva-list__item d-none" id="minhaReserva_taxa_coleta_li">
                <span class="minha-reserva-list__label text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Taxa coleta fora exp.</span>
                <span class="minha-reserva-list__value text-warning" id="minhaReserva_taxa_coleta_val">R$ 0,00</span>
            </li>
            <li class="minha-reserva-list__item d-none" id="minhaReserva_taxa_entrega_li">
                <span class="minha-reserva-list__label text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Taxa entrega fora exp.</span>
                <span class="minha-reserva-list__value text-warning" id="minhaReserva_taxa_entrega_val">R$ 0,00</span>
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
            <div class="minha-reserva-card__total-value" id="valor_total_reserva">0</div>
            <div class="minha-reserva-card__total-sub">
                <span id="valor_diario_reserva">0</span> <span class="muted">/ por dia</span>
            </div>
        </div>
    </div>
</div>
