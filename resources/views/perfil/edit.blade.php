@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')

<div class="pf-page">

    {{-- ── Hero card ── --}}
    <div class="card pf-card mb-3">

        {{-- Banner com cor primária da empresa --}}
        <div class="pf-banner">
            <span class="pf-id-badge">ID #{{ $user->id }}</span>
        </div>

        {{-- Avatar sobreposto ao banner --}}
        <div class="pf-avatar-wrap">
            <img class="pf-avatar" src="{{ $user->foto_perfil_url }}" alt="Foto de perfil">
        </div>

        {{-- Nome, email e badge — claramente na área branca --}}
        <div class="pf-info-section">
            <div class="pf-name">{{ $user->nome }}</div>
            <div class="pf-sub">{{ $user->email }}</div>
            <div class="pf-role-badge">
                {{ $user->isAdministrador() ? 'Superusuário' : (optional($user->tipoUsuario)->name ?? 'Sem perfil') }}
            </div>
        </div>

    </div>

    {{-- ── Info cards ── --}}
    <div class="row mb-3">
        @php
            $infoItems = [
                ['label' => 'Empresa',  'icon' => 'fas fa-building',      'value' => optional($user->empresa)->nome ?? 'Não informada'],
                ['label' => 'Telefone', 'icon' => 'fas fa-phone',         'value' => $user->telefone ?? 'Não informado'],
                ['label' => 'Celular',  'icon' => 'fas fa-mobile-alt',    'value' => $user->celular  ?? 'Não informado'],
                ['label' => 'Cidade',   'icon' => 'fas fa-map-marker-alt','value' => $user->cidade   ?? 'Não informada'],
            ];
        @endphp
        @foreach($infoItems as $info)
            <div class="col-md-3 col-6 mb-2">
                <div class="pf-info-card">
                    <span class="pf-info-label">
                        <i class="{{ $info['icon'] }} mr-1"></i>{{ $info['label'] }}
                    </span>
                    <span class="pf-info-value">{{ $info['value'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Action card ── --}}
    <div class="card pf-action-card">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div class="mb-2 mb-md-0">
                <div class="font-weight-bold text-dark">Visualização do perfil</div>
                <small class="text-muted">Nesta tela você pode apenas visualizar as <span class="pf-highlight">informações do usuário</span>.</small>
            </div>
            <a href="{{ route('profile.password.edit') }}" class="btn pf-btn-password">
                <i class="fas fa-key mr-1"></i> Alterar senha
            </a>
        </div>
    </div>

</div>

@stop

@section('css')
<style>
/* ── Page ── */
.pf-page { padding-bottom: 2rem; }

/* ── Hero card ── */
.pf-card {
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,.07);
}

/* Banner usando cor primária da empresa */
.pf-banner {
    height: 55px;
    position: relative;
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    padding: 12px 16px;
}

/* ID badge no canto superior direito do banner */
.pf-id-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 14px;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 800;
    background: color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 60%, #000 40%);
    color: var(--empresa-cor-primaria-contraste, #fff);
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
}

/* Avatar: círculo sobreposto ao banner */
.pf-avatar-wrap {
    padding: 0 24px;
    margin-top: -52px;
    margin-bottom: 4px;
}

.pf-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 6px 20px rgba(0,0,0,.18);
    background: var(--empresa-cor-secundaria, #E2E8F0);
    display: block;
}

/* Nome, email, badge — área branca limpa */
.pf-info-section {
    padding: 8px 24px 20px;
}

.pf-name {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 2px;
    line-height: 1.3;
}

.pf-sub {
    color: #64748b;
    font-size: .88rem;
    line-height: 1.4;
}

.pf-role-badge {
    display: inline-flex;
    align-items: center;
    margin-top: 8px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    background: color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 12%, white 88%);
    color: var(--empresa-cor-primaria, #1F2937);
    border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 25%, transparent 75%);
}

/* ── Info cards ── */
.pf-info-card {
    border: 1px solid #e9edf3;
    border-radius: 12px;
    padding: 14px;
    background: #fff;
    height: 100%;
    transition: box-shadow .15s, border-color .15s;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}

.pf-info-card:hover {
    border-color: color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 40%, transparent 60%);
    box-shadow: 0 4px 16px rgba(0,0,0,.09);
}

.pf-info-label {
    display: block;
    font-size: .72rem;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--empresa-cor-primaria, #64748b);
    font-weight: 700;
    margin-bottom: 6px;
    opacity: .85;
}

.pf-info-value {
    display: block;
    color: #273547;
    font-weight: 600;
    font-size: .95rem;
    word-break: break-word;
}

/* ── Action card ── */
.pf-action-card {
    border: 1px solid #e9edf3;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}

.pf-highlight {
    color: var(--empresa-cor-primaria, #1e40af);
    font-weight: 600;
}

.pf-btn-password {
    background: var(--empresa-cor-primaria, #1F2937);
    color: var(--empresa-cor-primaria-contraste, #fff) !important;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    padding: 10px 18px;
    transition: all .2s ease;
    white-space: nowrap;
}

.pf-btn-password:hover {
    opacity: .9;
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(0,0,0,.2);
}

/* ── Responsive ── */
@media (max-width: 576px) {
    .pf-main { padding: 0 16px 20px; }
    .pf-name { font-size: 1.1rem; }
    .pf-avatar { width: 88px; height: 88px; }
}
</style>
@stop
