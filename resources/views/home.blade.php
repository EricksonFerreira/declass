@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    <div class="row">
        @foreach ($items as $grupo => $grupoItems)
            @if($grupo !== 'sem_grupo')
                <h6 class="text-gray col-12 mt-3">{{ $grupoItems['grupo'] }}</h6>
            @endif
            @foreach ($grupoItems['items'] as $i)
                <a href="{{ route($i['route']) }}" class="col-12 col-sm-6 col-md-3 col-lg-auto">
                    <div class="info-box {{ $grupoItems['backgroundIcone'] }}">
                        <span class="info-box-icon">
                            <i class="{{ $i['icone'] }}"></i>
                        </span>
                        <div class="info-box-content text-center">
                            <span class="info-box-text font-weight-normal">{{ $i['nome'] }}</span>
                            <span class="info-box-number font-weight-bold">
                                {{ $i['prefix'] }} {{ $i['valor'] }}{{ $i['suffix'] }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        @endforeach
    </div>
@stop

@section('js')
    <script></script>
@stop
