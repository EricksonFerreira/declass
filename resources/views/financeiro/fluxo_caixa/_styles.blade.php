<style>
    .fc-page {
        --fc-ink: #0f172a;
        --fc-muted: #64748b;
        --fc-line: #dbe6f3;
        --fc-soft: #f4f7fb;
        --fc-blue: #2563eb;
        --fc-green: #059669;
        --fc-red: #dc2626;
        --fc-amber: #d97706;
    }

    .fc-card {
        border: 1px solid var(--fc-line) !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06) !important;
        overflow: hidden;
    }

    .fc-card .card-header {
        background: linear-gradient(180deg, #f8fbff 0%, #f2f6fc 100%) !important;
        border-bottom: 1px solid #e3edf9 !important;
    }

    .fc-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .fc-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: var(--fc-ink);
        font-size: .95rem;
        font-weight: 800;
    }

    .fc-title i {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e8f0ff;
        color: var(--fc-blue);
    }

    .fc-subtitle {
        display: block;
        margin-top: 2px;
        color: var(--fc-muted);
        font-size: .75rem;
        font-weight: 500;
    }

    .fc-filter {
        background: #f8fbff;
    }

    .fc-label {
        display: block;
        margin-bottom: 5px;
        color: #64748b;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .fc-page .form-control {
        border-color: #d7e3f2;
        border-radius: 8px;
        min-height: 38px;
        font-size: .85rem;
        color: var(--fc-ink);
    }

    .fc-page .form-control:focus {
        border-color: var(--fc-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }

    .fc-btn {
        border-radius: 8px !important;
        font-weight: 800 !important;
        font-size: .82rem !important;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .fc-kpi {
        min-height: 118px;
        border: 1px solid var(--fc-line);
        border-radius: 8px;
        background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
        padding: 16px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
    }

    .fc-kpi--receita { border-left: 4px solid var(--fc-green); }
    .fc-kpi--despesa { border-left: 4px solid var(--fc-red); }
    .fc-kpi--saldo { border-left: 4px solid #0891b2; }
    .fc-kpi--projetado { border-left: 4px solid var(--fc-blue); }

    .fc-kpi span {
        color: var(--fc-muted);
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .fc-kpi strong {
        display: block;
        margin-top: 6px;
        color: var(--fc-ink);
        font-size: 1.35rem;
        line-height: 1.15;
    }

    .fc-kpi small {
        color: var(--fc-muted);
        font-size: .75rem;
    }

    .fc-table {
        margin: 0;
        font-size: .86rem;
    }

    .fc-table thead th {
        background: #f2f6fc;
        border-bottom: 1px solid #dbe6f3;
        color: #64748b;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .fc-table tbody td {
        border-top: 1px solid #edf2f7;
        vertical-align: middle;
    }

    .fc-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        padding: 5px 9px;
        font-size: .72rem;
        font-weight: 800;
    }

    .fc-badge--receita,
    .bg-success-light {
        background: #dcfce7 !important;
        color: #047857 !important;
    }

    .fc-badge--despesa,
    .bg-danger-light {
        background: #fee2e2 !important;
        color: #b91c1c !important;
    }

    .fc-badge--pendente {
        background: #fef3c7;
        color: #92400e;
    }

    .fc-badge--pago {
        background: #d1fae5;
        color: #047857;
    }

    .fc-badge--cancelado {
        background: #e5e7eb;
        color: #475569;
    }

    .fc-form-card {
        max-width: 980px;
        /* margin: 0 auto; */
    }

    .fc-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .text-xs { font-size: .75rem; }

    @media (max-width: 767.98px) {
        .fc-actions,
        .fc-head {
            align-items: stretch;
        }

        .fc-actions .btn,
        .fc-filter .btn {
            width: 100%;
        }
    }
</style>
