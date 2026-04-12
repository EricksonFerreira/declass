@php
    $notificacoes = collect($notificacoes ?? []);
@endphp

<div class="px-2 py-2" style="min-width: 320px; max-width: 380px;">
    <div class="d-flex justify-content-between align-items-center px-2 pb-2 border-bottom">
        <strong>Notificações</strong>
        <small class="text-muted">{{ $notificacoes->count() }} novas</small>
    </div>

    <div style="max-height: 320px; overflow:auto;">
        @forelse ($notificacoes as $notificacao)
            @php
                $dados = $notificacao->data ?? [];
                $icone = $dados['icone'] ?? 'fas fa-bell';
                $cor = $dados['cor'] ?? 'warning';
                $titulo = $dados['titulo'] ?? 'Notificação';
                $mensagem = $dados['mensagem'] ?? '';
                $url = $dados['url'] ?? null;
            @endphp
            <div class="dropdown-item py-2 px-2 border-bottom">
                <div class="d-flex align-items-start">
                    <span class="badge badge-{{ $cor }} p-2 mt-1"><i class="{{ $icone }}"></i></span>
                    <div class="flex-grow-1 ml-2">
                        @if($url)
                            <a href="{{ $url }}" class="font-weight-bold d-block" style="font-size: .9rem; color: #1f2937;">{{ $titulo }}</a>
                        @else
                            <div class="font-weight-bold" style="font-size: .9rem;">{{ $titulo }}</div>
                        @endif
                        <div class="text-muted" style="font-size: .78rem; line-height: 1.3;">{{ $mensagem }}</div>
                        <small class="text-muted">{{ $notificacao->created_at?->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="dropdown-item text-center text-muted py-4">
                Nenhuma notificação pendente.
            </div>
        @endforelse
    </div>
</div>
