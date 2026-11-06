@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$titulo" subtitle="Próximas refeições das escolas dos seus alunos" />
@stop

@section('content')
@forelse($cardapios as $data => $refeicoes)
    <div class="card">
        <div class="card-header py-2">
            <strong>{{ \Illuminate\Support\Carbon::parse($data)->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y') }}</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <tbody>
                    @foreach($refeicoes as $cardapio)
                        <tr>
                            <td style="width:160px;">
                                <span class="badge badge-info">{{ $cardapio->tipoLabel() }}</span>
                            </td>
                            <td>
                                {{ $cardapio->descricao }}
                                @if($cardapio->restricoes_alimentares)
                                    <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> {{ $cardapio->restricoes_alimentares }}</small>
                                @endif
                                @if($cardapio->observacoes)
                                    <br><small class="text-muted">{{ $cardapio->observacoes }}</small>
                                @endif
                            </td>
                            <td class="text-right text-muted" style="width:180px;">{{ $cardapio->escola->nome ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-utensils fa-2x mb-2 d-block"></i>
            Nenhum cardápio programado para os próximos dias.
        </div>
    </div>
@endforelse

<div class="mt-2">
    <a href="{{ route('portal.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
</div>
@stop
