@extends('adminlte::page')

@section('title', 'Plano Contratado')

@section('content_header')
    <div>
        <h1 class="m-0 text-dark" style="font-size:1.3rem;font-weight:700;">Contratacao Concluida</h1>
    </div>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="text-center mb-4">
            <div style="font-size:4rem;margin-bottom:.75rem;">&#9989;</div>
            <h2 class="font-weight-bold" style="color:#0a1628;">Tudo pronto!</h2>
            <p class="text-muted" style="font-size:1rem;max-width:480px;margin:0 auto;">
                Seu plano foi contratado e o pagamento confirmado. Aproveite todos os recursos liberados.
            </p>
        </div>

        @if ($plano)
        <div class="card" style="border:2px solid #22c55e;border-radius:16px;overflow:hidden;">
            <div class="card-header text-white text-center py-3" style="background:#16a34a;">
                <i class="fas fa-check-circle mr-1"></i>
                <strong style="font-size:1.1rem;">{{ $plano->nome }}</strong>
                @if ($assinatura?->periodicidade === 'anual')
                    <span class="badge bg-white text-success ml-2">Anual</span>
                @else
                    <span class="badge bg-white text-primary ml-2">Mensal</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0" style="font-size:.88rem;">
                            <tr>
                                <td class="text-muted pl-0" style="width:45%;">Valor</td>
                                <td class="font-weight-bold">R$ {{ number_format($pagamento->valor ?? $assinatura?->valor_cobrado, 2, ',', '.') }}</td>
                            </tr>
                            @if ($assinatura)
                            <tr>
                                <td class="text-muted pl-0">Periodicidade</td>
                                <td class="font-weight-bold">{{ $assinatura->periodicidade === 'anual' ? 'Anual' : 'Mensal' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Inicio</td>
                                <td class="font-weight-bold">{{ $assinatura->data_inicio?->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Vencimento</td>
                                <td class="font-weight-bold">{{ $assinatura->data_vencimento?->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Metodo</td>
                                <td class="font-weight-bold">{{ ucfirst($pagamento->metodo ?? $assinatura->metodo_cobranca ?? '—') }}</td>
                            </tr>
                            @endif
                            @if ($pagamento?->pago_em)
                            <tr>
                                <td class="text-muted pl-0">Pago em</td>
                                <td class="font-weight-bold">{{ $pagamento->pago_em->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background:#f0fdf4;">
                            <h6 class="font-weight-bold mb-2" style="font-size:.85rem;color:#065f46;">
                                <i class="fas fa-check-circle mr-1"></i> O que esta incluso
                            </h6>
                            <ul class="list-unstyled mb-0" style="font-size:.82rem;">
                                @foreach ($plano->itens as $item)
                                    <li class="mb-1">
                                        <i class="fas fa-check text-success mr-2" style="font-size:.7rem;"></i>
                                        {{ $item->descricao }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <hr class="my-3">
                <div class="d-flex" style="gap:1.5rem;flex-wrap:wrap;">
                    @if ($plano->limite_veiculos)
                        <div>
                            <span class="text-muted" style="font-size:.78rem;">Limite de veiculos</span>
                            <div class="font-weight-bold" style="font-size:1.1rem;">{{ $plano->limite_veiculos }}</div>
                        </div>
                    @endif
                    @if ($plano->limite_usuarios)
                        <div>
                            <span class="text-muted" style="font-size:.78rem;">Limite de usuarios</span>
                            <div class="font-weight-bold" style="font-size:1.1rem;">{{ $plano->limite_usuarios }}</div>
                        </div>
                    @endif
                    @php
                        $storageMb = $plano->limite_armazenamento_mb ?? 0;
                        $storageTxt = $storageMb >= 1024 ? number_format($storageMb / 1024, 1, ',', '.') . ' GB' : $storageMb . ' MB';
                    @endphp
                    <div>
                        <span class="text-muted" style="font-size:.78rem;">Armazenamento</span>
                        <div class="font-weight-bold" style="font-size:1.1rem;"><i class="fas fa-database mr-1"></i>{{ $storageTxt }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="text-center mt-4 d-flex justify-content-center" style="gap:.75rem;flex-wrap:wrap;">
            <a href="{{ route('assinatura.meu-plano') }}" class="btn btn-primary btn-lg px-4">
                <i class="fas fa-id-card mr-1"></i> Ir para Meu Plano
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg px-4">
                <i class="fas fa-tachometer-alt mr-1"></i> Ir para o Painel
            </a>
        </div>

    </div>
</div>
@stop
