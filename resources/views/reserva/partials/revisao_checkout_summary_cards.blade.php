<div class="row">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-soft mb-0">
            <div class="sum-card">
                <div class="sum-ico"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <div class="sum-label">Reserva</div>
                    <div class="sum-value">#{{ $reserva->id }}</div>
                    <div class="sum-meta">{{ $codigoReserva }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-soft mb-0">
            <div class="sum-card">
                <div class="sum-ico"><i class="fas fa-car"></i></div>
                <div>
                    <div class="sum-label">Veículo</div>
                    <div class="sum-value">{{ $veiculo?->placa_licenca ?? $veiculo?->placa ?? '—' }}</div>
                    <div class="sum-meta">{{ $veiculo?->nome ?? $veiculo?->modelo?->nome ?? 'Veículo' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-soft mb-0">
            <div class="sum-card">
                <div class="sum-ico"><i class="fas fa-user"></i></div>
                <div>
                    <div class="sum-label">Cliente</div>
                    <div class="sum-value">{{ $cliente?->nome ?? $cliente?->name ?? 'Cliente' }}</div>
                    <div class="sum-meta">{{ $cliente?->telefone ?? $cliente?->phone ?? $cliente?->cpf ?? 'Sem dados' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card card-soft mb-0">
            <div class="sum-card">
                <div class="sum-ico"><i class="fas fa-dollar-sign"></i></div>
                <div>
                    <div class="sum-label">Resultado da revisão</div>
                    <div class="sum-value">R$ {{ number_format($resumoFinanceiro['resultado_revisao'] ?? $resumoFinanceiro['total_cobrancas'] ?? 0, 2, ',', '.') }}</div>
                    <span class="mini-chip {{ ($resumoFinanceiro['resultado_revisao'] ?? $resumoFinanceiro['total_cobrancas'] ?? 0) > 0 ? 'mini-chip--warn' : 'mini-chip--ok' }}">
                        {{ ($resumoFinanceiro['resultado_revisao'] ?? $resumoFinanceiro['total_cobrancas'] ?? 0) > 0 ? 'Soma das cobranças' : 'Sem cobrança' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
