<style>
    .veiculo-filter-sticky {
        position: sticky;
        top: 10px;
        z-index: 1030;
    }

    .ux-view-toggle {
        display: flex;
        gap: 5px;
    }

    .veiculo-view-toggle .btn.active,
    .ux-view-toggle .btn.active {
        background: #0f172a;
        color: #fff;
        border-color: #0f172a;
    }

    .ux-index-body[data-view="table"] .ux-cards { display: none; }
    .ux-index-body[data-view="cards"] .ux-table { display: none; }

    .ux-index-body[data-view="table"] .ux-table {
        max-height: calc(100vh - 360px);
        overflow: auto;
        border-radius: 6px;
    }

    .ux-index-body[data-view="table"] .ux-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8fafc;
    }

    .ux-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 16px;
        padding: 10px 8px 16px;
    }

    .ux-card {
        position: relative;
        background: #fff;
        border: 1px solid #e2e9f4;
        border-radius: 18px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 10px rgba(11,18,32,.06);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .ux-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(11,18,32,.13);
    }

    .ux-card__accent {
        height: 3px;
        background: linear-gradient(90deg, #1e293b, #334155);
    }

    .ux-card__head {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 14px 16px 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .ux-card__avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #fff;
        font-size: .82rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        letter-spacing: .04em;
    }

    .ux-card__head-info { flex: 1; min-width: 0; }
    .ux-card__id {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 2px;
    }

    .ux-card__title {
        font-size: .92rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ux-card__sub {
        font-size: .74rem;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 1px;
    }

    .ux-card__badges {
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-items: flex-end;
        margin-left: 6px;
    }

    .ux-card__badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .70rem;
        font-weight: 700;
        padding: 5px 8px;
        border-radius: 999px;
        line-height: 1;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .ux-card__badge--blue {
        background: rgba(59,130,246,.12);
        color: #1d4ed8;
        border-color: rgba(59,130,246,.2);
    }
    .ux-card__badge--green {
        background: rgba(34,197,94,.12);
        color: #15803d;
        border-color: rgba(34,197,94,.2);
    }
    .ux-card__badge--amber {
        background: rgba(245,158,11,.15);
        color: #b45309;
        border-color: rgba(245,158,11,.25);
    }
    .ux-card__badge--red {
        background: rgba(239,68,68,.12);
        color: #b91c1c;
        border-color: rgba(239,68,68,.2);
    }
    .ux-card__badge--gray {
        background: rgba(148,163,184,.15);
        color: #334155;
        border-color: rgba(148,163,184,.2);
    }

    .ux-card__meta {
        padding: 12px 16px 10px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 12px;
        flex: 1;
    }

    .ux-card__meta-row {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .ux-card__meta-row--full { grid-column: 1 / -1; }

    .ux-card__meta-label {
        font-size: .70rem;
        color: #94a3b8;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .ux-card__meta-value {
        font-size: .86rem;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ux-card__foot {
        margin-top: auto;
        padding: 12px 16px 14px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    @media (max-width: 991.98px) {
        .ux-cards {
            grid-template-columns: 1fr;
            padding: 6px 4px 12px;
        }

        .ux-card__foot {
            flex-direction: column;
            gap: 8px;
        }

        .ux-card__foot form {
            width: 100%;
            display: block;
        }

        .ux-card__foot .btn {
            width: 100%;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
        }
    }

    @media (max-width: 575.98px) {
        /* Filtro não fixa no topo no mobile — ocupa muito da tela */
        .veiculo-filter-sticky {
            position: static;
        }

        .ux-card__meta {
            grid-template-columns: 1fr;
        }

        .ux-view-toggle .btn {
            font-size: .75rem;
            padding: .35rem .5rem;
        }
    }
</style>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\partials\ux-index-styles.blade.php ENDPATH**/ ?>