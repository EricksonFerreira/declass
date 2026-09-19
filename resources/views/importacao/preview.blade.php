@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@push('css')
    @include('partials.ux-index-styles')
@endpush

@section('content')
    @php
        $validas = collect($linhas)->whereNull('erro');
        $invalidas = collect($linhas)->whereNotNull('erro');
    @endphp

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title text-bold text-sm">
                <i class="fas fa-file-csv mr-1"></i> {{ $arquivoNome }}
                <span class="badge badge-success ml-2">{{ $validas->count() }} válidas</span>
                @if ($invalidas->isNotEmpty())
                    <span class="badge badge-danger">{{ $invalidas->count() }} com erro</span>
                @endif
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 55vh;">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="thead-light" style="position:sticky; top:0;">
                        <tr>
                            <th style="width:60px">Linha</th>
                            @foreach (array_keys($linhas[0]['dados']) as $col)
                                <th>{{ $col }}</th>
                            @endforeach
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($linhas as $item)
                            <tr class="{{ $item['erro'] ? 'table-danger' : '' }}">
                                <td class="text-muted">{{ $item['linha'] }}</td>
                                @foreach ($item['dados'] as $valor)
                                    <td class="small">{{ \Illuminate\Support\Str::limit((string) $valor, 40) }}</td>
                                @endforeach
                                <td>
                                    @if ($item['erro'])
                                        <span class="badge badge-danger" style="white-space:normal">{{ $item['erro'] }}</span>
                                    @else
                                        <span class="badge badge-success">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <a href="{{ route('importacao.index') }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
            <form method="POST" action="{{ route('importacao.importar', $tipo) }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="senha_inicial" value="{{ $senhaInicial }}">
                <button type="submit" class="btn btn-sm btn-success" {{ $validas->isEmpty() ? 'disabled' : '' }}
                    onclick="return confirm('Importar {{ $validas->count() }} registro(s) válidos?{{ $invalidas->isNotEmpty() ? ' As ' . $invalidas->count() . ' linhas com erro serão ignoradas.' : '' }}')">
                    <i class="fas fa-upload mr-1"></i>Confirmar importação de {{ $validas->count() }} registro(s)
                </button>
            </form>
        </div>
    </div>

    @if ($invalidas->isNotEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            As linhas com erro serão <strong>ignoradas</strong> na importação. Corrija o CSV e reenvie depois, se quiser incluí-las.
        </div>
    @endif
@stop
