<style>
    .hs-app-header { display:flex; align-items:center; gap:.85rem; padding:1rem 1.2rem; border-radius:18px; margin-bottom:1rem; color:#fff; background:#007bff; box-shadow:0 6px 20px rgba(0,123,255,.28); }
    .hs-app-avatar { width:52px; height:52px; border-radius:50%; object-fit:cover; border:3px solid rgba(255,255,255,.45); flex-shrink:0; }
    .hs-app-ola { display:flex; flex-direction:column; min-width:0; }
    .hs-app-ola b { font-size:1.05rem; font-weight:800; }
    .hs-app-ola small { font-size:.72rem; opacity:.85; }
    .hs-app-extra { margin-left:auto; }
    .hs-app-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:.7rem; margin-bottom:.6rem; }
    .hs-app { display:flex; flex-direction:column; align-items:center; gap:.45rem; padding:.8rem .4rem .7rem; background:#fff; border:1px solid #eef1f6; border-radius:14px; text-decoration:none !important; transition:transform .15s, box-shadow .15s; }
    .hs-app:hover { transform:translateY(-3px); box-shadow:0 6px 16px rgba(15,23,42,.10); }
    .hs-app-icone { position:relative; width:52px; height:52px; border-radius:16px; background:var(--app); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.25rem; box-shadow:0 4px 10px color-mix(in srgb, var(--app) 40%, transparent); }
    .hs-app-badge { position:absolute; top:-7px; right:-9px; min-width:20px; height:20px; padding:0 5px; border-radius:10px; background:#111827; border:2px solid #fff; color:#fff; font-style:normal; font-size:.66rem; font-weight:800; display:flex; align-items:center; justify-content:center; }
    .hs-app-label { font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.02em; color:#6b7280; text-align:center; line-height:1.2; }
    .hs-app-desc { font-size:.62rem; color:#9ca3af; text-align:center; line-height:1.25; }
    @media (max-width: 991px) { .hs-app-grid { grid-template-columns:repeat(3,1fr); } }
    .hs-sec { display:flex; align-items:center; gap:.4rem; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:1.1rem 0 .6rem; }
    .hs-sec:first-child { margin-top:0; }
    .hs-sec-badge { min-width:20px; height:20px; padding:0 6px; border-radius:10px; background:#ef4444; color:#fff; font-size:.66rem; font-weight:800; display:inline-flex; align-items:center; justify-content:center; }
    .hs-sec-link { margin-left:auto; font-size:.72rem; font-weight:700; text-transform:none; letter-spacing:0; color:#2563eb !important; text-decoration:none !important; }
    .hs-lista { background:#fff; border:1px solid #eef1f6; border-radius:14px; overflow:hidden; }
    .hs-item { display:flex; align-items:center; gap:.8rem; padding:.7rem .95rem; text-decoration:none !important; border-bottom:1px solid #f1f5f9; transition:background .12s; }
    .hs-item:last-child { border-bottom:0; }
    .hs-item:hover { background:#f8fafc; }
    .hs-item-icone { width:40px; height:40px; border-radius:12px; background:var(--app); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.95rem; flex-shrink:0; }
    .hs-item-icone img { width:100%; height:100%; border-radius:12px; object-fit:cover; display:block; }
    .hs-item--ok .hs-item-icone { background:#22c55e; }
    .hs-item-info { display:flex; flex-direction:column; min-width:0; }
    .hs-item-info b { font-size:.85rem; color:#1f2937; }
    .hs-item-info small { font-size:.7rem; color:#9ca3af; }
    .hs-item-qtd { margin-left:auto; min-width:26px; height:26px; padding:0 8px; border-radius:13px; background:var(--app); color:#fff; font-size:.75rem; font-weight:800; display:flex; align-items:center; justify-content:center; }
    .hs-item-seta { color:#cbd5e1; font-size:.7rem; flex-shrink:0; }
    .hs-item-vazio { display:flex; flex-direction:column; align-items:center; gap:.2rem; padding:1.4rem; color:#9ca3af; text-align:center; }
    .hs-item-vazio i { font-size:1.4rem; opacity:.4; }
    .hs-widgets { display:grid; grid-template-columns:repeat(5,1fr); gap:.7rem; }
    .hs-widget { display:flex; flex-direction:column; align-items:center; gap:.15rem; padding:.75rem .4rem; background:#fff; border:1px solid #eef1f6; border-radius:14px; text-decoration:none !important; transition:transform .15s, box-shadow .15s; }
    .hs-widget:hover { transform:translateY(-2px); box-shadow:0 6px 14px rgba(15,23,42,.08); }
    .hs-widget-icone { width:34px; height:34px; border-radius:10px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:.9rem; margin-bottom:.2rem; }
    .hs-widget b { font-size:1.2rem; font-weight:800; color:#1e3a8a; line-height:1.1; }
    .hs-widget small { font-size:.6rem; text-transform:uppercase; color:#9ca3af; font-weight:700; text-align:center; }
    .hs-chips { display:grid; grid-template-columns:repeat(3,1fr); gap:.7rem; }
    .hs-chip { display:flex; align-items:center; gap:.55rem; padding:.65rem .9rem; background:#fff; border:1px solid #e5e7eb; border-radius:12px; color:#1f2937 !important; font-weight:600; font-size:.82rem; text-decoration:none !important; transition:background .12s, border-color .12s; }
    .hs-chip i { width:30px; height:30px; border-radius:9px; background:var(--app); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.8rem; flex-shrink:0; }
    .hs-chip:hover { background:#f8fafc; border-color:#cbd5e1; }
    @media (max-width: 991px) { .hs-widgets { grid-template-columns:repeat(3,1fr); } .hs-chips { grid-template-columns:repeat(2,1fr); } }
    @media (max-width: 576px) { .hs-widgets { grid-template-columns:repeat(2,1fr); } .hs-chips { grid-template-columns:1fr; } }
</style>
