@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <style>
        .perfil-wrap {
            background: linear-gradient(135deg, #f4f7fb 0%, #eef3f8 60%, #e8f0fa 100%);
            border-radius: 16px;
            padding: 20px;
        }

        .perfil-hero {
            border: 1px solid #e8edf4;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(13, 26, 47, 0.08);
            overflow: hidden;
        }

        .perfil-banner {
            height: 120px;
            background:
                radial-gradient(circle at 15% 25%, rgba(0, 138, 186, 0.35), transparent 36%),
                radial-gradient(circle at 85% 70%, rgba(255, 159, 67, 0.30), transparent 40%),
                linear-gradient(120deg, #0f4c81, #1678c9 45%, #1cb5e0);
        }

        .perfil-main {
            margin-top: -56px;
            padding: 0 24px 24px 24px;
        }

        .perfil-avatar {
            width: 116px;
            height: 116px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 8px 20px rgba(13, 26, 47, 0.18);
            background: #fff;
        }

        .perfil-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1c2733;
            margin-bottom: 2px;
        }

        .perfil-sub {
            color: #5f6f82;
            font-size: .92rem;
        }

        .info-card {
            border: 1px solid #e9edf3;
            border-radius: 12px;
            padding: 12px 14px;
            background: #fff;
            height: 100%;
        }

        .info-label {
            display: block;
            font-size: .76rem;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #7b8ca3;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .info-value {
            color: #273547;
            font-weight: 600;
            font-size: .95rem;
            word-break: break-word;
        }

        .btn-password-profile {
            background: linear-gradient(120deg, #0f4c81, #1678c9 45%, #1cb5e0);
            border: 0;
            color: #fff;
            font-weight: 700;
            border-radius: 10px;
            padding: 11px 16px;
            transition: all .2s ease;
        }

        .btn-password-profile:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(15, 76, 129, 0.25);
        }
    </style>

    <div class="perfil-wrap">
        <div class="perfil-hero mb-4">
            <div class="perfil-banner"></div>
            <div class="perfil-main">
                <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <img class="perfil-avatar" src="{{ $user->foto_perfil_url }}" alt="Foto de perfil">
                        <div class="ml-3">
                            <div class="perfil-name">{{ $user->nome }}</div>
                            <div class="perfil-sub">{{ $user->email }}</div>
                            <div class="perfil-sub">
                                {{ $user->isAdministrador() ? 'Superusuario' : (optional($user->tipoUsuario)->name ?? 'Sem regra definida') }}
                            </div>
                        </div>
                    </div>
                    <span class="badge badge-pill badge-info px-3 py-2">ID #{{ $user->id }}</span>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <div class="info-card">
                    <span class="info-label">Empresa</span>
                    <span class="info-value">{{ optional($user->empresa)->nome ?? 'Não informada' }}</span>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="info-card">
                    <span class="info-label">Telefone</span>
                    <span class="info-value">{{ $user->telefone ?? 'Não informado' }}</span>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="info-card">
                    <span class="info-label">Celular</span>
                    <span class="info-value">{{ $user->celular ?? 'Não informado' }}</span>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="info-card">
                    <span class="info-label">Cidade</span>
                    <span class="info-value">{{ $user->cidade ?? 'Não informada' }}</span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div class="mb-2 mb-md-0">
                    <div class="font-weight-bold text-dark">Visualização do perfil</div>
                    <small class="text-muted">Nesta tela você pode apenas visualizar as informações do usuário.</small>
                </div>
                <a href="{{ route('profile.password.edit') }}" class="btn btn-password-profile">
                    <i class="fas fa-key mr-1"></i> Alterar senha
                </a>
            </div>
        </div>
    </div>
@stop
