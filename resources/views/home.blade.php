@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    <div class="row">
        @foreach ($items as $i)
            <a href="{{ route($i['route']) }}" class="col-12 col-sm-6 col-md-2">
                <div class="info-box  {{ $i['backgroundIcone'] }}">
                    <span class="info-box-icon">
                        <i class="{{ $i['icone'] }}"></i>
                    </span>
                    <div class="info-box-content   text-center">
                        <span class="info-box-text font-weight-normal ">{{ $i['nome'] }}</span>
                        <span class="info-box-number font-weight-bold">
                            {{ $i['prefix'] }} {{ $i['valor'] }}{{ $i['suffix'] }}
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
        </div>
@stop

@section('js')
    <script></script>
@stop
