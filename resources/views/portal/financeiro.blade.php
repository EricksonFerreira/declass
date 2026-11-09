@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Mensalidades dos alunos vinculados à sua conta" />
@stop

@section('content')
<x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

@foreach($alunos as $aluno)
    @php $cobrancas = $mensalidades->get($aluno->id, collect()); @endphp
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold mb-0">{{ $aluno->pessoa->nome ?? $aluno->nome }}</h3>
            @php
                $emAberto = $cobrancas->where('status', 'pendente')->sum('valor');
                $vencidas = $cobrancas->filter(fn ($m) => $m->situacao === 'vencida')->count();
            @endphp
            <div>
                @if($vencidas > 0)
                    <span class="badge badge-danger">{{ $vencidas }} vencida(s)</span>
                @endif
                <span class="badge badge-warning">R$ {{ number_format($emAberto, 2, ',', '.') }} em aberto</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Competência</th>
                        <th>Turma</th>
                        <th>Plano</th>
                        <th class="text-right">Valor</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cobrancas as $c)
                        @php $situacao = $c->situacao; @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $c->competencia)->format('m/Y') }}</td>
                            <td>{{ $c->matricula->turma->nome ?? '—' }}</td>
                            <td>{{ $c->planoMensalidade->nome ?? '—' }}</td>
                            <td class="text-right">R$ {{ number_format($c->valor, 2, ',', '.') }}</td>
                            <td>{{ $c->vencimento->format('d/m/Y') }}</td>
                            <td>
                                @php $cor = ['pendente' => 'warning', 'vencida' => 'danger', 'paga' => 'success', 'cancelada' => 'secondary'][$situacao] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $cor }}">{{ ucfirst($situacao) }}</span>
                            </td>
                            <td class="text-right">
                                @if($c->boleto_path && $c->status === 'pendente')
                                    <a href="{{ route('portal.mensalidade.boleto', $c->id) }}" class="btn btn-xs btn-primary" title="Baixar boleto para pagamento"><i class="fa fa-barcode"></i> Boleto</a>
                                @endif
                                @if($c->status === 'paga')
                                    <a href="{{ route('portal.mensalidade.recibo', $c->id) }}" class="btn btn-xs btn-outline-primary" title="Recibo de pagamento"><i class="fa fa-file-invoice"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Nenhuma mensalidade registrada.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
@endforeach
@stop
