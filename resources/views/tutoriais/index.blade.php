@php
    $videosJs = $tutoriais->mapWithKeys(fn ($t) => [$t->id => [
        'titulo' => $t->titulo,
        'descricao' => $t->descricao,
        'video' => $t->video_exibicao,
    ]])->all();
@endphp

@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Aprenda a usar o DECLOCK com vídeos curtos organizados por assunto" />
@stop

@section('content')
    <!-- Busca -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm" style="border-radius:12px; border:1px solid #e2e8f0;">
                <div class="card-body p-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-radius:8px 0 0 8px; border-color:#cbd5e1;">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text" id="tutorial-search" class="form-control border-left-0"
                               placeholder="Buscar tutorial..."
                               style="border-radius:0 8px 8px 0; border-color:#cbd5e1; height:48px; font-size:1.05rem;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($tutoriais->isEmpty())
        <div class="alert alert-info text-center shadow-sm p-5" style="border-radius:12px;">
            <i class="fas fa-video fa-2x mb-3 d-block"></i>
            <h5 class="font-weight-bold">Nenhum tutorial disponível ainda</h5>
            <p class="text-muted mb-0">Em breve publicaremos vídeos para ajudar você a usar o sistema.</p>
        </div>
    @else
        <!-- Abas de categoria -->
        <ul class="nav nav-pills tutorial-tabs mb-4" id="tutorial-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="#" data-cat="all">
                    Todos <span class="badge badge-light ml-1">{{ $tutoriais->count() }}</span>
                </a>
            </li>
            @foreach ($categorias as $categoria)
                <li class="nav-item">
                    <a class="nav-link" href="#" data-cat="{{ $categoria->id }}">
                        {{ $categoria->nome }}
                        <span class="badge badge-light ml-1">{{ $tutoriais->where('tutorial_categoria_id', $categoria->id)->count() }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <!-- Grid de tutoriais -->
        <div class="row" id="tutoriais-grid">
            @foreach ($tutoriais as $tutorial)
                @php $video = $tutorial->video_exibicao; @endphp
                <div class="col-sm-6 col-lg-4 mb-4 tutorial-card"
                     data-cat="{{ $tutorial->tutorial_categoria_id ?? 'sem-categoria' }}"
                     data-titulo="{{ Str::lower($tutorial->titulo) }}"
                     data-descricao="{{ Str::lower($tutorial->descricao ?? '') }}">
                    <div class="card shadow-sm h-100 tutorial-card-inner" style="border-radius:14px; overflow:hidden; border:1px solid #e2e8f0;">
                        <div class="tutorial-thumb" role="button" data-toggle="modal" data-target="#tutorialModal"
                             data-id="{{ $tutorial->id }}">
                            @if ($tutorial->capa_url)
                                <img src="{{ $tutorial->capa_url }}" alt="{{ $tutorial->titulo }}" class="tutorial-thumb-img">
                            @else
                                <div class="tutorial-thumb-placeholder">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                            @endif
                            <div class="tutorial-play">
                                <span class="tutorial-play-btn"><i class="fas fa-play"></i></span>
                            </div>
                            @if ($tutorial->principal)
                                <span class="badge badge-warning tutorial-badge-principal">
                                    <i class="fas fa-star mr-1"></i>Principal
                                </span>
                            @endif
                        </div>
                        <div class="card-body p-3">
                            <h6 class="font-weight-bold mb-1 tutorial-titulo">{{ $tutorial->titulo }}</h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <small class="text-muted">
                                    <i class="fas fa-folder mr-1"></i>{{ $tutorial->categoria->nome ?? 'Geral' }}
                                </small>
                                @if ($tutorial->descricao)
                                    <a href="#" class="small tutorial-desc-toggle" data-toggle="collapse"
                                       data-target="#desc-{{ $tutorial->id }}">Ver descrição</a>
                                @endif
                            </div>
                            @if ($tutorial->descricao)
                                <div class="collapse mt-2" id="desc-{{ $tutorial->id }}">
                                    <p class="small text-muted mb-0">{{ $tutorial->descricao }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="no-results-alert" class="alert alert-warning text-center shadow-sm p-4 d-none" style="border-radius:12px;">
            <i class="fas fa-search mr-2"></i>
            <strong>Nenhum tutorial encontrado</strong>
            <p class="text-muted mb-0">Tente outro termo ou categoria.</p>
        </div>
    @endif

    <!-- Modal do player -->
    <div class="modal fade" id="tutorialModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:14px; overflow:hidden;">
                <div class="modal-header py-2">
                    <h6 class="modal-title font-weight-bold" id="tutorialModalTitle"></h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 bg-dark" id="tutorialModalBody"></div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        .tutorial-tabs { gap: .5rem; flex-wrap: wrap; }
        .tutorial-tabs .nav-link {
            border-radius: 999px; background: #f1f5f9; color: #334155;
            font-weight: 600; padding: .45rem 1.1rem; border: 1px solid #e2e8f0;
        }
        .tutorial-tabs .nav-link.active {
            background: var(--primary, #007bff); color: #fff;
            border-color: var(--primary, #007bff);
        }
        .tutorial-tabs .nav-link.active .badge { color: var(--primary, #007bff); }

        .tutorial-card-inner { transition: transform .15s ease, box-shadow .15s ease; }
        .tutorial-card-inner:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.2rem rgba(15,23,42,.15) !important; }

        .tutorial-thumb { position: relative; cursor: pointer; background: #0f172a; }
        .tutorial-thumb-img { width: 100%; height: 190px; object-fit: cover; display: block; }
        .tutorial-thumb-placeholder {
            height: 190px; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #1e3a8a 0%, #0b3d91 55%, #1565c0 100%);
            color: rgba(255,255,255,.85); font-size: 3rem;
        }
        .tutorial-play {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            background: rgba(2,6,23,.25); opacity: 0; transition: opacity .15s ease;
        }
        .tutorial-thumb:hover .tutorial-play { opacity: 1; }
        .tutorial-play-btn {
            width: 58px; height: 58px; border-radius: 50%; background: rgba(255,255,255,.95);
            display: flex; align-items: center; justify-content: center;
            color: #0b3d91; font-size: 1.3rem; padding-left: 4px;
            box-shadow: 0 4px 14px rgba(0,0,0,.35);
        }
        .tutorial-badge-principal { position: absolute; top: 10px; left: 10px; font-size: .72rem; }

        @media (max-width: 576px) {
            .tutorial-thumb-img, .tutorial-thumb-placeholder { height: 160px; }
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const videos = @json($videosJs);

            const searchInput = document.getElementById('tutorial-search');
            const noResults = document.getElementById('no-results-alert');
            const cards = document.querySelectorAll('.tutorial-card');
            const tabs = document.querySelectorAll('#tutorial-tabs .nav-link');
            let categoriaAtiva = 'all';

            function aplicarFiltros() {
                const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
                let visiveis = 0;

                cards.forEach(function (card) {
                    const catOk = categoriaAtiva === 'all' || card.dataset.cat === categoriaAtiva;
                    const textoOk = query === ''
                        || card.dataset.titulo.includes(query)
                        || card.dataset.descricao.includes(query);
                    const show = catOk && textoOk;
                    card.style.display = show ? '' : 'none';
                    if (show) visiveis++;
                });

                if (noResults) {
                    noResults.classList.toggle('d-none', visiveis > 0);
                }
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    categoriaAtiva = this.dataset.cat;
                    aplicarFiltros();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', aplicarFiltros);
            }

            // Player no modal
            $('#tutorialModal').on('show.bs.modal', function (e) {
                const id = $(e.relatedTarget).data('id');
                const item = videos[id];
                const body = document.getElementById('tutorialModalBody');
                document.getElementById('tutorialModalTitle').textContent = item ? item.titulo : '';
                body.innerHTML = '';

                if (!item || !item.video) {
                    body.innerHTML = '<div class="text-center text-muted p-5 bg-light">Vídeo não disponível.</div>';
                    return;
                }

                if (item.video.tipo === 'arquivo') {
                    body.innerHTML = '<video controls autoplay class="w-100" style="max-height:70vh;" src="' + item.video.url + '"></video>';
                } else if (item.video.tipo === 'embed') {
                    body.innerHTML = '<div class="embed-responsive embed-responsive-16by9">'
                        + '<iframe class="embed-responsive-item" src="' + item.video.url + '" allowfullscreen allow="autoplay"></iframe></div>';
                } else {
                    body.innerHTML = '<div class="text-center p-5 bg-light">'
                        + '<p class="text-muted">Este tutorial está em um link externo.</p>'
                        + '<a class="btn btn-primary" target="_blank" rel="noopener" href="' + item.video.url + '">'
                        + '<i class="fas fa-external-link-alt mr-1"></i>Abrir vídeo</a></div>';
                }
            });

            // Para o video ao fechar
            $('#tutorialModal').on('hidden.bs.modal', function () {
                document.getElementById('tutorialModalBody').innerHTML = '';
            });
        });
    </script>
@endpush
