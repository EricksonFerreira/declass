@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :subtitle="$subtitulo" />
@stop

@section('content')
    <div class="row">
        <!-- Categories Sidebar -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm" style="border-radius:12px; overflow:hidden;">
                <div class="card-header bg-light py-3">
                    <h6 class="font-weight-bold text-dark m-0">
                        <i class="fas fa-list mr-2"></i> Módulos do Sistema
                    </h6>
                </div>
                <div class="list-group list-group-flush" id="faq-categories-tab" role="tablist">
                    @foreach ($faqs as $category => $items)
                        @php $catSlug = Str::slug($category); @endphp
                        <a class="list-group-item list-group-item-action @if($loop->first) active @endif d-flex align-items-center justify-content-between py-3" 
                           id="cat-{{ $catSlug }}-tab" data-toggle="pill" href="#cat-{{ $catSlug }}" 
                           role="tab" aria-controls="cat-{{ $catSlug }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" style="gap: 10px;">
                            <span>
                                <i class="fas fa-folder mr-2 text-secondary"></i>
                                <strong>{{ $category }}</strong>
                            </span>
                            <span class="badge badge-secondary badge-pill">{{ count($items) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Category Content -->
        <div class="col-md-9">
            <div class="tab-content" id="faq-categories-content">
                @foreach ($faqs as $category => $items)
                    @php $catSlug = Str::slug($category); @endphp
                    <div class="tab-pane fade @if($loop->first) show active @endif" id="cat-{{ $catSlug }}" role="tabpanel" aria-labelledby="cat-{{ $catSlug }}-tab">
                        <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
                            <div class="card-header py-3 bg-white border-bottom">
                                <h5 class="font-weight-bold text-dark m-0">
                                    <i class="fas fa-book-open mr-2 text-blue"></i>
                                    {{ $category }}
                                </h5>
                            </div>
                            <div class="card-body p-3 bg-light">
                                <div class="accordion" id="accordion-{{ $catSlug }}">
                                    @foreach ($items as $j => $item)
                                        @php
                                            $collapseId = $item['id'] ?? ('faq-' . $catSlug . '-' . $j);
                                            $headingId  = $collapseId . '-h';
                                        @endphp
                                        <div class="card mb-2 border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                                            <div class="card-header py-3 border-0" id="{{ $headingId }}" style="background:#ffffff;">
                                                <button class="btn btn-link p-0 text-left w-100 d-flex align-items-center justify-content-between"
                                                        type="button" data-toggle="collapse" data-target="#{{ $collapseId }}"
                                                        aria-expanded="false" aria-controls="{{ $collapseId }}">
                                                    <span class="font-weight-bold text-dark" style="font-size: 0.95rem; line-height: 1.4;">
                                                        <i class="fas fa-question-circle mr-2 text-primary"></i>{{ $item['q'] }}
                                                    </span>
                                                    <i class="fas fa-chevron-down text-muted" style="transition: transform 0.2s;"></i>
                                                </button>
                                            </div>
                                            <div id="{{ $collapseId }}" class="collapse"
                                                 aria-labelledby="{{ $headingId }}" data-parent="#accordion-{{ $catSlug }}">
                                                <div class="card-body border-top bg-light text-secondary">
                                                    <div class="markdown-body">{!! $item['aHtml'] !!}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        #faq-categories-tab .list-group-item.active {
            background-color: var(--primary, #007bff);
            border-color: var(--primary, #007bff);
            color: #fff !important;
        }
        #faq-categories-tab .list-group-item.active i {
            color: #fff !important;
        }
        .markdown-body h1 { font-size: 1.35rem; margin-top: 0; }
        .markdown-body h2 { font-size: 1.1rem; margin-top: 1.15rem; }
        .markdown-body h3 { font-size: 1.0rem; margin-top: 1rem; }
        .markdown-body pre { background: #0b1220; color: #e2e8f0; padding: .75rem; border-radius: .5rem; }
        .markdown-body code { background: rgba(15, 23, 42, .06); padding: .1rem .25rem; border-radius: .25rem; }
        .markdown-body table { width: 100%; margin-bottom: 1rem; }
        .markdown-body table th, .markdown-body table td { border: 1px solid #e5e7eb; padding: .4rem .5rem; }
        .markdown-body blockquote { border-left: 3px solid #cbd5e1; padding-left: .75rem; color: #475569; }
        .markdown-body hr { border-top: 1px solid #e5e7eb; }
    </style>
@endpush
