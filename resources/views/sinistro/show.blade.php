@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="col-12 px-0">
    <div class="card card-outline card-danger">

        {{-- Header --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center" style="gap:.75rem">
                <div class="sn-show-icon">
                    @php
                        $iconeTipo = match($sinistro->tipo_sinistro) {
                            'furto_roubo' => 'fa-user-secret',
                            'incendio'    => 'fa-fire',
                            'vandalismo'  => 'fa-hammer',
                            'alagamento'  => 'fa-water',
                            default       => 'fa-car-crash',
                        };
                        $badgeStatus = match($sinistro->status) {
                            'encerrado', 'indenizado' => 'success',
                            'aprovado'   => 'primary',
                            'cancelado'  => 'secondary',
                            'aguardando_documentos' => 'warning',
                            default      => 'danger',
                        };
                    @endphp
                    <i class="fas {{ $iconeTipo }}"></i>
                </div>
                <div>
                    <h3 class="card-title mb-0">Sinistro #{{ $sinistro->id }}</h3>
                    <div class="d-flex align-items-center mt-1" style="gap:.5rem">
                        <span class="badge badge-{{ $badgeStatus }}">{{ $sinistro->label_status }}</span>
                        <span class="badge badge-light text-dark">{{ $sinistro->label_tipo }}</span>
                        @if($sinistro->placa_momento)
                            <span class="badge badge-secondary">{{ $sinistro->placa_momento }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div>
                @can('sinistro.edit')
                <a href="{{ route('sinistro.edit', $sinistro->id) }}" class="btn btn-sm btn-warning mr-1">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
                @endcan
                <a href="{{ route('sinistro.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">

                {{-- Coluna Esquerda: Ocorrido --}}
                <div class="col-md-6">
                    <h5 class="sn-show-section-title">
                        <i class="fas fa-info-circle mr-1"></i>Identificação do Ocorrido
                    </h5>
                    <table class="table table-sm table-borderless sn-show-table">
                        <tr><th>Tipo</th><td>{{ $sinistro->label_tipo }}</td></tr>
                        <tr><th>Data / Hora</th><td>{{ $sinistro->data_hora_ocorrido?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                        <tr><th>Local</th><td>{{ $sinistro->local_ocorrido ?? '—' }}</td></tr>
                        <tr><th>Placa no Momento</th><td>{{ $sinistro->placa_momento ?? '—' }}</td></tr>
                        <tr><th>Veículo</th><td>
                            @if($sinistro->veiculo)
                                <a href="{{ route('veiculo.show', $sinistro->veiculo_id) }}">
                                    {{ $sinistro->veiculo->nome }}
                                    @if($sinistro->veiculo->marca || $sinistro->veiculo->modelo)
                                        — {{ $sinistro->veiculo->marca?->nome }} {{ $sinistro->veiculo->modelo?->nome }}
                                    @endif
                                </a>
                            @else —
                            @endif
                        </td></tr>
                        <tr><th>Reserva</th><td>
                            @if($sinistro->reserva_id)
                                <a href="{{ route('reserva.show', $sinistro->reserva_id) }}">#{{ $sinistro->reserva_id }}</a>
                            @else —
                            @endif
                        </td></tr>
                        <tr><th>Cliente</th><td>{{ $sinistro->cliente?->nome ?? '—' }}</td></tr>
                        <tr><th>Condutor</th><td>{{ $sinistro->condutor?->nome ?? '—' }}</td></tr>
                        <tr><th>Número BO</th><td>{{ $sinistro->numero_bo ?? '—' }}</td></tr>
                        <tr><th>Órgão BO</th><td>{{ $sinistro->orgao_bo ?? '—' }}</td></tr>
                        <tr><th>Terceiros</th><td>
                            @if($sinistro->terceiros_envolvidos)
                                <span class="badge badge-warning">Sim</span>
                                @if($sinistro->terceiro_nome)
                                    &nbsp;{{ $sinistro->terceiro_nome }}
                                    @if($sinistro->terceiro_telefone) — {{ $sinistro->terceiro_telefone }} @endif
                                    @if($sinistro->terceiro_placa) — Placa: {{ $sinistro->terceiro_placa }} @endif
                                @endif
                            @else
                                <span class="badge badge-secondary">Não</span>
                            @endif
                        </td></tr>
                        <tr><th>Vítimas</th><td>
                            <span class="badge badge-{{ $sinistro->vitimas ? 'danger' : 'secondary' }}">
                                {{ $sinistro->vitimas ? 'Sim' : 'Não' }}
                            </span>
                        </td></tr>
                    </table>

                    @if($sinistro->descricao)
                    <h5 class="sn-show-section-title mt-3">
                        <i class="fas fa-align-left mr-1"></i>Descrição
                    </h5>
                    <p class="small text-secondary" style="white-space:pre-wrap;">{{ $sinistro->descricao }}</p>
                    @endif

                    @if($sinistro->observacoes)
                    <h5 class="sn-show-section-title mt-3">
                        <i class="fas fa-comment-alt mr-1"></i>Observações
                    </h5>
                    <p class="small text-secondary" style="white-space:pre-wrap;">{{ $sinistro->observacoes }}</p>
                    @endif
                </div>

                {{-- Coluna Direita: Seguro e Valores --}}
                <div class="col-md-6">
                    <h5 class="sn-show-section-title">
                        <i class="fas fa-shield-alt mr-1"></i>Seguro
                    </h5>
                    <table class="table table-sm table-borderless sn-show-table">
                        <tr><th>Seguradora</th><td>{{ $sinistro->seguradora ?? '—' }}</td></tr>
                        <tr><th>Apólice</th><td>{{ $sinistro->numero_apolice ?? '—' }}</td></tr>
                        <tr><th>Nº Sinistro Seguradora</th><td>{{ $sinistro->numero_sinistro_seguradora ?? '—' }}</td></tr>
                        <tr><th>Franquia</th><td>
                            {{ $sinistro->franquia !== null ? 'R$ ' . number_format((float)$sinistro->franquia, 2, ',', '.') : '—' }}
                        </td></tr>
                    </table>

                    <h5 class="sn-show-section-title mt-3">
                        <i class="fas fa-dollar-sign mr-1"></i>Valores
                    </h5>
                    <table class="table table-sm table-borderless sn-show-table">
                        <tr>
                            <th>Prejuízo Estimado</th>
                            <td class="font-weight-bold text-danger">
                                {{ $sinistro->valor_prejuizo_estimado !== null ? 'R$ ' . number_format((float)$sinistro->valor_prejuizo_estimado, 2, ',', '.') : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Aprovado Seguradora</th>
                            <td class="font-weight-bold text-success">
                                {{ $sinistro->valor_aprovado_seguradora !== null ? 'R$ ' . number_format((float)$sinistro->valor_aprovado_seguradora, 2, ',', '.') : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Resp. do Cliente</th>
                            <td class="font-weight-bold text-warning">
                                {{ $sinistro->valor_responsabilidade_cliente !== null ? 'R$ ' . number_format((float)$sinistro->valor_responsabilidade_cliente, 2, ',', '.') : '—' }}
                            </td>
                        </tr>
                    </table>

                    <h5 class="sn-show-section-title mt-3">
                        <i class="fas fa-info mr-1"></i>Status e Responsável
                    </h5>
                    <table class="table table-sm table-borderless sn-show-table">
                        <tr><th>Status</th><td><span class="badge badge-{{ $badgeStatus }}">{{ $sinistro->label_status }}</span></td></tr>
                        <tr><th>Responsável</th><td>{{ $sinistro->responsavel?->nome ?? '—' }}</td></tr>
                        <tr><th>Criado em</th><td>{{ $sinistro->created_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                    </table>

                    {{-- Evidências --}}
                    @php
                        $anexos = array_filter([
                            'BO'     => $sinistro->anexo_bo_url,
                            'Foto 1' => $sinistro->anexo_foto1_url,
                            'Foto 2' => $sinistro->anexo_foto2_url,
                            'Foto 3' => $sinistro->anexo_foto3_url,
                            'Laudo'  => $sinistro->anexo_laudo_url,
                        ]);
                    @endphp
                    @if(count($anexos))
                    <h5 class="sn-show-section-title mt-3">
                        <i class="fas fa-paperclip mr-1"></i>Evidências
                    </h5>
                    <div class="d-flex flex-wrap" style="gap:.5rem">
                        @foreach($anexos as $label => $url)
                            @php
                                $ext  = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                                $icon = in_array(strtolower($ext), ['pdf']) ? 'fa-file-pdf text-danger' : 'fa-image text-primary';
                            @endphp
                            <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fas {{ $icon }} mr-1"></i>{{ $label }}
                            </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Histórico --}}
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="sn-show-section-title">
                        <i class="fas fa-history mr-1"></i>Histórico de Alterações
                    </h5>
                    @if($sinistro->historico->isEmpty())
                        <p class="text-muted small">Nenhuma alteração registrada.</p>
                    @else
                    <div class="timeline timeline-inverse" style="max-height:300px;overflow-y:auto;">
                        @foreach($sinistro->historico as $hist)
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

@section('css')
<style>
.sn-show-icon {
    width: 48px; height: 48px; border-radius: 10px;
    background: #fff3f3; color: #dc3545;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.sn-show-section-title {
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: #666;
    border-bottom: 1px solid #eee;
    padding-bottom: .35rem;
    margin-bottom: .75rem;
}
.sn-show-table th {
    width: 42%;
    font-size: .8rem;
    color: #777;
    font-weight: 600;
    padding: .3rem .5rem;
}
.sn-show-table td {
    font-size: .82rem;
    padding: .3rem .5rem;
}
</style>
@stop
