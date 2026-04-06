<style>
    /* ================================================
       VARIAVEIS
    ================================================ */
    :root {
        --c-ink:    #0c1428;
        --c-navy:   #1a2848;
        --c-indigo: #4338ca;
        --c-sky:    #0ea5e9;
        --c-green:  #10b981;
        --c-amber:  #f59e0b;
        --c-red:    #ef4444;
        --c-slate:  #64748b;
        --c-border: #e2e9f4;
        --c-bg:     #f2f5fb;
        --c-card:   #ffffff;
        --r-card:   18px;
        --shadow-sm: 0 2px 8px rgba(12,20,40,.07);
        --shadow-md: 0 6px 24px rgba(12,20,40,.10);
        --shadow-lg: 0 16px 48px rgba(12,20,40,.13);
    }

    .content-wrapper { background: var(--c-bg); }

    /* ================================================
       CARD PRINCIPAL
    ================================================ */
    .ci-card {
        border-radius: 20px;
        overflow: hidden;
        background: var(--c-card);
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--c-border);
    }

    /* ================================================
       HEADER ESCURO
    ================================================ */
    .ci-card-header {
        position: relative;
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        padding: 1.75rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        overflow: hidden;
    }

    .ci-card-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px);
        background-size: 22px 22px;
        pointer-events: none;
    }

    .ci-card-header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
    }

    .ci-header-left {
        position: relative;
        display: flex;
        align-items: center;
        gap: 16px;
        z-index: 1;
    }

    .ci-header-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #fff;
        flex-shrink: 0;
        backdrop-filter: blur(8px);
    }

    .ci-header-eyebrow {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(148,163,184,0.8);
        margin-bottom: 3px;
    }

    .ci-header-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 3px;
        letter-spacing: -0.02em;
    }

    .ci-header-sub {
        font-size: 0.78rem;
        color: rgba(148,163,184,0.75);
        margin: 0;
    }

    .ci-header-right {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        z-index: 1;
    }

    .ci-date-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        color: rgba(226,232,240,0.9);
        backdrop-filter: blur(6px);
    }

    .ci-btn-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.18);
        color: rgba(226,232,240,0.9);
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        transition: background 0.2s, color 0.2s;
        backdrop-filter: blur(6px);
    }

    .ci-btn-back:hover {
        background: rgba(255,255,255,0.18);
        color: #fff;
        text-decoration: none;
    }

    /* ================================================
       BODY
    ================================================ */
    .ci-body {
        padding: 1.75rem;
        background: var(--c-bg);
    }

    /* ================================================
       STEP INDICATOR
    ================================================ */
    .step-indicator {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 2rem;
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 14px;
        padding: 6px;
        overflow-x: auto;
    }

    .step-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 10px 16px;
        border-radius: 10px;
        flex-shrink: 0;
        transition: background 0.2s;
    }

    .step-btn:hover { background: #f1f5ff; }

    .step-num {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid #d0dff0;
        background: #f4f8ff;
        color: #94a3b8;
        font-weight: 800;
        font-size: 0.78rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.25s;
    }

    .step-btn.active { background: #eff3ff; }

    .step-btn.active .step-num {
        border-color: transparent;
        background: linear-gradient(135deg, var(--c-indigo), var(--c-sky));
        color: #fff;
        box-shadow: 0 3px 10px rgba(67,56,202,0.35);
    }

    .step-btn.completed .step-num {
        border-color: transparent;
        background: var(--c-green);
        color: #fff;
    }

    .step-label-text {
        font-size: 0.78rem;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .step-btn.active .step-label-text { color: var(--c-indigo); font-weight: 700; }
    .step-btn.completed .step-label-text { color: var(--c-green); }

    .step-divider {
        flex: 1;
        height: 1px;
        background: var(--c-border);
        min-width: 12px;
    }

    /* ================================================
       WIZARD STEPS
    ================================================ */
    .wizard-step { display: none; }

    .wizard-step.active {
        display: block;
        animation: fadeSlideIn 0.22s ease;
    }

    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .step-head {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--c-border);
    }

    .step-head h5 {
        font-weight: 800;
        color: var(--c-ink);
        margin-bottom: 4px;
        font-size: 1.02rem;
        letter-spacing: -0.01em;
    }

    .step-head p {
        color: var(--c-slate);
        font-size: 0.83rem;
        margin: 0;
    }

    /* ================================================
       CONFIG BLOCKS
    ================================================ */
    .ci-block {
        border: 1px solid var(--c-border);
        border-left: 3px solid var(--c-indigo);
        border-radius: 14px;
        padding: 1.1rem 1.3rem;
        background: #fff;
        margin-bottom: 1rem;
        transition: box-shadow 0.2s;
    }

    .ci-block:hover { box-shadow: var(--shadow-sm); }

    .ci-block--no-mb { margin-bottom: 0; }

    .ci-block--amber {
        border-left-color: var(--c-amber);
    }

    .ci-block-label {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px dashed #e5eaf3;
    }

    .ci-block-label > i {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #eef2ff;
        color: var(--c-indigo);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .ci-block--amber .ci-block-label > i {
        background: #fffbeb;
        color: var(--c-amber);
    }

    .ci-block-label strong {
        display: block;
        color: var(--c-ink);
        font-size: 0.88rem;
        font-weight: 700;
    }

    .ci-block-label span {
        color: var(--c-slate);
        font-size: 0.76rem;
        margin-top: 2px;
        display: block;
    }

    .ci-block label {
        font-size: 0.76rem;
        font-weight: 600;
        color: #3d4f63;
        margin-bottom: 4px;
        display: block;
    }

    .ci-block .form-control {
        border-radius: 9px;
        border-color: #dce6f4;
        font-size: 0.84rem;
        background: #fcfeff;
        transition: border-color 0.18s, box-shadow 0.18s;
    }

    .ci-block .form-control:focus {
        border-color: var(--c-indigo);
        box-shadow: 0 0 0 3px rgba(67,56,202,0.10);
    }

    .req { color: var(--c-red); font-size: 0.76rem; }

    .ci-hint {
        display: block;
        font-size: 0.71rem;
        color: #8fa5c0;
        margin-top: 4px;
        font-style: italic;
    }

    /* Select2 override */
    .select2-container .select2-selection--single {
        border-radius: 9px !important;
        border-color: #dce6f4 !important;
        height: calc(1.5em + .5rem + 2px) !important;
        font-size: 0.84rem !important;
        background: #fcfeff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + .5rem + 2px) !important;
        font-size: 0.84rem !important;
        color: #3d4f63 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + .5rem + 2px) !important;
    }

    /* ================================================
       CAR VIEWS GRID
    ================================================ */
    .car-views-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }

    .car-view-card {
        border: 1px solid var(--c-border);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        cursor: pointer;
        transition: all 0.22s ease;
    }

    .car-view-card:hover {
        border-color: #c7d4ff;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(67,56,202,0.14);
    }

    .car-view-card__img {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #f6f8ff, #eef1ff);
    }

    .car-view-card__img img {
        width: 100%;
        height: 100px;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
    }

    .car-view-card:hover .car-view-card__img img {
        transform: scale(1.04);
    }

    .car-view-card__overlay {
        position: absolute;
        inset: 0;
        background: rgba(67,56,202,0.72);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        opacity: 0;
        transition: opacity 0.22s;
    }

    .car-view-card__overlay i { font-size: 1.3rem; }

    .car-view-card:hover .car-view-card__overlay { opacity: 1; }

    .car-view-card__label {
        padding: 7px 10px;
        font-size: 0.72rem;
        font-weight: 700;
        text-align: center;
        color: var(--c-ink);
        background: #fafbff;
        border-top: 1px solid var(--c-border);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .car-view-card__label i { color: var(--c-indigo); font-size: 0.7rem; }

    /* ================================================
       DAMAGE TABLES
    ================================================ */
    .ci-damages-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .damage-table-wrap {
        overflow-x: auto;
        border-radius: 10px;
        border: 1px solid var(--c-border);
    }

    .damage-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .damage-table thead tr {
        background: #f0f2ff;
    }

    .damage-table th {
        padding: 9px 12px;
        text-align: left;
        font-weight: 700;
        color: var(--c-indigo);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-bottom: 1px solid #dce2ff;
    }

    .ci-block--amber .damage-table thead tr { background: #fffbeb; }
    .ci-block--amber .damage-table th { color: #92400e; border-bottom-color: #fde68a; }

    .damage-table td {
        padding: 9px 12px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .damage-table tbody tr:last-child td { border-bottom: 0; }

    .damage-table tbody tr:hover td { background: #fafbff; }

    .damage-table img {
        width: 46px;
        height: 46px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid var(--c-border);
    }

    .damage-empty {
        text-align: center;
        padding: 22px !important;
        color: #94a3b8;
        font-size: 0.8rem;
    }

    .damage-empty i {
        display: block;
        font-size: 1.5rem;
        margin-bottom: 6px;
        opacity: 0.5;
    }

    /* ================================================
       SUMMARY CARD (Passo 3)
    ================================================ */
    .ci-summary-card {
        display: flex;
        align-items: center;
        gap: 18px;
        background: linear-gradient(135deg, #f5f4ff 0%, #eef2ff 100%);
        border: 1px solid #c7d0ff;
        border-radius: 16px;
        padding: 20px 22px;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    .ci-summary-card__icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--c-indigo), var(--c-sky));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
        box-shadow: 0 6px 18px rgba(67,56,202,0.3);
    }

    .ci-summary-card__content {
        flex: 1;
        min-width: 0;
    }

    .ci-summary-card__content strong {
        display: block;
        color: var(--c-ink);
        font-size: 0.92rem;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .ci-summary-card__content p {
        margin: 0;
        font-size: 0.82rem;
        color: var(--c-slate);
        line-height: 1.5;
    }

    .ci-btn-create {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--c-indigo), var(--c-sky));
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-size: 0.88rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 6px 18px rgba(67,56,202,0.3);
        white-space: nowrap;
        letter-spacing: -0.01em;
    }

    .ci-btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 26px rgba(67,56,202,0.4);
        color: #fff;
    }

    /* ================================================
       WIZARD ACTIONS
    ================================================ */
    .wizard-actions {
        margin-top: 2rem;
        padding-top: 1.1rem;
        border-top: 1px solid var(--c-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .wizard-actions-right {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        border-radius: 10px;
        padding: 11px 22px;
        font-size: 0.84rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s;
        letter-spacing: -0.01em;
    }

    .btn-action-secondary {
        background: #fff;
        color: #475569;
        border: 1px solid var(--c-border);
        box-shadow: var(--shadow-sm);
    }

    .btn-action-secondary:hover { background: #f1f5f9; color: var(--c-ink); }

    .btn-action-primary {
        background: linear-gradient(135deg, var(--c-indigo), #6366f1);
        color: #fff;
        box-shadow: 0 4px 12px rgba(67,56,202,0.28);
    }

    .btn-action-primary:hover {
        opacity: 0.92;
        transform: translateY(-1px);
        color: #fff;
    }

    .btn-action-success {
        background: linear-gradient(135deg, var(--c-green), #059669);
        color: #fff;
        box-shadow: 0 4px 12px rgba(16,185,129,0.28);
    }

    .btn-action-success:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: #fff;
    }

    /* ================================================
       MODAL DANO — override para o design
    ================================================ */
    #detalhesDanoModal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(12,20,40,0.22);
    }

    #detalhesDanoModal .modal-header {
        background: linear-gradient(135deg, #0c1428, #1a2848);
        border: 0;
        padding: 16px 20px;
    }

    #detalhesDanoModal .modal-header::after {
        display: none;
    }

    #detalhesDanoModal .modal-title {
        color: #fff;
        font-size: 0.9rem;
    }

    #detalhesDanoModal .modal-title span {
        color: #fbbf24;
    }

    #detalhesDanoModal .close {
        color: rgba(255,255,255,0.7);
        text-shadow: none;
        opacity: 1;
    }

    #detalhesDanoModal .modal-body {
        padding: 20px;
        background: #f8faff;
    }

    #detalhesDanoModal .modal-footer {
        background: #f0f2ff;
        border-top: 1px solid #dce2ff;
        padding: 12px 20px;
    }

    #detalhesDanoModal .btn-primary {
        background: linear-gradient(135deg, var(--c-indigo), #6366f1);
        border: 0;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 8px 20px;
        box-shadow: 0 3px 10px rgba(67,56,202,0.3);
    }

    /* ================================================
       RESPONSIVE
    ================================================ */
    @media (max-width: 1100px) {
        .car-views-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 992px) {
        .ci-damages-grid { grid-template-columns: 1fr; }
        .step-btn { padding: 8px 12px; }
        .step-label-text span { display: none; }
    }

    @media (max-width: 768px) {
        .ci-card-header { flex-direction: column; align-items: flex-start; }
        .ci-body { padding: 1.1rem; }
        .ci-header-right { width: 100%; }
        .car-views-grid { grid-template-columns: repeat(2, 1fr); }
        .ci-summary-card { flex-direction: column; align-items: flex-start; }
        .ci-btn-create { width: 100%; justify-content: center; }
        .wizard-actions, .wizard-actions-right { width: 100%; }
        .wizard-actions-right { flex-direction: column; }
        .btn-action { width: 100%; justify-content: center; }
    }