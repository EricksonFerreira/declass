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

    /* Mobile/tablet: cards em grid 2-3 colunas e acoes compactas.
       Seletor forte (.ux-index-body[data-view]) vence os overrides
       por tela (.x-cards.ux-cards) sem precisar de !important. */
    @media (max-width: 991.98px) {
        .ux-index-body[data-view="cards"] .ux-card__foot {
            flex-direction: row;
            justify-content: flex-end;
            gap: 6px;
            padding: 8px 10px 10px;
        }

        .ux-index-body[data-view="cards"] .ux-card__foot form {
            width: auto;
            display: inline;
        }

        .ux-index-body[data-view="cards"] .ux-card__foot .btn {
            width: auto;
            min-height: 0;
            height: 32px;
            padding: 0 .55rem;
            font-size: .72rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }

    @media (max-width: 767.98px) {
        /* Celular: 2 cards por linha (padrao mobile comprovado;
           3 colunas deixaria ~110px por card, apertado demais) */
        .ux-index-body[data-view="cards"] .ux-cards {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            padding: 8px 4px 12px;
        }

        .ux-index-body[data-view="cards"] .ux-card__head { padding: 9px 10px 7px; gap: 7px; }
        .ux-index-body[data-view="cards"] .ux-card__avatar { width: 30px; height: 30px; border-radius: 8px; font-size: .62rem; }
        .ux-index-body[data-view="cards"] .ux-card__title { font-size: .74rem; }
        .ux-index-body[data-view="cards"] .ux-card__sub { font-size: .64rem; }
        .ux-index-body[data-view="cards"] .ux-card__badges { gap: 4px; }
        .ux-index-body[data-view="cards"] .ux-card__badge { font-size: .56rem; padding: 2px 5px; }
        .ux-index-body[data-view="cards"] .ux-card__meta { padding: 6px 10px 4px; gap: 4px 8px; }
        .ux-index-body[data-view="cards"] .ux-card__meta-label { font-size: .56rem; }
        .ux-index-body[data-view="cards"] .ux-card__meta-value { font-size: .68rem; white-space: normal; }
        .ux-index-body[data-view="cards"] .ux-card__foot { padding: 6px 10px 8px; gap: 5px; }
        .ux-index-body[data-view="cards"] .ux-card__foot .btn { height: 32px; font-size: .7rem; padding: 0 .5rem; }
    }

    @media (max-width: 575.98px) {
        /* Filtro não fixa no topo no mobile — ocupa muito da tela */
        .veiculo-filter-sticky {
            position: static;
        }

        /* Meta em 1 coluna nos cards estreitos do celular */
        .ux-index-body[data-view="cards"] .ux-card__meta {
            grid-template-columns: 1fr;
        }

        .ux-view-toggle .btn {
            font-size: .75rem;
            padding: .35rem .5rem;
        }
    }
</style>
