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

                            <ul class="list-unstyled mb-3 flex-grow-1" style="font-size:.85rem;">
                                <li class="mb-1 text-muted">
                                    <i class="fas fa-car mr-1"></i>
                                    Veiculos: <strong>{{ $plano->limite_veiculos_texto }}</strong>
                                </li>
                                @foreach ([
                                    ['site_vendas',   'globe',       'Site de vendas'],
                                    ['modulo_multas', 'file-invoice','Modulo de multas'],
                                    ['modulo_nfse',   'receipt',     'NFS-e'],
                                    ['multi_estacao', 'map-marker',  'Multi-estacao'],
                                    ['api_integracao','plug',        'API'],
                                ] as [$campo, $icon, $label])
                                    <li class="mb-1 {{ $plano->$campo ? '' : 'text-muted' }}">
                                        <i class="fas fa-{{ $icon }} mr-1 {{ $plano->$campo ? 'text-success' : '' }}"></i>
                                        {{ $label }}
                                        @if (!$plano->$campo)
                                            <i class="fas fa-times text-secondary ml-1" style="font-size:.7rem;"></i>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            <div class="text-muted mb-3" style="font-size:.8rem;">
                                <i class="fas fa-users mr-1"></i>
                                {{ $plano->assinaturas_count }} assinatura(s)
                            </div>

                            <div class="mt-auto d-flex" style="gap:6px;">
                                <a href="{{ route('plano.edit', $plano) }}" class="btn btn-outline-secondary btn-sm flex-fill">
                                    <i class="fas fa-edit mr-1"></i> Editar
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
