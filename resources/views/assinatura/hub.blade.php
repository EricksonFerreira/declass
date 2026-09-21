@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :subtitle="$subtitulo" />
@stop

@section('content')
    {{-- Atalhos --}}
    <div class="row">
        <div class="col-md-3 col-6">
            <a href="{{ route('assinatura.index') }}" class="small-box bg-primary shadow-sm">
                <div class="inner">
                    <h3>{{ number_format($stats->ativas, 0, ',', '.') }}</h3>
                    <p>Assinaturas ativas</p>
                </div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
                <span class="small-box-footer">Gerenciar <i class="fas fa-arrow-circle-right"></i></span>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('assinatura.monitoramento-pagamentos') }}" class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3>{{ number_format($stats->aguardando, 0, ',', '.') }}</h3>
                    <p>Aguardando pagamento</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <span class="small-box-footer">Monitoramento <i class="fas fa-arrow-circle-right"></i></span>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('assinatura.financas') }}" class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3><i class="fas fa-chart-line"></i></h3>
                    <p>Finanças das assinaturas</p>
                </div>
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                <span class="small-box-footer">Abrir <i class="fas fa-arrow-circle-right"></i></span>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('assinatura.ajuda') }}" class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3><i class="fas fa-question"></i></h3>
                    <p>Central de ajuda (FAQ)</p>
                </div>
                <div class="icon"><i class="fas fa-question-circle"></i></div>
                <span class="small-box-footer">Abrir <i class="fas fa-arrow-circle-right"></i></span>
            </a>
        </div>
    </div>

    @if (($stats->inadimplentes ?? 0) > 0 || ($stats->trial ?? 0) > 0)
        <div class="alert alert-light border shadow-sm small mb-3">
            <i class="fas fa-info-circle mr-1 text-muted"></i>
            <strong>{{ $stats->inadimplentes }}</strong> inadimplente(s) ·
            <strong>{{ $stats->trial }}</strong> em trial ·
            <strong>{{ $stats->total }}</strong> assinatura(s) no total
        </div>
    @endif

    {{-- Catálogo de planos --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title text-bold text-sm"><i class="fas fa-th-large mr-1"></i> Catálogo de planos</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="pl-3">Plano</th>
                            <th class="text-right">Mensal</th>
                            <th class="text-right">Anual</th>
                            <th class="text-center">Usuários</th>
                            <th class="text-center">Assinaturas</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($planos as $plano)
                            <tr>
                                <td class="pl-3">
                                    <span class="font-weight-bold">{{ $plano->nome }}</span>
                                    @if ($plano->descricao)
                                        <div class="text-muted" style="font-size:.75rem">{{ \Illuminate\Support\Str::limit($plano->descricao, 80) }}</div>
                                    @endif
                                </td>
                                <td class="text-right">R$ {{ number_format((float) $plano->valor_mensal, 2, ',', '.') }}</td>
                                <td class="text-right">R$ {{ number_format((float) $plano->valor_anual, 2, ',', '.') }}</td>
                                <td class="text-center">{{ $plano->limite_usuarios ?? '∞' }}</td>
                                <td class="text-center"><span class="badge badge-info">{{ $plano->assinaturas_count }}</span></td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $plano->ativo ? 'success' : 'secondary' }}">{{ $plano->ativo ? 'Ativo' : 'Inativo' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhum plano cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-2">
            <small class="text-muted">O catálogo é gerenciado diretamente no banco de dados — esta tela é apenas consulta.</small>
        </div>
    </div>
@stop
