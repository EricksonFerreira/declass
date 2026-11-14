@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Acervo de livros das escolas" :create-url="auth()->user()->hasPermissao('livro.create') ? route('livro.create') : null" :create-can="'livro.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .liv-capa { width: 46px; height: 62px; border-radius: 6px; object-fit: cover; border: 1px solid #e5e9f0; flex-shrink: 0; }
        .liv-capa--vazio { width: 46px; height: 62px; border-radius: 6px; background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        .liv-loc { display: inline-flex; align-items: center; gap: 4px; font-size: .72rem; color: #475569; background: #f1f5f9; border-radius: 5px; padding: 2px 7px; }
        .liv-com { font-size: .7rem; color: #92400e; }
        .liv-item { cursor: pointer; }
        .liv-item:hover { background: #f0f6ff; }
        .liv-cards .ux-card.liv-item:hover { transform: none; box-shadow: 0 4px 14px rgba(11,18,32,.10); }

        .liv-modal .modal-content { border-radius: 14px; overflow: hidden; border: none; }
        .liv-modal .modal-header { background: linear-gradient(135deg,#4c1d95,#7c3aed); color: #fff; padding: .7rem 1rem; border-bottom: none; }
        .liv-modal .close { color: #fff; opacity: .85; text-shadow: none; }
        .liv-head { display: flex; gap: .9rem; align-items: flex-start; margin-bottom: 12px; }
        .liv-head .liv-capa, .liv-head .liv-capa--vazio { width: 74px; height: 100px; }
        .liv-head-info { flex: 1; min-width: 0; }
        .liv-tit { font-size: 1.05rem; font-weight: 800; color: #1f2937; line-height: 1.25; }
        .liv-sub { font-size: .8rem; color: #64748b; margin-top: 2px; }
        .liv-badges { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 7px; }
        .liv-badges .badge { font-size: .68rem; padding: 4px 8px; }
        .liv-meta { display: flex; flex-wrap: wrap; gap: 4px 18px; margin-bottom: 12px; }
        .liv-meta-item { font-size: .78rem; color: #475569; }
        .liv-meta-item i { color: #7c3aed; margin-right: 5px; width: 13px; text-align: center; }
        .liv-lbl { font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 3px; }
        .liv-bloco { background: #f8fafc; border: 1px solid #e5e9f0; border-radius: 8px; padding: 10px 12px; font-size: .83rem; margin-bottom: 10px; }
        .liv-bloco--com { background: #fffbeb; border-color: #fde68a; }
    </style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        @include('partials.ux-view-toggle', ['viewKey' => 'livro_index_view'])
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        {{-- Visao cards --}}
        <div class="ux-cards liv-cards">
            @forelse($livros as $livro)
                @php $disp = $livro->disponiveis; @endphp
                <article class="ux-card liv-item"
                    data-titulo="{{ $livro->titulo }}"
                    data-autor="{{ $livro->autor ?? '' }}"
                    data-editora="{{ $livro->editora ?? '' }}"
                    data-isbn="{{ $livro->isbn ?? '' }}"
                    data-categoria="{{ \App\Models\Livro::CATEGORIAS[$livro->categoria] ?? $livro->categoria }}"
                    data-escola="{{ $livro->escola->nome ?? '' }}"
                    data-localizacao="{{ $livro->localizacao ?? '' }}"
                    data-ano="{{ $livro->ano_publicacao ?? '' }}"
                    data-exemplares="{{ $livro->exemplares }}"
                    data-disponiveis="{{ $disp }}"
                    data-status="{{ $livro->esta_ativo ? 'Ativo' : 'Inativo' }}"
                    data-descricao="{{ $livro->descricao ?? '' }}"
                    data-capa="{{ $livro->capa_url ?? '' }}"
                    data-com="{{ $livro->emprestimosAtivos->map(fn ($e) => $e->aluno->pessoa->nome ?? 'Aluno #'.$e->aluno_id)->implode(', ') }}"
                    data-edit-url="{{ auth()->user()->hasPermissao('livro.edit') ? route('livro.edit', $livro->id) : '' }}">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        @if($livro->capa_url)
                            <img src="{{ $livro->capa_url }}" alt="" class="liv-capa">
                        @else
                            <span class="liv-capa--vazio"><i class="fas fa-book"></i></span>
                        @endif
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $livro->titulo }}</div>
                            <div class="ux-card__sub">{{ $livro->autor ?? '—' }}{{ $livro->editora ? ' · '.$livro->editora : '' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge ux-card__badge--gray">{{ \App\Models\Livro::CATEGORIAS[$livro->categoria] ?? $livro->categoria }}</span>
                            @if($disp > 0)
                                <span class="ux-card__badge ux-card__badge--green">{{ $disp }}/{{ $livro->exemplares }} disp.</span>
                            @else
                                <span class="ux-card__badge ux-card__badge--red">esgotado</span>
                            @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Escola</span>
                            <span class="ux-card__meta-value">{{ $livro->escola->nome ?? '—' }}</span>
                        </div>
                        @if($livro->localizacao)
                            <div class="ux-card__meta-row">
                                <span class="ux-card__meta-label">Local</span>
                                <span class="ux-card__meta-value"><span class="liv-loc"><i class="fa fa-map-marker-alt"></i>{{ $livro->localizacao }}</span></span>
                            </div>
                        @endif
                        @if($livro->emprestimosAtivos->isNotEmpty())
                            <div class="ux-card__meta-row ux-card__meta-row--full">
                                <span class="ux-card__meta-label">Com</span>
                                <span class="ux-card__meta-value liv-com">{{ $livro->emprestimosAtivos->map(fn ($e) => $e->aluno->pessoa->nome ?? 'Aluno #'.$e->aluno_id)->implode(', ') }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="ux-card__foot">
                        @can('livro.edit')
                            <a href="{{ route('livro.edit', $livro->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('livro.destroy')
                            <x-delete-button :action="route('livro.destroy', $livro->id)" titulo="Excluir" mensagem="Excluir o livro &quot;{{ $livro->titulo }}&quot;?" />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-book fa-2x mb-2 d-block"></i>
                    Nenhum livro cadastrado.
                </div>
            @endforelse
        </div>

        {{-- Visao tabela --}}
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categoria</th>
                        <th>Escola</th>
                        <th>Localização</th>
                        <th class="text-center">Exemplares</th>
                        <th class="text-center">Disponíveis</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($livros as $livro)
                        @php $disp = $livro->disponiveis; @endphp
                        <tr class="liv-item"
                            data-titulo="{{ $livro->titulo }}"
                            data-autor="{{ $livro->autor ?? '' }}"
                            data-editora="{{ $livro->editora ?? '' }}"
                            data-isbn="{{ $livro->isbn ?? '' }}"
                            data-categoria="{{ \App\Models\Livro::CATEGORIAS[$livro->categoria] ?? $livro->categoria }}"
                            data-escola="{{ $livro->escola->nome ?? '' }}"
                            data-localizacao="{{ $livro->localizacao ?? '' }}"
                            data-ano="{{ $livro->ano_publicacao ?? '' }}"
                            data-exemplares="{{ $livro->exemplares }}"
                            data-disponiveis="{{ $disp }}"
                            data-status="{{ $livro->esta_ativo ? 'Ativo' : 'Inativo' }}"
                            data-descricao="{{ $livro->descricao ?? '' }}"
                            data-capa="{{ $livro->capa_url ?? '' }}"
                            data-com="{{ $livro->emprestimosAtivos->map(fn ($e) => $e->aluno->pessoa->nome ?? 'Aluno #'.$e->aluno_id)->implode(', ') }}"
                            data-edit-url="{{ auth()->user()->hasPermissao('livro.edit') ? route('livro.edit', $livro->id) : '' }}">
                            <td>
                                <div class="font-weight-bold">{{ $livro->titulo }}</div>
                                @if($livro->editora)
                                    <div class="text-muted" style="font-size:.8rem;">{{ $livro->editora }}{{ $livro->ano_publicacao ? ' · '.$livro->ano_publicacao : '' }}</div>
                                @endif
                            </td>
                            <td>{{ $livro->autor ?? '—' }}</td>
                            <td><span class="badge badge-light border">{{ \App\Models\Livro::CATEGORIAS[$livro->categoria] ?? $livro->categoria }}</span></td>
                            <td>{{ $livro->escola->nome ?? '—' }}</td>
                            <td>
                                @if($livro->localizacao)
                                    <span class="liv-loc"><i class="fa fa-map-marker-alt"></i>{{ $livro->localizacao }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                                @if($livro->emprestimosAtivos->isNotEmpty())
                                    <div class="liv-com" title="{{ $livro->emprestimosAtivos->map(fn ($e) => $e->aluno->pessoa->nome ?? '')->implode(', ') }}">
                                        <i class="fa fa-user-clock"></i> {{ $livro->emprestimosAtivos->count() }} emprestado(s)
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">{{ $livro->exemplares }}</td>
                            <td class="text-center">
                                @php $disp = $livro->disponiveis; @endphp
                                @if($disp > 0)
                                    <span class="badge badge-success">{{ $disp }}</span>
                                @else
                                    <span class="badge badge-danger">0</span>
                                @endif
                            </td>
                            <td>
                                @if($livro->esta_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('livro.edit')
                                    <a href="{{ route('livro.edit', $livro->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('livro.destroy')
                                    <x-delete-button :action="route('livro.destroy', $livro->id)" titulo="Excluir" mensagem="Excluir o livro &quot;{{ $livro->titulo }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-book fa-2x mb-2 d-block"></i>
                                Nenhum livro cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($livros->hasPages())
        <div class="card-footer">
            {{ $livros->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes do livro --}}
<div class="modal fade liv-modal" id="modalLivro" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:.95rem;"><i class="fas fa-book-open mr-1"></i> Detalhes do livro</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="liv-head">
                    <span class="liv-capa--vazio" id="livMVazio"><i class="fas fa-book"></i></span>
                    <img src="" alt="" class="liv-capa" id="livMCapa" style="display:none;">
                    <div class="liv-head-info">
                        <div class="liv-tit" id="livMTitulo"></div>
                        <div class="liv-sub" id="livMSub"></div>
                        <div class="liv-badges">
                            <span class="badge badge-light border" id="livMCat"></span>
                            <span class="badge badge-success" id="livMDisp"></span>
                            <span class="badge badge-secondary" id="livMStatus"></span>
                        </div>
                    </div>
                </div>
                <div class="liv-meta">
                    <span class="liv-meta-item"><i class="fas fa-school"></i><span id="livMEscola"></span></span>
                    <span class="liv-meta-item" id="livMRowLoc" style="display:none;"><i class="fas fa-map-marker-alt"></i><span id="livMLoc"></span></span>
                    <span class="liv-meta-item" id="livMRowIsbn" style="display:none;"><i class="fas fa-barcode"></i><span id="livMIsbn"></span></span>
                    <span class="liv-meta-item" id="livMRowAno" style="display:none;"><i class="far fa-calendar"></i><span id="livMAno"></span></span>
                </div>
                <div id="livMWrapDesc" style="display:none;">
                    <div class="liv-lbl">Sinopse</div>
                    <div class="liv-bloco" id="livMDesc"></div>
                </div>
                <div id="livMWrapCom" style="display:none;">
                    <div class="liv-lbl">Emprestado para</div>
                    <div class="liv-bloco liv-bloco--com" id="livMCom"></div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-sm btn-warning mr-auto" id="livMBtnEdit" style="display:none;"><i class="fa fa-edit mr-1"></i> Editar</a>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(function () {
    var $modal = $('#modalLivro');
    $(document).on('click', '.liv-item', function (e) {
        // acoes internas (editar, excluir) nao abrem o modal
        if ($(e.target).closest('a, button, form').length) return;

        var d = this.dataset;
        $('#livMTitulo').text(d.titulo || '');
        $('#livMSub').text([d.autor, d.editora].filter(Boolean).join(' · ') || '—');
        $('#livMCat').text(d.categoria || '');
        $('#livMDisp').text(d.disponiveis + '/' + d.exemplares + ' disp.')
            .attr('class', 'badge ' + (+d.disponiveis > 0 ? 'badge-success' : 'badge-danger'));
        $('#livMStatus').text(d.status || '')
            .attr('class', 'badge ' + (d.status === 'Ativo' ? 'badge-success' : 'badge-secondary'));
        $('#livMEscola').text(d.escola || '—');
        $('#livMLoc').text(d.localizacao || '');
        $('#livMRowLoc').toggle(!!d.localizacao);
        $('#livMIsbn').text(d.isbn || '');
        $('#livMRowIsbn').toggle(!!d.isbn);
        $('#livMAno').text(d.ano || '');
        $('#livMRowAno').toggle(!!d.ano);
        $('#livMDesc').text(d.descricao || '');
        $('#livMWrapDesc').toggle(!!d.descricao);
        $('#livMCom').text(d.com || '');
        $('#livMWrapCom').toggle(!!d.com);

        var img = $('#livMCapa');
        if (d.capa) {
            img.attr('src', d.capa).show();
            $('#livMVazio').hide();
        } else {
            img.hide();
            $('#livMVazio').show();
        }
        var btnEdit = $('#livMBtnEdit');
        btnEdit.toggle(!!d.editUrl).attr('href', d.editUrl || '#');

        $modal.modal('show');
    });
});
</script>
@endpush
