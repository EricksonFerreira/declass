{{-- Estilos dos cards de atalho (home-launcher). Uso: @include('partials.home-cards-styles') dentro de @section('css') --}}
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    .home-launcher {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .home-section-title {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin: 16px 0 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .home-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    .home-section:first-child .home-section-title {
        margin-top: 2px;
    }

    .home-card {
        display: flex;
        align-items: center;
        gap: 10px;
        height: 100%;
        min-height: 58px;
        padding: 8px 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        text-decoration: none !important;
        color: #0f172a;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }

    .home-card:hover {
        transform: translateY(-2px);
        border-color: var(--card-cor, var(--empresa-cor-primaria, #3b82f6));
        box-shadow: 0 8px 20px rgba(15, 23, 42, .10);
        color: #0f172a;
    }

    .home-card-icone {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
        color: #fff;
        background: var(--card-cor, var(--empresa-cor-primaria, #3b82f6));
        box-shadow: 0 4px 10px rgba(15, 23, 42, .14);
    }

    .home-card-texto {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .home-card-nome {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .home-card-desc {
        font-size: 11px;
        line-height: 1.3;
        color: #64748b;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .home-card-seta {
        margin-left: auto;
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 700;
        color: var(--card-cor, var(--empresa-cor-primaria, #3b82f6));
        opacity: 0;
        transition: opacity .15s ease;
    }

    .home-card:hover .home-card-seta {
        opacity: 1;
    }

    @media (max-width: 575.98px) {
        .home-card { padding: 8px 10px; gap: 8px; min-height: 48px; }
        .home-card-icone { width: 30px; height: 30px; font-size: 12px; }
        .home-card-desc { display: none; }
        .home-card-seta { display: none; }
    }
</style>
