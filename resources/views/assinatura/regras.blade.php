@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :subtitle="$subtitulo">
        <a href="{{ route('planos.hub') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-th-large mr-1"></i> Central
        </a>
        <a href="{{ route('assinatura.index') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-id-card mr-1"></i> Assinaturas
        </a>
    </x-page-title>
@stop

@section('content')
    <x-page-header-card :showHeader="false">
        <div class="card card-outline card-secondary mb-0">
            <div class="card-header py-2">
                <ul class="nav nav-pills card-header-pills" role="tablist" style="gap:.35rem;">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-regras" role="tab">
                            <i class="fas fa-book mr-1"></i> Regras do Sistema
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-logica" role="tab">
                            <i class="fas fa-sitemap mr-1"></i> Logica de Planos
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-regras" role="tabpanel">
                        <div class="markdown-body">{!! $regrasHtml !!}</div>
                    </div>
                    <div class="tab-pane fade" id="tab-logica" role="tabpanel">
                        <div class="markdown-body">{!! $logicaHtml !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </x-page-header-card>
@stop

@push('css')
    <style>
        .markdown-body h1 { font-size: 1.4rem; margin-top: 0; }
        .markdown-body h2 { font-size: 1.15rem; margin-top: 1.25rem; }
        .markdown-body h3 { font-size: 1.0rem; margin-top: 1rem; }
        .markdown-body pre { background: #0b1220; color: #e2e8f0; padding: .75rem; border-radius: .5rem; }
        .markdown-body code { background: rgba(15, 23, 42, .06); padding: .1rem .25rem; border-radius: .25rem; }
        .markdown-body table { width: 100%; }
        .markdown-body table th, .markdown-body table td { border: 1px solid #e5e7eb; padding: .4rem .5rem; }
        .markdown-body blockquote { border-left: 3px solid #cbd5e1; padding-left: .75rem; color: #475569; }
        .markdown-body hr { border-top: 1px solid #e5e7eb; }
    </style>
@endpush
