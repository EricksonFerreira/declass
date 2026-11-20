{{-- Estilo compartilhado dos modais de turma (nova turma / geração em massa). Incluir dentro de @once. --}}
<style>
    .tm-modal .modal-dialog { max-width: 660px; }
    .tm-modal .modal-content { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 24px 60px rgba(11,18,32,.28); }
    .tm-hero { display: flex; align-items: center; gap: 13px; padding: 16px 20px; color: #fff; }
    .tm-hero--blue { background: linear-gradient(135deg, #0d6efd, #084298); }
    .tm-hero--violet { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
    .tm-hero__icon {
        width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
        background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.3);
        display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
    }
    .tm-hero__info { flex: 1; min-width: 0; }
    .tm-hero__title { margin: 0; font-size: 1.02rem; font-weight: 800; line-height: 1.3; }
    .tm-hero__sub { font-size: .75rem; opacity: .85; margin-top: 2px; }
    .tm-hero__close {
        width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
        border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.12);
        color: #fff; font-size: .8rem; display: flex; align-items: center; justify-content: center;
    }
    .tm-hero__close:hover { background: rgba(255,255,255,.3); color: #fff; }
    .tm-modal .modal-body { padding: 16px 20px 6px; }
    .tm-sec {
        display: flex; align-items: center; gap: 8px;
        font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em;
        color: #64748b; margin: 14px 0 10px;
    }
    .tm-sec:first-child { margin-top: 0; }
    .tm-sec i {
        width: 22px; height: 22px; border-radius: 7px;
        background: #eff6ff; color: #2563eb;
        display: flex; align-items: center; justify-content: center; font-size: .62rem;
    }
    .tm-sec--violet i { background: #f5f3ff; color: #7c3aed; }
    .tm-chips { display: flex; flex-wrap: wrap; gap: 7px; }
    .tm-chip { position: relative; margin: 0; cursor: pointer; }
    .tm-chip input { position: absolute; opacity: 0; pointer-events: none; }
    .tm-chip span {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: .76rem; font-weight: 700; color: #475569;
        background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 999px;
        padding: 6px 13px; transition: all .15s; user-select: none;
    }
    .tm-chip span::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; transition: background .15s; }
    .tm-chip input:checked + span { color: #1d4ed8; background: #eff6ff; border-color: #93c5fd; }
    .tm-chip input:checked + span::before { background: #2563eb; }
    .tm-chip--violet input:checked + span { color: #6d28d9; background: #f5f3ff; border-color: #c4b5fd; }
    .tm-chip--violet input:checked + span::before { background: #7c3aed; }
    .tm-modal label.font-weight-bold { font-size: .78rem; color: #334155; }
    .tm-modal .form-control { border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .85rem; }
    .tm-modal .form-control:focus { border-color: #93c5fd; box-shadow: 0 0 0 .15rem rgba(37,99,235,.12); }
    .tm-preview {
        max-height: 210px; overflow-y: auto;
        background: #f8fafc; border: 1.5px solid #e8edf5; border-radius: 12px;
        padding: 10px 12px; font-size: .82rem;
    }
    .tm-foot {
        display: flex; align-items: center; justify-content: flex-end; gap: 8px; flex-wrap: wrap;
        padding: 14px 20px; background: #f8fafc; border-top: 1px solid #eef2f7;
    }
    .tm-foot__hint { margin-right: auto; font-size: .72rem; color: #94a3b8; }
    @media (max-width: 575.98px) {
        .tm-hero { padding: 14px 16px; }
        .tm-hero__icon { width: 40px; height: 40px; border-radius: 11px; font-size: 1rem; }
        .tm-modal .modal-body { padding: 14px 16px 4px; }
        .tm-foot { padding: 12px 16px; }
    }
</style>
