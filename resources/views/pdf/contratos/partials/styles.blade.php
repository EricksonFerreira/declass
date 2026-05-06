<style>
    {!! $css ?? $data['css'] ?? '' !!}
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    @page {
        size: A4 portrait;
        margin: 9mm 12mm;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 8.5px;
        color: #1a2035;
        background: #fff;
    }
    .page {
        width: 100%;
        max-width: 190mm;
        min-height: 250mm;
        margin: 0 auto;
        padding: 0;
    }
    .section-title,
    .doc-header,
    .two-col,
    .dano-box,
    .finance-box,
    .signature-grid,
    .no-break {
        page-break-inside: avoid;
    }
    .page-break {
        page-break-before: always;
    }
    .doc-header {
        display: table;
        width: 100%;
        border-bottom: 2px solid #1e3a8a;
        padding-bottom: 6px;
        margin-bottom: 8px;
    }
    .doc-header__logo {
        display: table-cell;
        width: 35%;
        vertical-align: middle;
    }
    .doc-header__logo img {
        max-height: 64px;
        max-width: 200px;
    }
    .doc-header__logo .sistema-logo {
        max-height: 48px;
    }
    .doc-header__info {
        display: table-cell;
        width: 65%;
        vertical-align: middle;
        text-align: right;
    }
    .doc-header__title {
        font-size: 13px;
        font-weight: 900;
        color: #1e3a8a;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .doc-header__subtitle {
        font-size: 7px;
        color: #475569;
        margin-top: 2px;
        letter-spacing: 0.04em;
    }
    .doc-header__company {
        font-size: 6.5px;
        color: #64748b;
        margin-top: 2px;
        line-height: 1.3;
    }
    .doc-header__num {
        display: inline-block;
        margin-top: 3px;
        background: #1e3a8a;
        color: #fff;
        font-weight: 800;
        font-size: 9px;
        padding: 2px 10px;
        border-radius: 20px;
        letter-spacing: 0.06em;
    }
    .accent-stripe {
        height: 3px;
        background: #1e3a8a;
        border-radius: 2px;
        margin-bottom: 8px;
    }
    .section-title {
        font-size: 7.5px;
        font-weight: 900;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #1e3a8a;
        border-left: 3px solid #1e3a8a;
        padding-left: 5px;
        margin-bottom: 4px;
        margin-top: 7px;
    }
    .info-table {
        width: 100%;
        border-collapse: collapse;
    }
    .info-table td {
        padding: 2px 4px;
        vertical-align: top;
    }
    .field-label {
        font-size: 6.5px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .field-value {
        font-size: 8px;
        color: #0f172a;
        font-weight: 500;
        border-bottom: 1px solid #cbd5e1;
        padding-bottom: 1px;
        min-width: 50px;
        min-height: 12px;
    }
    .two-col {
        display: table;
        width: 100%;
        margin-top: 3px;
    }
    .col-left {
        display: table-cell;
        width: 55%;
        padding-right: 10px;
        vertical-align: top;
    }
    .col-right {
        display: table-cell;
        width: 45%;
        vertical-align: top;
    }
    .viatura-images {
        margin-bottom: 6px;
    }
    .viatura-images img {
        width: 60px;
        height: 38px;
        object-fit: cover;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        margin: 2px;
    }
    .dano-box {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 4px;
        padding: 4px 6px;
        margin-top: 4px;
    }
    .dano-box table {
        width: 100%;
        border-collapse: collapse;
    }
    .dano-box td {
        padding: 2px 3px;
        font-size: 8.5px;
    }
    .dano-box td:first-child {
        font-weight: 700;
        color: #92400e;
        white-space: nowrap;
        width: 30%;
    }
    .opcionais-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }
    .opcionais-table th {
        background: #1e3a8a;
        color: #fff;
        font-size: 7px;
        font-weight: 700;
        padding: 3px 4px;
        text-align: left;
    }
    .opcionais-table td {
        padding: 2px 4px;
        font-size: 7.5px;
        border-bottom: 1px solid #e2e8f0;
    }
    .opcionais-table tr:nth-child(even) td {
        background: #f8fafc;
    }
    .detail-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
    }
    .detail-table th {
        background: #e2e8f0;
        color: #0f172a;
        font-size: 7px;
        font-weight: 800;
        padding: 3px 4px;
        text-align: left;
        border: 1px solid #cbd5e1;
    }
    .detail-table td {
        padding: 2px 4px;
        font-size: 7.5px;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .detail-table tr:nth-child(even) td {
        background: #f8fafc;
    }
    .detail-table .money {
        text-align: right;
        white-space: nowrap;
        font-weight: 700;
    }
    .detail-table .muted,
    .muted {
        color: #64748b;
        font-size: 7.5px;
    }
    .thumb {
        width: 32px;
        height: 24px;
        object-fit: cover;
        border: 1px solid #cbd5e1;
        border-radius: 2px;
        margin-right: 1px;
    }
    .finance-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        padding: 5px 8px;
        margin-top: 5px;
    }
    .finance-row {
        display: table;
        width: 100%;
        padding: 1px 0;
    }
    .finance-row .lbl {
        display: table-cell;
        font-size: 8px;
        font-weight: 700;
        color: #475569;
    }
    .finance-row .val {
        display: table-cell;
        text-align: right;
        font-size: 8.5px;
        font-weight: 700;
        color: #1e3a8a;
    }
    .finance-row.total .lbl,
    .finance-row.total .val {
        font-size: 10px;
        color: #1e40af;
    }
    .divider {
        border: none;
        border-top: 1px dashed #cbd5e1;
        margin: 6px 0;
    }
    .declaration {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        padding: 5px 8px;
        font-size: 7.5px;
        color: #475569;
        text-align: justify;
        margin-top: 7px;
        line-height: 1.4;
    }
    .signature-grid {
        display: table;
        width: 100%;
        margin-top: 12px;
    }
    .signature-box {
        display: table-cell;
        width: 50%;
        padding: 0 10px;
        text-align: center;
        vertical-align: bottom;
    }
    .signature-line {
        border-bottom: 1.5px solid #1e3a8a;
        height: 55px;
        margin-bottom: 4px;
    }
    .signature-line img {
        max-height: 65px;
        max-width: 220px;
    }
    .signature-name {
        font-size: 8.5px;
        font-weight: 700;
        color: #0f172a;
    }
    .signature-role,
    .signature-doc {
        font-size: 7.5px;
        color: #64748b;
        line-height: 1.35;
    }
    .doc-footer {
        margin-top: 10px;
        text-align: center;
        font-size: 6.5px;
        color: #94a3b8;
        border-top: 1px solid #e2e8f0;
        padding-top: 4px;
        line-height: 1.3;
    }
    .qr-code {
        width: 55px;
        height: 55px;
        margin-top: 4px;
    }
    .badge-blue {
        display: inline-block;
        background: #dbeafe;
        color: #1e3a8a;
        font-size: 7.5px;
        font-weight: 700;
        border-radius: 4px;
        padding: 1px 6px;
    }

    .clause-page {
        page-break-inside: avoid;
    }

    .clause-header {
        margin-bottom: 10px;
    }
    .clause-header-bar {
        height: 2px;
        background: linear-gradient(to right, #1e3a8a, #60a5fa, #1e3a8a);
        border-radius: 2px;
        margin-bottom: 10px;
    }

    .clause-header-content {
        display: table;
        width: 100%;
        margin: 6px 0;
    }

    .clause-header-icon {
        display: table-cell;
        width: 32px;
        font-size: 22px;
        color: #1e3a8a;
        vertical-align: middle;
        text-align: center;
    }

    .clause-header-icon ~ * {
        display: table-cell;
        vertical-align: middle;
    }

    .clause-header-title {
        font-size: 14px;
        font-weight: 900;
        color: #1e3a8a;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .clause-header-subtitle {
        font-size: 7.5px;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-top: 2px;
    }

    .clause-grid {
        display: table;
        width: 100%;
        border-collapse: separate;
        border-spacing: 4px;
    }

    .clause-grid-col {
        display: table-cell;
        width: 50%;
        vertical-align: top;
    }

    .clause-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 4px 6px 4px 6px;
        margin-bottom: 4px;
        page-break-inside: avoid;
    }

    .clause-item:nth-child(odd) {
        background: #f8fafc;
    }

    .clause-num {
        display: inline-block;
        background: #1e3a8a;
        color: #ffffff;
        font-size: 7px;
        font-weight: 800;
        padding: 1px 5px;
        border-radius: 3px;
        margin-right: 4px;
        letter-spacing: 0.05em;
        vertical-align: middle;
    }

    .clause-body {
        display: inline;
        font-size: 7.5px;
        line-height: 1.4;
        color: #334155;
        text-align: justify;
    }

    .clause-body strong {
        color: #1e3a8a;
    }

    .clause-footer-bar {
        margin-top: 10px;
        padding-top: 5px;
        border-top: 1px solid #cbd5e1;
    }

    .clause-footer-bar-inner {
        text-align: center;
        font-size: 7px;
        color: #64748b;
        letter-spacing: 0.04em;
    }

    .clause-footer-bull {
        margin: 0 6px;
        color: #94a3b8;
    }

    .doc-footer-inner {
        display: table;
        width: 100%;
    }

    .doc-footer-col {
        display: table-cell;
        width: 33%;
        vertical-align: middle;
        font-size: 7px;
        color: #64748b;
    }

    .doc-footer-center {
        text-align: center;
    }

    .doc-footer-right {
        text-align: right;
    }

    .doc-footer-brand {
        font-weight: 700;
        color: #475569;
    }

    .doc-footer-sep {
        margin: 0 4px;
        color: #cbd5e1;
    }

    .doc-footer-hash {
        font-family: 'Courier New', monospace;
        font-size: 6.5px;
        color: #94a3b8;
    }

    .doc-footer-qr {
        text-align: center;
        margin-top: 4px;
    }

    .status-pago {
        color: #16a34a !important;
        font-weight: 800;
    }
    .status-parcial {
        color: #d97706 !important;
        font-weight: 800;
    }
    .status-aberto {
        color: #dc2626 !important;
        font-weight: 800;
    }
</style>
