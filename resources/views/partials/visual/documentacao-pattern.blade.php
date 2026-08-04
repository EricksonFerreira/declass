<style>
    .vehicle-show-page { padding-bottom: 8px; }
    .config-card {
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }
    .vs-panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        padding: 14px 16px;
        border-bottom: 1px solid #e7edf6;
    }
    .vs-panel-head__eyebrow {
        display: inline-block;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .vs-panel-head__title {
        color: #0f172a;
        font-weight: 800;
        font-size: 1rem;
        line-height: 1.2;
    }
    .vs-panel-head__sub {
        color: #64748b;
        font-size: .82rem;
    }
    .vehicle-table-wrap {
        max-height: none;
        overflow: auto;
    }
    .vehicle-list-table {
        margin-bottom: 0;
    }
    .vehicle-list-table thead th {
        white-space: nowrap;
        background: #fff;
        font-weight: 800;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        font-size: .72rem;
        letter-spacing: .06em;
    }
    .vehicle-list-table tbody td { vertical-align: middle; }
    .vehicle-list-table tbody tr:hover { background: #f8fafc; }
    .doc-filters {
        padding: 1rem;
        background: #fff;
    }
    .doc-filters__form .form-control,
    .doc-filters__form select,
    .doc-filter-control {
        background: #fff;
        border-radius: 12px;
        border-color: #dbe4ef;
        min-height: 42px;
    }
    .doc-filter-control:focus,
    .doc-filters__form .form-control:focus,
    .doc-filters__form select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .15);
    }
    .doc-filter-label {
        display: block;
        font-size: .75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #475569;
        margin-bottom: .35rem;
    }
    .kpi-card {
        border-radius: 18px;
        padding: 1rem 1.1rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
    }
    .kpi-label {
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 800;
        font-size: .72rem;
    }
    .kpi-value { font-size: 1.5rem; line-height: 1.1; }
    .kpi-subtitle { color: #64748b; font-size: .78rem; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: .73rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .status-badge--green { background: #dcfce7; color: #166534; }
    .status-badge--amber { background: #fef3c7; color: #92400e; }
    .status-badge--red { background: #fee2e2; color: #991b1b; }
    .status-badge--gray { background: #f1f5f9; color: #475569; }
    .status-badge--blue { background: #dbeafe; color: #1d4ed8; }
    .vs-badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .vs-badge-soft.is-warning {
        background: #fff7ed;
        color: #9a3412;
    }
    @media (max-width: 991.98px) {
        .vs-panel-head,
        .doc-filters { padding-left: 14px; padding-right: 14px; }
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td { font-size: .86rem; }
        .vs-panel-head { align-items: flex-start; flex-direction: column; }
    }
</style>
