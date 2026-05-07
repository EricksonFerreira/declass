@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :subtitle="$subtitulo">
        <a href="{{ route('planos.hub') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-th-large mr-1"></i> Central
        </a>
        <a href="{{ route('plano.create') }}" class="btn btn-xs btn-primary">
            <i class="fa fa-plus" style="font-size:10px"></i> Novo Plano
        </a>
    </x-page-title>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <x-page-header-card :showHeader="false">
        <div class="row">
            @forelse ($planos as $plano)
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card h-100 shadow-sm {{ $plano->destaque ? 'border-primary' : '' }}" style="border-width:{{ $plano->destaque ? '2px' : '1px' }};">
                        @if ($plano->destaque)
                            <div class="card-header bg-primary text-white py-1 text-center" style="font-size:.75rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                                <i class="fas fa-star mr-1"></i> Mais Escolhido
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="mb-0 font-weight-bold">{{ $plano->nome }}</h5>
                                <span class="badge {{ $plano->ativo ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $plano->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted" style="font-size:.8rem;">Mensal</div>
                                <div class="font-weight-bold" style="font-size:1.5rem;color:#0a1628;">
                                    R$ {{ number_format($plano->valor_mensal, 2, ',', '.') }}
                                    <small class="text-muted font-weight-normal" style="font-size:.8rem;">/mes</small>
                                </div>
                                <div class="text-muted" style="font-size:.8rem;">
                                    Anual: R$ {{ number_format($plano->valor_anual, 2, ',', '.') }}/mes
                                    @if ($plano->desconto_anual_percentual > 0)
                                        <span class="badge badge-success ml-1">{{ $plano->desconto_anual_percentual }}% off</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Resumo dos modulos do plano --}}
                            <div class="mb-3 flex-grow-1">
                                {{-- Veiculos --}}
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-car text-primary mr-2" style="width:14px;text-align:center;"></i>
                                    <span><strong>{{ $plano->limite_veiculos_texto }}</strong> veiculos</span>
                                </div>
                                {{-- Usuarios --}}
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-users text-primary mr-2" style="width:14px;text-align:center;"></i>
                                    <span><strong>{{ $plano->limite_usuarios_texto }}</strong> usuario(s)</span>
                                </div>
                                {{-- Multi-estacao --}}
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-map-marker-alt {{ $plano->multi_estacao ? 'text-success' : 'text-muted' }} mr-2" style="width:14px;text-align:center;"></i>
                                    <span class="{{ $plano->multi_estacao ? '' : 'text-muted' }}">
                                        {{ $plano->multi_estacao ? 'Multi-estacoes' : '1 estacao' }}
                                    </span>
                                </div>
                                {{-- Site de vendas --}}
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-globe {{ $plano->site_vendas ? 'text-success' : 'text-muted' }} mr-2" style="width:14px;text-align:center;"></i>
                                    <span class="{{ $plano->site_vendas ? '' : 'text-muted' }}">
                                        Site de vendas
                                        @if (!$plano->site_vendas)<i class="fas fa-times text-secondary ml-1" style="font-size:.65rem;"></i>@endif
                                    </span>
                                </div>
                                {{-- Modulo multas --}}
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-file-invoice {{ $plano->modulo_multas ? 'text-success' : 'text-muted' }} mr-2" style="width:14px;text-align:center;"></i>
                                    <span class="{{ $plano->modulo_multas ? '' : 'text-muted' }}">
                                        Modulo de multas
                                        @if (!$plano->modulo_multas)<i class="fas fa-times text-secondary ml-1" style="font-size:.65rem;"></i>@endif
                                    </span>
                                </div>
                                {{-- NFS-e --}}
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-receipt {{ $plano->modulo_nfse ? 'text-success' : 'text-muted' }} mr-2" style="width:14px;text-align:center;"></i>
                                    <span class="{{ $plano->modulo_nfse ? '' : 'text-muted' }}">
                                        NFS-e
                                        @if (!$plano->modulo_nfse)<i class="fas fa-times text-secondary ml-1" style="font-size:.65rem;"></i>@endif
                                    </span>
                                </div>
                                {{-- API --}}
                                <div class="d-flex align-items-center mb-1" style="font-size:.82rem;">
                                    <i class="fas fa-plug {{ $plano->api_integracao ? 'text-success' : 'text-muted' }} mr-2" style="width:14px;text-align:center;"></i>
                                    <span class="{{ $plano->api_integracao ? '' : 'text-muted' }}">
                                        API de integracao
                                        @if (!$plano->api_integracao)<i class="fas fa-times text-secondary ml-1" style="font-size:.65rem;"></i>@endif
                                    </span>
                                </div>
                                {{-- Sempre inclusos --}}
                                @foreach ([
                                    ['calendar-check','Reservas completas'],
                                    ['file-signature','Contratos digitais'],
                                    ['dollar-sign',   'Controle financeiro'],
                                    ['users',         'Multiplos usuarios'],
                                    ['chart-bar',     'Relatorios e exportacao'],
                                ] as [$icon, $label])
                                    <div class="d-flex align-items-center mb-1 text-muted" style="font-size:.78rem;">
                                        <i class="fas fa-{{ $icon }} text-success mr-2" style="width:14px;text-align:center;font-size:.7rem;"></i>
                                        <span>{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="text-muted mb-3" style="font-size:.8rem;">
                                @php
                                    $storageMb = $plano->limite_armazenamento_mb ?? 0;
                                    $storageTxt = $storageMb >= 1024 ? number_format($storageMb / 1024, 1, ',', '.') . ' GB' : $storageMb . ' MB';
                                @endphp
                                <i class="fas fa-database mr-1"></i>
                                {{ $storageTxt }} de armazenamento
                                <br>
                                <i class="fas fa-users mr-1"></i>
                                {{ $plano->assinaturas_count }} assinatura(s)
                                <br>
                                <i class="fas fa-user-shield mr-1"></i>
                                {{ $plano->limite_permissoes_por_regra ? $plano->limite_permissoes_por_regra . ' permissões por perfil' : 'Permissões por perfil sem limite' }}
                            </div>

                            <div class="mt-auto d-flex flex-wrap" style="gap:6px;">
                                <a href="{{ route('plano.edit', $plano) }}" class="btn btn-outline-secondary btn-sm flex-fill">
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </a>
                                <a href="{{ route('plano.acessos', $plano) }}" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="fas fa-user-shield mr-1"></i> Acessos
                                </a>
                                @if ($plano->assinaturas_count === 0)
                                    <form action="{{ route('plano.destroy', $plano) }}" method="POST"
                                          onsubmit="return confirm('Remover o plano {{ addslashes($plano->nome) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Nenhum plano cadastrado ainda.
                        <a href="{{ route('plano.create') }}">Criar primeiro plano</a>.
                    </div>
                </div>
            @endforelse
        </div>
    </x-page-header-card>
@stop
