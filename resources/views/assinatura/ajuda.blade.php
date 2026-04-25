@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :subtitle="$subtitulo">
        <a href="{{ route('planos.hub') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-th-large mr-1"></i> Central
        </a>
        <a href="{{ route('plano.index') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-tags mr-1"></i> Catalogo
        </a>
        <a href="{{ route('assinatura.index') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-id-card mr-1"></i> Assinaturas
        </a>
    </x-page-title>
@stop

@section('content')
    <x-page-header-card :showHeader="false">
        <div class="card card-outline card-secondary mb-0" style="border-radius:12px;">
            <div class="card-body p-0">
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap:.75rem;">
                    <div class="text-muted" style="font-size:.9rem;">
                        Edite as perguntas em <code>docs/ajuda-planos.md</code> (cada pergunta deve ser um titulo <code>##</code>).
                    </div>
                    <div class="text-muted" style="font-size:.85rem;">
                        Total: <strong>{{ count($faqs) }}</strong>
                    </div>
                </div>

                <div class="p-3">
                    <div id="faqAccordion">
                        @foreach ($faqs as $i => $item)
                            @php
                                $collapseId = $item['id'] ?? ('faq-' . ($i + 1));
                                $headingId  = $collapseId . '-h';
                                $isFirst    = $i === 0;
                            @endphp
                            <div class="card mb-2" style="border-radius:10px; overflow:hidden;">
                                <div class="card-header py-2" id="{{ $headingId }}" style="background:#f8fafc;">
                                    <button class="btn btn-link p-0 text-left w-100 d-flex align-items-center justify-content-between"
                                            type="button" data-toggle="collapse" data-target="#{{ $collapseId }}"
                                            aria-expanded="{{ $isFirst ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                                        <span class="font-weight-bold" style="color:#0a1628;">
                                            <i class="fas fa-question-circle mr-2 text-secondary"></i>{{ $item['q'] }}
                                        </span>
                                        <i class="fas fa-chevron-down text-muted"></i>
                                    </button>
                                </div>
                                <div id="{{ $collapseId }}" class="collapse {{ $isFirst ? 'show' : '' }}"
                                     aria-labelledby="{{ $headingId }}" data-parent="#faqAccordion">
                                    <div class="card-body">
                                        <div class="markdown-body">{!! $item['aHtml'] !!}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </x-page-header-card>
@stop

@push('css')
    <style>
        .markdown-body h1 { font-size: 1.35rem; margin-top: 0; }
        .markdown-body h2 { font-size: 1.1rem; margin-top: 1.15rem; }
        .markdown-body h3 { font-size: 1.0rem; margin-top: 1rem; }
        .markdown-body pre { background: #0b1220; color: #e2e8f0; padding: .75rem; border-radius: .5rem; }
        .markdown-body code { background: rgba(15, 23, 42, .06); padding: .1rem .25rem; border-radius: .25rem; }
        .markdown-body table { width: 100%; }
        .markdown-body table th, .markdown-body table td { border: 1px solid #e5e7eb; padding: .4rem .5rem; }
        .markdown-body blockquote { border-left: 3px solid #cbd5e1; padding-left: .75rem; color: #475569; }
        .markdown-body hr { border-top: 1px solid #e5e7eb; }
    </style>
@endpush

