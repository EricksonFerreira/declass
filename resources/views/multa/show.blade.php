@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="col-12 px-0">
    <div class="card card-outline card-danger">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>
                Multa #{{ $multa->id }}
            </h3>
            <div>
                @can('multa.edit')
                <a href="{{ route('multa.edit', $multa->id) }}" class="btn btn-sm btn-warning mr-1">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
                @endcan
                <a href="{{ route('multa.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                {{-- Coluna Esquerda --}}
                <div class="col-md-6">
                    <h5 class="border-bottom pb-1 mb-3">Identificacao da Infracao</h5>
                    <table class="table table-sm table-borderless">
                        <tr><th class="w-40">Reserva</th><td>
                            @if($multa->reserva_id)
                                <a href="{{ route('reserva.show', $multa->reserva_id) }}">#{{ $multa->reserva_id }}</a>
                            @else —
                            @endif
                        </td></tr>
                        <tr><th>Veiculo / Placa</th><td>{{ $multa->veiculo?->nome ?? '—' }} / {{ $multa->placa_momento ?? '—' }}</td></tr>
                        <tr><th>Cliente</th><td>{{ $multa->cliente?->nome ?? '—' }}</td></tr>
                        <tr><th>Condutor</th><td>{{ $multa->condutor?->nome ?? '—' }}</td></tr>
                        <tr><th>Numero Auto</th><td>{{ $multa->numero_auto ?? '—' }}</td></tr>
                        <tr><th>Orgao Autuador</th><td>{{ $multa->orgao_autuador ?? '—' }}</td></tr>
                        <tr><th>Data Infracao</th><td>{{ $multa->data_hora_infracao?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                        <tr><th>Local</th><td>{{ $multa->local_infracao ?? '—' }}</td></tr>
                        <tr><th>Descricao</th><td>{{ $multa->descricao ?? '—' }}</td></tr>
                        <tr><th>Vencimento</th><td>{{ $multa->data_vencimento?->format('d/m/Y') ?? '—' }}</td></tr>
                        <tr><th>Pontos</th><td>{{ $multa->pontos ?? '—' }}</td></tr>
                    </table>
                </div>

                {{-- Coluna Direita --}}
                <div class="col-md-6">
                    <h5 class="border-bottom pb-1 mb-3">Valores</h5>
                    <table class="table table-sm table-borderless">
                        <tr><th class="w-40">Valor Original</th><td>R$ {{ number_format((float)$multa->valor_original, 2, ',', '.') }}</td></tr>
                        <tr><th>Valor Desconto</th><td>{{ $multa->valor_desconto !== null ? 'R$ ' . number_format((float)$multa->valor_desconto, 2, ',', '.') : '—' }}</td></tr>
                        <tr><th>Valor Final</th><td class="font-weight-bold">R$ {{ number_format((float)$multa->valor_final, 2, ',', '.') }}</td></tr>
                    </table>

                    <h5 class="border-bottom pb-1 mb-3 mt-3">Status</h5>
                    @php
                        $badgeMulta = match($multa->status_multa) {
                            'paga' => 'success', 'cancelada' => 'secondary',
                            'deferida' => 'info', 'indeferida' => 'warning', default => 'danger',
                        };
                        $badgeDesc = match($multa->status_desconto) {
                            'descontado' => 'success', 'nao_descontar' => 'secondary',
                            'estornado' => 'warning', 'pendente' => 'info', default => 'light',
                        };
                    @endphp
                    <table class="table table-sm table-borderless">
                        <tr><th class="w-40">Status Multa</th><td><span class="badge badge-{{ $badgeMulta }}">{{ $multa->label_status_multa }}</span></td></tr>
                        <tr><th>Status Desconto</th><td><span class="badge badge-{{ $badgeDesc }}">{{ $multa->label_status_desconto }}</span></td></tr>
                        <tr><th>Responsavel</th><td>{{ $multa->responsavelLancamento?->nome ?? '—' }}</td></tr>
                        <tr><th>Observacao</th><td>{{ $multa->observacao ?? '—' }}</td></tr>
                    </table>

                    @if($multa->anexo_comprovante_path || $multa->anexo_pdf_path)
                    <h5 class="border-bottom pb-1 mb-3 mt-3">Documentos</h5>
                    <div class="d-flex gap-2">
                        @if($multa->anexo_comprovante_path)
                        <a href="{{ $multa->anexo_comprovante_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-image mr-1"></i> Comprovante
                        </a>
                        @endif
                        @if($multa->anexo_pdf_path)
                        <a href="{{ $multa->anexo_pdf_url }}" target="_blank" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf mr-1"></i> PDF Auto
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Historico --}}
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="border-bottom pb-1 mb-3">
                        <i class="fas fa-history mr-1"></i> Historico de Alteracoes
                    </h5>
                    @if($multa->historico->isEmpty())
                        <p class="text-muted small">Nenhuma alteracao registrada.</p>
                    @else
                    <div class="timeline timeline-inverse" style="max-height:300px;overflow-y:auto;">
                        @foreach($multa->historico as $hist)
                        <div class="time-label">
                            <span class="bg-secondary text-white" style="font-size:11px;padding:2px 6px;border-radius:3px;">
                                {{ $hist->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <div>
                            <i class="fas fa-circle bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-user"></i> {{ $hist->usuario?->nome ?? 'Sistema' }}</span>
                                <p class="timeline-body small">
                                    {{ $hist->descricao }}
                                    @if($hist->campo_alterado)
                                        <br><strong>{{ $hist->campo_alterado }}:</strong>
                                        <span class="text-danger">{{ $hist->valor_anterior ?? 'vazio' }}</span>
                                        &rarr;
                                        <span class="text-success">{{ $hist->valor_novo }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop
