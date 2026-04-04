@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <h1 class="m-0">{{ $titulo }}</h1>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('site.apresentacao') }}">Voltar ao site</a>
            <a class="btn btn-primary btn-sm" href="{{ route('site.reserva.index') }}">Nova reserva</a>
        </div>
    </div>
@stop

@section('css')
    <style>
        .toast-wrap { position: fixed; top: 16px; right: 16px; display: grid; gap: 8px; z-index: 60; }
        .toast { min-width: 260px; max-width: 360px; border-radius: 12px; padding: 12px 14px; font-size: 13px; font-weight: 700; box-shadow: 0 18px 30px rgba(15, 23, 42, .18); border: 1px solid #e2e8f0; background: #fff; }
        .toast.ok { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
        .toast.err { border-color: #fecaca; background: #fef2f2; color: #991b1b; }
    </style>
@stop

@section('content')
    <div class="toast-wrap" id="toastWrap"></div>
    <div class="card">
        <div class="card-header">
            <strong>Minhas reservas</strong>
        </div>
        <div class="card-body">
            @forelse ($reservas as $reserva)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div class="font-weight-bold">Reserva #{{ $reserva->id }}</div>
                        <div>
                            <span class="badge badge-{{ $reserva->pagamento_efetuado === 'S' ? 'success' : 'warning' }}">
                                {{ $reserva->pagamento_efetuado === 'S' ? 'Pago' : 'Aguardando pagamento' }}
                            </span>
                            <span class="badge badge-light ml-1" style="border:1px solid #e2e8f0;">
                                {{ $reserva->statusReserva?->nome ?? 'Status' }}
                            </span>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6 mb-2">
                            <div class="text-muted text-sm">Retirada</div>
                            <div>{{ $reserva->estacaoRetirada?->nome ?? '-' }}</div>
                            <small>{{ \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="text-muted text-sm">Devolucao</div>
                            <div>{{ $reserva->estacaoDevolucao?->nome ?? '-' }}</div>
                            <small>{{ \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="text-muted text-sm">Veiculo</div>
                            <div>{{ $reserva->grupoFrota?->nome ?? '-' }}</div>
                            <small>{{ $reserva->modelos?->nome ?? '-' }}</small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="text-muted text-sm">Total</div>
                            <div class="font-weight-bold">R$ {{ number_format((float) ($reserva->valor_total_final ?? 0), 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info mb-0">Voce ainda nao possui reservas.</div>
            @endforelse

            <div class="mt-3">
                {{ $reservas->links() }}
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        (function () {
            const toastWrap = document.getElementById('toastWrap');
            const successMessage = @json(session('success'));
            const errorMessages = @json($errors->all());

            const showToast = (message, type) => {
                if (!toastWrap || !message) return;
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                toastWrap.appendChild(toast);
                setTimeout(() => {
                    toast.remove();
                }, 5000);
            };

            if (successMessage) {
                showToast(successMessage, 'ok');
            }
            if (Array.isArray(errorMessages)) {
                errorMessages.forEach((msg) => showToast(msg, 'err'));
            }
        })();
    </script>
@stop
