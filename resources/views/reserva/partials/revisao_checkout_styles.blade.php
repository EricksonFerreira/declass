<style>
    .rev-page { padding: 8px 0 24px; }
    .rev-shell {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 30%);
        border: 1px solid #e5edf6;
        border-radius: 22px;
        box-shadow: 0 18px 38px rgba(15,23,42,.06);
        padding: 18px;
    }
    .rev-top { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; margin-bottom:18px; }
    .rev-title { margin:0; font-size:1.9rem; font-weight:900; color:#0f172a; line-height:1.1; }
    .rev-sub { color:#64748b; margin-top:6px; max-width: 740px; }
    .rev-actions { display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
    .rev-btn {
        border:1px solid #dbe4f0; border-radius:14px; padding:10px 14px; background:#fff;
        color:#0f172a; font-weight:700; text-decoration:none; transition:.2s ease;
    }
    .rev-btn:hover { text-decoration:none; color:#0f172a; transform:translateY(-1px); box-shadow:0 8px 16px rgba(15,23,42,.05); }
    .rev-btn--primary { background:#2563eb; color:#fff; border-color:#2563eb; }
    .rev-btn--primary:hover { color:#fff; }
    .rev-badge { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; font-size:.88rem; font-weight:800; margin-left:12px; }
    .rev-badge--green { background:#ecfdf5; color:#166534; border:1px solid #bbf7d0; }
    .card-soft { border:1px solid #e5edf6; border-radius:18px; box-shadow:0 12px 28px rgba(15,23,42,.06); background:#fff; overflow:hidden; }
    .card-soft .card-header { background:#fff; border-bottom:1px solid #edf2f7; font-weight:800; color:#0f172a; padding:14px 16px; }
    .rev-table-header {
        display:flex;
        justify-content:space-between;
        gap:16px;
        align-items:flex-start;
        background:linear-gradient(135deg,#f8fbff,#ffffff);
    }
    .rev-table-title { font-size:1.02rem; font-weight:900; color:#0f172a; }
    .rev-table-subtitle { color:#64748b; font-size:.92rem; margin-top:4px; }
    .rev-table-legend { display:flex; flex-wrap:wrap; gap:10px 14px; color:#64748b; font-size:.84rem; font-weight:700; align-items:center; }
    .rev-table-legend span { display:inline-flex; align-items:center; gap:4px; }
    .sum-card { display:flex; gap:14px; padding:18px; min-height:110px; }
    .sum-ico { width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; background:#eff6ff; color:#2563eb; flex:0 0 auto; }
    .sum-label { font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:#64748b; font-weight:800; }
    .sum-value { font-size:1.2rem; font-weight:800; color:#0f172a; line-height:1.2; }
    .sum-meta { color:#64748b; font-size:.92rem; margin-top:4px; }
    .soft-table thead th { background:#f8fbff; color:#64748b; text-transform:uppercase; font-size:.77rem; letter-spacing:.06em; border-top:0; }
    .soft-table td { vertical-align:top; }
    .img-strip { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
    .img-thumb { width:92px; height:68px; border-radius:10px; object-fit:cover; border:1px solid #e2e8f0; background:#fff; }
    .mini-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; font-size:.78rem; font-weight:800; border:1px solid #dbe4f0; background:#fff; }
    .mini-chip--warn { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .mini-chip--ok { background:#ecfdf5; color:#166534; border-color:#bbf7d0; }
    .mini-chip--info { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
    .rev-local-dot {
        width:10px;
        height:10px;
        border-radius:999px;
        margin-top:5px;
        background:linear-gradient(135deg,#2563eb,#7dd3fc);
        box-shadow:0 0 0 4px rgba(37,99,235,.1);
        flex:0 0 auto;
    }
    .rev-value-pill {
        display:inline-flex;
        align-items:center;
        padding:7px 10px;
        border-radius:999px;
        background:#f8fbff;
        color:#0f172a;
        font-weight:800;
        font-size:.84rem;
        border:1px solid #dbe4f0;
        margin-top:6px;
    }
    .rev-price-cell {
        font-size:1.02rem;
        color:#0f172a;
        white-space:nowrap;
    }
    .rev-danos-table tbody tr {
        transition:background-color .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .rev-danos-table tbody tr:hover {
        background:linear-gradient(90deg,#f8fbff, #ffffff);
        box-shadow:inset 0 0 0 999px rgba(37,99,235,.02);
    }
    .rev-danos-table tbody td {
        padding-top:16px;
        padding-bottom:16px;
    }
    .side-list { display:grid; gap:12px; }
    .side-item { border:1px solid #e5edf6; border-radius:14px; padding:14px; background:#fff; }
    .side-item strong { display:block; color:#0f172a; margin-bottom:2px; }
    .side-item small { color:#64748b; }
    .sticky-actions {
        position:sticky; bottom:0; z-index:10; background:rgba(255,255,255,.96); backdrop-filter:blur(8px);
        border-top:1px solid #e5edf6; box-shadow:0 -10px 24px rgba(15,23,42,.06);
    }
    .rev-summary-banner {
        border:1px solid #dbeafe; background:linear-gradient(135deg,#eff6ff,#f8fbff); border-radius:16px; padding:16px;
    }
    .rev-summary-banner h5 { margin:0 0 4px; font-weight:900; color:#0f172a; }
    .rev-summary-banner p { margin:0; color:#475569; }
    .rev-summary-value { font-size:1.5rem; font-weight:900; color:#16a34a; }
    .dano-card { border:1px solid #e5edf6; border-radius:16px; padding:14px; background:#fff; box-shadow:0 8px 20px rgba(15,23,42,.04); }
    .dano-card + .dano-card { margin-top:12px; }
    .dano-head { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; }
    .dano-title { font-weight:900; color:#0f172a; margin:0; }
    .dano-sub { color:#64748b; font-size:.92rem; margin-top:4px; }
    .dano-grid { display:grid; grid-template-columns:1.2fr 1fr auto; gap:12px; align-items:center; margin-top:12px; }
    .dano-actions { display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
    .img-strip--compact .img-thumb { width:74px; height:56px; }
    .rev-danos-scroll {
        max-height: 68vh;
        overflow-y: auto;
        padding-right: 6px;
    }
    .rev-danos-scroll::-webkit-scrollbar { width: 10px; }
    .rev-danos-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
        border: 2px solid #f8fbff;
    }
    .rev-danos-scroll::-webkit-scrollbar-track { background: #f8fbff; }
    .rev-danos-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background:#f8fbff;
        box-shadow:0 1px 0 #e5edf6;
    }
    .rev-danos-table tbody tr:hover {
        background: #f8fbff;
    }
    .rev-modal-xl { max-width: 1180px; }
    .rev-modal-card { border:1px solid #dbe4f0; border-radius:16px; box-shadow:0 8px 20px rgba(15,23,42,.04); background:#fff; }
    .rev-modal-card__body { padding:18px; }
    .rev-modal-label { font-size:.78rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; }
    .rev-modal-kv { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:8px; }
    .rev-modal-kv:last-child { margin-bottom:0; }
    .rev-modal-kv span { color:#64748b; }
    .rev-modal-kv strong { color:#0f172a; text-align:right; }
    .rev-modal-preview { font-size:1rem; font-weight:800; color:#0f172a; }
    .rev-modal-photo-empty { min-height: 120px; display:flex; align-items:center; justify-content:center; border:1px dashed #dbe4f0; border-radius:12px; color:#64748b; background:#f8fbff; }
    .rev-modal-summary-green { color:#16a34a; }
    .dano-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 36px 90px rgba(15, 23, 42, 0.28);
        background: #fff;
    }
    .dano-modal__header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        background: linear-gradient(135deg, #0f172a 0%, #172554 52%, #1d4ed8 100%);
        color: #fff;
        overflow: hidden;
    }
    .dano-modal__header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px);
        background-size: 18px 18px;
        pointer-events: none;
    }
    .dano-modal__header::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        height: 3px;
        background: linear-gradient(90deg, #22c55e, #38bdf8, #f59e0b);
    }
    .dano-modal__header-left {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 1;
        min-width: 0;
    }
    .dano-modal__header-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.16);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #f8fafc;
        flex: 0 0 auto;
        backdrop-filter: blur(8px);
    }
    .dano-modal__eyebrow {
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: rgba(226, 232, 240, 0.78);
        margin-bottom: 3px;
    }
    .dano-modal__title {
        margin: 0;
        font-size: 1rem;
        line-height: 1.2;
        font-weight: 900;
        color: #fff;
    }
    .dano-modal__close {
        position: relative;
        z-index: 1;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.16);
        background: rgba(255,255,255,0.08);
        color: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .18s ease;
        flex: 0 0 auto;
    }
    .dano-modal__close:hover {
        background: rgba(239, 68, 68, 0.30);
        border-color: rgba(239, 68, 68, 0.48);
        color: #fff;
    }
    .dano-modal__body {
        padding: 18px 20px;
        background: linear-gradient(180deg, #f8fbff 0%, #f3f7fc 100%);
        max-height: 72vh;
        overflow-y: auto;
    }
    .dano-modal__footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 20px;
        background: #fff;
        border-top: 1px solid #e5edf6;
    }
    .dano-block {
        border: 1px solid #dbe4f0;
        border-left: 4px solid #2563eb;
        border-radius: 18px;
        background: #fff;
        padding: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        margin-bottom: 12px;
    }
    .dano-block__label {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding-bottom: 12px;
        margin-bottom: 14px;
        border-bottom: 1px dashed #e5edf6;
    }
    .dano-block__label > i {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        flex: 0 0 auto;
    }
    .dano-block__label strong {
        display: block;
        color: #0f172a;
        font-size: 0.88rem;
        font-weight: 900;
    }
    .dano-block__label span,
    .dano-modal__body .small.text-muted {
        color: #64748b;
    }
    .dano-block label {
        font-size: 0.78rem;
        font-weight: 800;
        color: #334155;
        margin-bottom: 5px;
    }
    .dano-block .form-control {
        border-radius: 12px;
        border-color: #dbe4f0;
        background: #fff;
        box-shadow: none;
    }
    .dano-block .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59,130,246,0.14);
    }
    .dano-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        border-radius: 12px;
        padding: 10px 18px;
        font-size: 0.86rem;
        font-weight: 900;
        transition: all .18s ease;
        letter-spacing: -0.01em;
    }
    .dano-btn--ghost {
        background: #fff;
        color: #475569;
        border: 1px solid #dbe4f0;
    }
    .dano-btn--ghost:hover {
        background: #f8fafc;
        color: #0f172a;
        transform: translateY(-1px);
    }
    .dano-btn--primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.24);
    }
    .dano-btn--primary:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(37, 99, 235, 0.28);
    }
    .dano-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(92px, 1fr));
        gap: 10px;
    }
    .dano-preview-grid .img-thumb {
        width: 100%;
        height: 84px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #dbe4f0;
        box-shadow: 0 8px 18px rgba(15,23,42,0.06);
    }
    .dano-decisao-group {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .dano-decisao-card {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-height: 94px;
        padding: 14px 14px 13px;
        border: 1px solid #dbe4f0;
        border-radius: 16px;
        background: #f8fbff;
        cursor: pointer;
        transition: all .18s ease;
        overflow: hidden;
    }
    .dano-decisao-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(37,99,235,0.05), rgba(34,197,94,0.05));
        opacity: 0;
        transition: opacity .18s ease;
    }
    .dano-decisao-card:hover {
        transform: translateY(-1px);
        border-color: #93c5fd;
        box-shadow: 0 10px 22px rgba(15,23,42,0.08);
    }
    .dano-decisao-card input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .dano-decisao-card__title {
        position: relative;
        font-weight: 900;
        color: #0f172a;
        font-size: 0.92rem;
        z-index: 1;
    }
    .dano-decisao-card__subtitle {
        position: relative;
        color: #64748b;
        font-size: 0.78rem;
        line-height: 1.35;
        z-index: 1;
    }
    .dano-decisao-card--primary {
        border-color: #93c5fd;
        background: linear-gradient(180deg, #eff6ff 0%, #f8fbff 100%);
    }
    .dano-decisao-card--muted {
        border-style: dashed;
    }
    .dano-decisao-card:has(input:checked) {
        border-color: #2563eb;
        background: linear-gradient(180deg, #dbeafe 0%, #eff6ff 100%);
        box-shadow: 0 12px 22px rgba(37,99,235,0.10);
    }
    .dano-decisao-card:has(input:checked)::before {
        opacity: 1;
    }
    .dano-decisao-card:has(input:checked) .dano-decisao-card__title {
        color: #1d4ed8;
    }
    .dano-radio-group {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }
    .dano-radio-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 74px;
        padding: 10px 8px;
        border: 1px solid #dbe4f0;
        border-radius: 14px;
        background: #f8fbff;
        color: #0f172a;
        transition: all .18s ease;
        cursor: pointer;
    }
    .dano-radio-card:hover {
        transform: translateY(-1px);
        border-color: #93c5fd;
        box-shadow: 0 8px 18px rgba(37,99,235,0.08);
    }
    .dano-radio-card input { display: none; }
    .dano-radio-card i { font-size: 1rem; margin-bottom: 6px; }
    .dano-radio-card span { font-size: 0.78rem; font-weight: 800; line-height: 1.2; }
    .dano-radio-card--green i { color: #16a34a; }
    .dano-radio-card--amber i { color: #d97706; }
    .dano-radio-card--red i { color: #dc2626; }
    .dano-radio-card:has(input:checked) {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .dano-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        border: 1px dashed #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
        cursor: pointer;
        font-weight: 800;
        transition: all .18s ease;
    }
    .dano-upload-btn:hover {
        background: #dbeafe;
        transform: translateY(-1px);
    }
    .dano-hint {
        display: block;
        margin-top: 8px;
        color: #64748b;
    }
    .dano-select-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    @media (max-width: 991px) {
        .rev-top { flex-direction:column; }
        .rev-actions { justify-content:flex-start; }
        .dano-grid { grid-template-columns:1fr; }
        .rev-danos-scroll { max-height: none; }
        .rev-table-header { flex-direction:column; }
        .rev-table-legend { justify-content:flex-start; }
        .dano-radio-group { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dano-decisao-group { grid-template-columns: 1fr; }
        .dano-modal__header, .dano-modal__footer { padding-left: 14px; padding-right: 14px; }
        .dano-modal__body { padding-left: 14px; padding-right: 14px; }
    }
</style>
