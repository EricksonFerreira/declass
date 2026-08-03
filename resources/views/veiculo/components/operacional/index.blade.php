{{--
    Componente principal da operação do veículo.
    Caminho sugerido:
    resources/views/veiculo/components/operacional/index.blade.php
--}}
@php
    $showPercentualDisponibilidade = (int) ($dispResumo['percentual'] ?? 0);
    $showStatusDisponibilidade = (string) ($dispResumo['status_mes'] ?? 'off');

    $showDisponivel = $showStatusDisponibilidade === 'ok' && $showPercentualDisponibilidade >= 100;
    $showParcial = $showStatusDisponibilidade === 'partial'
        || ($showPercentualDisponibilidade > 0 && $showPercentualDisponibilidade < 100);

    $showLabelDisponibilidade = match (true) {
        $showDisponivel => 'Disponível',
        $showParcial => 'Disponibilidade parcial',
        default => 'Indisponível',
    };

    $showIconDisponibilidade = match (true) {
        $showDisponivel => 'fa-check-circle',
        $showParcial => 'fa-adjust',
        default => 'fa-clock',
    };

    $showClassDisponibilidade = match (true) {
        $showDisponivel => 'is-success',
        $showParcial => 'is-warning',
        default => 'is-danger',
    };

    $disponibilidadeAtiva = (int) ($dispReservas->count() ?? 0);
    $avariasPendentes = (int) $veiculo->veiculoDanos->whereNull('correcao_data')->count();
    $movimentacoesTotal = (int) $veiculo->movimentacoes->count();
    $osPendentes = (int) $veiculo->ordensServico->whereIn('status', [\App\Models\OrdemServico::STATUS_ABERTA, \App\Models\OrdemServico::STATUS_EM_ANDAMENTO])->count();
    $sinistrosAbertos = (int) $veiculo->sinistros->whereIn('status', ['aberto', 'em_analise', 'aguardando_documentos'])->count();
    $multasTotal = (int) $veiculo->multas->count();
    $multasVencidas = (int) $veiculo->multas->filter(function ($multa) {
        return in_array((string) ($multa->status_multa ?? ''), ['pendente', 'em_analise'], true)
            && ! empty($multa->data_vencimento)
            && \Carbon\Carbon::parse($multa->data_vencimento)->isPast();
    })->count();

    $manutencoesVencidas = (int) $veiculo->pendenciasManutencaoPreventiva->filter(function ($pendencia) use ($veiculo) {
        $vencidaPorData = ! empty($pendencia->data_prevista) && \Carbon\Carbon::parse($pendencia->data_prevista)->isPast();
        $vencidaPorKm = $pendencia->km_previsto !== null && (int) ($veiculo->kms ?? 0) >= (int) $pendencia->km_previsto;

        return in_array((string) ($pendencia->status ?? ''), ['pendente', 'atrasado'], true) && ($vencidaPorData || $vencidaPorKm);
    })->count();

    $documentosCadastrados = (int) $veiculo->documentacoesVeiculo->count();
    $documentosVencidos = (int) $veiculo->documentacoesVeiculo->flatMap->parcelas->filter(function ($parcela) {
        return in_array((string) ($parcela->status ?? ''), ['pendente', 'atrasado'], true)
            && ! empty($parcela->data_vencimento)
            && \Carbon\Carbon::parse($parcela->data_vencimento)->isPast();
    })->count();

    $countTooltips = [
        'disp' => 'Quantidade de reservas ou bloqueios ativos no período atual.',
        'avarias' => 'Quantidade de avarias ainda pendentes de correção.',
        'mov' => 'Quantidade total de movimentações registradas para este veículo.',
        'os' => 'Quantidade de ordens de serviço abertas ou em andamento.',
        'sinistros' => 'Quantidade de sinistros ainda não encerrados.',
        'multas' => 'Quantidade total de multas cadastradas para este veículo.',
        'manutencao' => 'Quantidade de manutenções preventivas vencidas.',
        'documentacao' => 'Quantidade de parcelas vencidas dentro dos documentos cadastrados para este veículo.',
    ];

    $badgeColor = function (int $count, int $alertCount = 0, string $alertType = 'red') {
        if ($alertCount > 0) {
            return $alertType === 'orange' ? 'vs-step__count--warn' : 'vs-step__count--alert';
        }

        return 'vs-step__count--muted';
    };
@endphp

