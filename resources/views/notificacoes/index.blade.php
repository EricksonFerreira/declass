@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0">Notificações</h1>
            <p class="text-muted mb-0">Avisos de multas, vencimentos e atualizações do sistema.</p>
        </div>
        @if(($naoLidas ?? 0) > 0)
            <form method="POST" action="{{ route('notificacoes.marcarTodasComoLidas') }}">
                @csrf
                @method('PATCH')
                <button class="btn btn-sm btn-outline-primary">Marcar todas como lidas</button>
            </form>
        @endif
    </div>
@stop

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Status</th>
                        <th>Assunto</th>
                        <th>Mensagem</th>
                        <th>Data</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notificacoes as $notificacao)
                        @php
                            $dados = $notificacao->data ?? [];
                            $icone = $dados['icone'] ?? 'fas fa-bell';
                            $cor = $dados['cor'] ?? 'warning';
                            $titulo = $dados['titulo'] ?? 'Notificação';
                            $mensagem = $dados['mensagem'] ?? '';
                            $url = $dados['url'] ?? null;
                        @endphp
                        <tr class="{{ $notificacao->read_at ? '' : 'table-warning' }}">
                            <td>
                                <span class="badge badge-{{ $cor }}"><i class="{{ $icone }}"></i></span>
                                @if(!$notificacao->read_at)
                                    <span class="badge badge-danger ml-1">Nova</span>
                                @else
                                    <span class="badge badge-secondary ml-1">Lida</span>
                                @endif
                            </td>
                            <td>{{ $titulo }}</td>
                            <td>{{ $mensagem }}</td>
                            <td>{{ $notificacao->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-right">
                                @if($url)
                                    <a href="{{ $url }}" class="btn btn-sm btn-outline-primary">Abrir</a>
                                @endif
                                @if(!$notificacao->read_at)
                                    <form method="POST" action="{{ route('notificacoes.marcarLida', $notificacao->id) }}" class="d-inline ml-1">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-success">Marcar lida</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Nenhuma notificação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $notificacoes->links() }}
</div>
@endsection
