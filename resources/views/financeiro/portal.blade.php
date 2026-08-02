@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    {{-- <x-page-title :title="$titulo" /> --}}
@stop

@section('content')
    <style>
        .fp-page { display:grid; gap:20px; }
        .fp-hero {
            border-radius:24px; padding:24px; color:#fff;
            background:
                radial-gradient(circle at top right, rgba(56,189,248,.18), transparent 25%),
                radial-gradient(circle at bottom left, rgba(16,185,129,.15), transparent 22%),
                linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #2563eb 100%);
            box-shadow:0 24px 60px rgba(15,23,42,.18);
        }
        .fp-hero__eyebrow { font-size:.72rem; letter-spacing:.18em; text-transform:uppercase; font-weight:800; opacity:.72; }
        .fp-hero__title { margin:.35rem 0 .25rem; font-size:1.7rem; font-weight:900; letter-spacing:-.02em; }
        .fp-hero__text { margin:0; max-width:62rem; color:rgba(255,255,255,.86); line-height:1.6; }
        .fp-grid { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:20px; }
        @media (min-width: 1200px) { .fp-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
        @media (min-width: 992px) and (max-width: 1199.98px) { .fp-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 767px) { .fp-grid { grid-template-columns: 1fr; } }
        .fp-card {
            border:1px solid #dbe4f0; border-radius:18px; background:#fff; box-shadow:0 14px 36px rgba(15,23,42,.06);
            overflow:hidden; transition: transform .18s ease, box-shadow .18s ease;
        }
        .fp-card:hover { transform: translateY(-3px); box-shadow:0 18px 44px rgba(15,23,42,.1); }
        .fp-card__body { padding:20px; display:flex; flex-direction:column; gap:14px; min-height: 240px; }
        .fp-card__icon {
            width:60px; height:60px; border-radius:18px; display:flex; align-items:center; justify-content:center;
            font-size:1.5rem;
        }
        .fp-card__title { margin:0; font-size:1.05rem; font-weight:900; color:#0f172a; }
        .fp-card__text { margin:0; color:#64748b; line-height:1.6; }
        .fp-card__action {
            margin-top:auto; display:inline-flex; align-items:center; justify-content:center;
            border-radius:12px; font-weight:800; min-height:42px;
            width: 100%;
        }
        .bg-primary-light { background-color:#eff6ff; }
        .bg-success-light { background-color:#ecfdf5; }
        .bg-info-light { background-color:#e0f2fe; }
        .bg-danger-light { background-color:#fef2f2; }
        .bg-warning-light { background-color:#fffbeb; }
        .bg-slate-light { background-color:#f1f5f9; }
        .bg-violet-light { background-color:#f5f3ff; }
        .bg-emerald-light { background-color:#ecfdf5; }
        .bg-rose-light { background-color:#fff1f2; }
        .bg-sky-light { background-color:#f0f9ff; }
    </style>

    <div class="fp-page">
        <section class="fp-hero">
            <div class="fp-hero__eyebrow">Financeiro</div>
            <h1 class="fp-hero__title">Portal financeiro da operação</h1>
            <p class="fp-hero__text">
                Acesso rápido aos painéis de faturamento, fluxo de caixa, NFS-e, análises e ferramentas de cobrança.
                A navegação segue o mesmo padrão visual do restante do sistema.
            </p>
        </section>

        <div class="fp-grid">
            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="fp-card__icon bg-sky-light text-info"><i class="fas fa-wallet"></i></div>
                    <div>
                        <h4 class="fp-card__title">Portal Financeiro</h4>
                        <p class="fp-card__text">Visão rápida com atalhos, indicadores e acesso organizado às principais rotinas financeiras.</p>
                    </div>
                    <a href="{{ route('financeiro.index') }}" class="btn btn-info text-white fp-card__action">Abrir portal</a>
                </div>
            </div>

            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="fp-card__icon bg-success-light text-success"><i class="fas fa-receipt"></i></div>
                    <div>
                        <h4 class="fp-card__title">Faturamento por Reserva</h4>
                        <p class="fp-card__text">Veja o faturamento acumulado por locação, com leitura rápida do volume financeiro e do ticket médio.</p>
                    </div>
                    <a href="{{ route('financeiro.reservas') }}" class="btn btn-success fp-card__action">Acessar faturamento</a>
                </div>
            </div>

            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="fp-card__icon bg-info-light text-info"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <h4 class="fp-card__title">Fluxo de Caixa</h4>
                        <p class="fp-card__text">Controle entradas, despesas, saldos e projeções com visual diário e categorias principais.</p>
                    </div>
                    <a href="{{ route('financeiro.fluxo-caixa.dashboard') }}" class="btn btn-info text-white fp-card__action">Acessar fluxo de caixa</a>
                </div>
            </div>

            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="fp-card__icon bg-violet-light text-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div>
                        <h4 class="fp-card__title">Lançamentos</h4>
                        <p class="fp-card__text">Acompanhe entradas e saídas registradas no fluxo de caixa com acesso direto à listagem operacional.</p>
                    </div>
                    <a href="{{ route('financeiro.fluxo-caixa.index') }}" class="btn btn-primary fp-card__action">Ver lançamentos</a>
                </div>
            </div>

            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="fp-card__icon bg-danger-light text-danger"><i class="fas fa-file-invoice"></i></div>
                    <div>
                        <h4 class="fp-card__title">Notas Fiscais (NFS-e)</h4>
                        <p class="fp-card__text">Consulte emissões, baixas e importações de notas fiscais com foco operacional.</p>
                    </div>
                    <a href="{{ route('financeiro.nfse.index') }}" class="btn btn-danger fp-card__action">Acessar notas fiscais</a>
                </div>
            </div>

            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="fp-card__icon bg-primary-light text-primary"><i class="fas fa-user-friends"></i></div>
                    <div>
                        <h4 class="fp-card__title">Análise por Cliente</h4>
                        <p class="fp-card__text">Monitore pendências, reservas próximas e cobranças pessoais com justificativa.</p>
                    </div>
                    <a href="{{ route('financeiro.analise-clientes') }}" class="btn btn-primary fp-card__action">Abrir análise</a>
                </div>
            </div>

            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="fp-card__icon bg-emerald-light text-success"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div>
                        <h4 class="fp-card__title">Contas a Pagar</h4>
                        <p class="fp-card__text">Concentre despesas, vencimentos e pendências em uma tela de controle financeiro operacional.</p>
                    </div>
                    <a href="{{ route('financeiro.contas-pagar') }}" class="btn btn-success fp-card__action">Abrir contas a pagar</a>
                </div>
            </div>

            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="fp-card__icon bg-emerald-light text-success"><i class="fas fa-hand-holding-usd"></i></div>
                    <div>
                        <h4 class="fp-card__title">Contas a Receber</h4>
                        <p class="fp-card__text">Acompanhe cobranças, recebimentos e valores em aberto com leitura rápida da carteira.</p>
                    </div>
                    <a href="{{ route('financeiro.contas-receber') }}" class="btn btn-success fp-card__action">Abrir contas a receber</a>
                </div>
            </div>

            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="fp-card__icon bg-slate-light text-secondary"><i class="fas fa-car-side"></i></div>
                    <div>
                        <h4 class="fp-card__title">Análise por Veículo</h4>
                        <p class="fp-card__text">Compare receitas, despesas e resultado operacional por veículo da frota.</p>
                    </div>
                    <a href="{{ route('financeiro.analise-veiculos') }}" class="btn btn-secondary fp-card__action">Abrir análise</a>
                </div>
            </div>

            <div class="fp-card">
                <div class="fp-card__body">
                    <div class="fp-card__icon bg-warning-light text-warning"><i class="fas fa-percentage"></i></div>
                    <div>
                        <h4 class="fp-card__title">Cupons de Desconto</h4>
                        <p class="fp-card__text">Gerencie promoções, validade e regras de desconto com acesso centralizado.</p>
                    </div>
                    <a href="{{ route('financeiro.cupom_desconto.index') }}" class="btn btn-warning text-dark fp-card__action">Acessar cupons</a>
                </div>
            </div>
        </div>
    </div>
@stop