<section class="vs-shell vs-shell--compact mb-4" aria-labelledby="vehicle-operation-title">
    <header class="vs-compact-head">
        <div>
            <span class="vs-shell__eyebrow">Operação e histórico</span>
            <h3 class="vs-shell__title" id="vehicle-operation-title">Acompanhamento do veículo</h3>
        </div>

        <span class="vs-badge-soft {{ $showClassDisponibilidade }}">
            <i class="fas {{ $showIconDisponibilidade }}"></i>
            {{ $showLabelDisponibilidade }} · {{ $showPercentualDisponibilidade }}%
        </span>
    </header>

    {{--
        FIPE foi removida desta navegação porque já existe na área de dados
        gerenciais acima. Isso evita duas entradas para a mesma informação.
    --}}
    <nav class="vs-steps vs-steps--compact" id="vsSteps" aria-label="Seções operacionais do veículo">
        <button class="vs-step is-active" type="button" data-step="disp">
            <span class="vs-step__icon"><i class="fas fa-calendar-check"></i></span>
            <span class="vs-step__content"><strong>Disponibilidade</strong></span>
            <span class="vs-step__count {{ $disponibilidadeAtiva > 0 ? 'vs-step__count--alert' : 'vs-step__count--success' }}" title="{{ $countTooltips['disp'] }}" data-toggle="tooltip">
                {{ $disponibilidadeAtiva }}
            </span>
        </button>

        <button class="vs-step" type="button" data-step="avarias">
            <span class="vs-step__icon"><i class="fas fa-car-crash"></i></span>
            <span class="vs-step__content"><strong>Avarias</strong></span>
            <span class="vs-step__count {{ $avariasPendentes > 0 ? 'vs-step__count--alert' : 'vs-step__count--success' }}" title="{{ $countTooltips['avarias'] }}" data-toggle="tooltip">
                {{ $avariasPendentes }}
            </span>
        </button>

        <button class="vs-step" type="button" data-step="mov">
            <span class="vs-step__icon"><i class="fas fa-route"></i></span>
            <span class="vs-step__content"><strong>Movimentações</strong></span>
            <span class="vs-step__count vs-step__count--muted" title="{{ $countTooltips['mov'] }}" data-toggle="tooltip">{{ $movimentacoesTotal }}</span>
        </button>

        <button class="vs-step" type="button" data-step="os">
            <span class="vs-step__icon"><i class="fas fa-tools"></i></span>
            <span class="vs-step__content"><strong>Ordens de serviço</strong></span>
            <span class="vs-step__count {{ $osPendentes > 0 ? 'vs-step__count--warn' : 'vs-step__count--success' }}" title="{{ $countTooltips['os'] }}" data-toggle="tooltip">
                {{ $osPendentes }}
            </span>
        </button>

        <button class="vs-step" type="button" data-step="sinistros">
            <span class="vs-step__icon"><i class="fas fa-shield-alt"></i></span>
            <span class="vs-step__content"><strong>Sinistros</strong></span>
            <span class="vs-step__count {{ $sinistrosAbertos > 0 ? 'vs-step__count--alert' : 'vs-step__count--success' }}" title="{{ $countTooltips['sinistros'] }}" data-toggle="tooltip">
                {{ $sinistrosAbertos }}
            </span>
        </button>

        <button class="vs-step" type="button" data-step="multas">
            <span class="vs-step__icon"><i class="fas fa-exclamation-triangle"></i></span>
            <span class="vs-step__content"><strong>Multas</strong></span>
            <span class="vs-step__count {{ $multasVencidas > 0 ? 'vs-step__count--alert' : 'vs-step__count--muted' }}" title="{{ $countTooltips['multas'] }}" data-toggle="tooltip">
                {{ $multasTotal }}
            </span>
        </button>

        <button class="vs-step" type="button" data-step="manutencao">
            <span class="vs-step__icon"><i class="fas fa-tools"></i></span>
            <span class="vs-step__content"><strong>Manut. Preventiva</strong></span>
            <span class="vs-step__count {{ $manutencoesVencidas > 0 ? 'vs-step__count--alert' : 'vs-step__count--success' }}" title="{{ $countTooltips['manutencao'] }}" data-toggle="tooltip">
                {{ $manutencoesVencidas }}
            </span>
        </button>

        <button class="vs-step" type="button" data-step="documentacao">
            <span class="vs-step__icon"><i class="fas fa-file-alt"></i></span>
            <span class="vs-step__content"><strong>Documentação</strong></span>
            <span class="vs-step__count {{ $documentosVencidos > 0 ? 'vs-step__count--alert' : 'vs-step__count--muted' }}" title="{{ $countTooltips['documentacao'] }}" data-toggle="tooltip">
                {{ $documentosVencidos }}
            </span>
        </button>
    </nav>

    @include('veiculo.components.operacional.etapas.disponibilidade')
    @include('veiculo.components.operacional.etapas.avarias')
    @include('veiculo.components.operacional.etapas.ordens-servico')
    @include('veiculo.components.operacional.etapas.movimentacoes')
    @include('veiculo.components.operacional.etapas.sinistros')
    @include('veiculo.components.operacional.etapas.multas')
    @include('veiculo.components.operacional.etapas.manutencao-preventiva')
    @include('veiculo.components.operacional.etapas.documentacao')
</section>
