@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h1 class="m-0 text-dark">{{ $titulo }}</h1>
        <a href="{{ route('ordem-servico.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nova OS
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card card-outline card-secondary">
        <div class="card-header py-2">
            <div class="btn-group btn-group-sm flex-wrap" role="group">
                <a href="{{ route('ordem-servico.index') }}"
                   class="btn btn-{{ ! $status ? 'secondary' : 'outline-secondary' }}">Todas</a>
                <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_ABERTA]) }}"
                   class="btn btn-{{ $status === \App\Models\OrdemServico::STATUS_ABERTA ? 'warning' : 'outline-warning' }}">Abertas</a>
                <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_EM_ANDAMENTO]) }}"
                   class="btn btn-{{ $status === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO ? 'info' : 'outline-info' }}">Em andamento</a>
                <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_CONCLUIDA]) }}"
                   class="btn btn-{{ $status === \App\Models\OrdemServico::STATUS_CONCLUIDA ? 'success' : 'outline-success' }}">Concluídas</a>
                <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_CANCELADA]) }}"
                   class="btn btn-{{ $status === \App\Models\OrdemServico::STATUS_CANCELADA ? 'dark' : 'outline-dark' }}">Canceladas</a>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0 text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th>Número</th>
                        <th>Veículo</th>
                        <th>Reserva</th>
                        <th>Status</th>
                        <th>Aberta por</th>
                        <th>Atualização</th>
                        <th class="text-right">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordens as $os)
                        <tr>
                            <td><strong>{{ $os->numero ?? '#' . $os->id }}</strong></td>
                            <td>{{ $os->veiculo->nome ?? ('#' . $os->veiculo_id) }}</td>
                            <td>
                                @if($os->reserva_id)
                                    <a href="{{ route('reserva.show', $os->reserva_id) }}">#{{ $os->reserva_id }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($os->status === \App\Models\OrdemServico::STATUS_ABERTA)
                                    <span class="badge badge-warning">Aberta</span>
                                @elseif($os->status === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO)
                                    <span class="badge badge-info">Em andamento</span>
                                @elseif($os->status === \App\Models\OrdemServico::STATUS_CONCLUIDA)
                                    <span class="badge badge-success">Concluída</span>
                                @else
                                    <span class="badge badge-secondary">Cancelada</span>
                                @endif
                            </td>
                            <td>{{ $os->usuarioAbertura->name ?? '—' }}</td>
                            <td>
                                @if($os->data_conclusao)
                                    {{ $os->data_conclusao->format('d/m/Y H:i') }}
                                @else
                                    {{ $os->updated_at?->format('d/m/Y H:i') ?? '—' }}
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('ordem-servico.show', $os) }}" class="btn btn-xs btn-primary">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhuma ordem de serviço encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ordens->hasPages())
            <div class="card-footer">{{ $ordens->links() }}</div>
        @endif
    </div>
@stop
