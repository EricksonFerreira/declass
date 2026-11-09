@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Circulação do acervo: empréstimos e devoluções" :create-url="auth()->user()->hasPermissao('emprestimo.create') ? route('emprestimo.create') : null" :create-can="'emprestimo.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .emp-cards .ux-card__avatar { background: linear-gradient(135deg,#8b5cf6,#6366f1); }
        .emp-cards .ux-card__avatar--atrasado { background: linear-gradient(135deg,#dc2626,#f97316); }
        .emp-cards .ux-card__avatar--devolvido { background: linear-gradient(135deg,#16a34a,#22c55e); }
    </style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        @include('partials.ux-view-toggle', ['viewKey' => 'emprestimo_index_view'])
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        {{-- Visao cards --}}
        <div class="ux-cards emp-cards">
            @forelse($emprestimos as $emp)
                @php $situacao = $emp->situacao(); @endphp
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar ux-card__avatar--{{ $situacao === 'atrasado' ? 'atrasado' : ($situacao === 'devolvido' ? 'devolvido' : '') }}">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $emp->livro->titulo ?? 'Livro #'.$emp->livro_id }}</div>
                            <div class="ux-card__sub">{{ $emp->aluno->pessoa->nome ?? 'Aluno #'.$emp->aluno_id }}</div>
                        </div>
                        <div class="ux-card__badges">
                            @if($situacao === 'devolvido')
                                <span class="ux-card__badge ux-card__badge--green">Devolvido</span>
                            @elseif($situacao === 'atrasado')
                                <span class="ux-card__badge ux-card__badge--red">Atrasado</span>
                            @else
                                <span class="ux-card__badge ux-card__badge--yellow">No prazo</span>
                            @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Empréstimo</span>
                            <span class="ux-card__meta-value">{{ optional($emp->data_emprestimo)->format('d/m/Y') }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Prevista</span>
                            <span class="ux-card__meta-value {{ $situacao === 'atrasado' ? 'text-danger font-weight-bold' : '' }}">{{ optional($emp->data_devolucao_prevista)->format('d/m/Y') }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Devolvido</span>
                            <span class="ux-card__meta-value">{{ optional($emp->data_devolucao)->format('d/m/Y') ?? '—' }}</span>
                        </div>
                    </div>
                    @if($emp->anexos->isNotEmpty() || $emp->observacoes)
                        <div class="ux-card__meta-row ux-card__meta-row--full">
                            <span class="ux-card__meta-label">Devolução</span>
                            <span class="ux-card__meta-value">
                                {{ \Illuminate\Support\Str::limit($emp->observacoes, 60) }}
                                @if($emp->anexos->isNotEmpty())
                                    @foreach($emp->anexos as $anx)
                                        <a href="{{ $anx->url() }}" target="_blank" class="badge badge-light border ml-1" title="Foto da avaria"><i class="fa fa-camera"></i></a>
                                    @endforeach
                                @endif
                            </span>
                        </div>
                    @endif
                    @if($emp->estaAtivo())
                        <div class="ux-card__foot">
                            @can('emprestimo.devolver')
                                <button type="button" class="btn btn-xs btn-success btn-devolver" title="Registrar devolução"
                                    data-url="{{ route('emprestimo.devolver', $emp->id) }}"
                                    data-livro="{{ $emp->livro->titulo ?? 'Livro #'.$emp->livro_id }}"
                                    data-aluno="{{ $emp->aluno->pessoa->nome ?? 'Aluno #'.$emp->aluno_id }}"><i class="fa fa-check"></i></button>
                            @endcan
                            @can('emprestimo.destroy')
                                <x-delete-button :action="route('emprestimo.destroy', $emp->id)" titulo="Excluir" mensagem="Excluir este empréstimo? O livro voltará a ficar disponível." />
                            @endcan
                        </div>
                    @endif
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-book-reader fa-2x mb-2 d-block"></i>
                    Nenhum empréstimo encontrado.
                </div>
            @endforelse
        </div>

        {{-- Visao tabela --}}
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Aluno</th>
                        <th>Livro</th>
                        <th>Empréstimo</th>
                        <th>Devolução prevista</th>
                        <th>Devolvido em</th>
                        <th>Situação</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($emprestimos as $emp)
                        @php $situacao = $emp->situacao(); @endphp
                        <tr>
                            <td class="font-weight-bold">{{ $emp->aluno->pessoa->nome ?? 'Aluno #'.$emp->aluno_id }}</td>
                            <td>
                                <div>{{ $emp->livro->titulo ?? 'Livro #'.$emp->livro_id }}</div>
                                @if($emp->livro?->autor)
                                    <div class="text-muted" style="font-size:.8rem;">{{ $emp->livro->autor }}</div>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ optional($emp->data_emprestimo)->format('d/m/Y') }}</td>
                            <td class="text-nowrap {{ $situacao === 'atrasado' ? 'text-danger font-weight-bold' : '' }}">
                                {{ optional($emp->data_devolucao_prevista)->format('d/m/Y') }}
                            </td>
                            <td class="text-nowrap">{{ optional($emp->data_devolucao)->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($situacao === 'devolvido')
                                    <span class="badge badge-success">Devolvido</span>
                                @elseif($situacao === 'atrasado')
                                    <span class="badge badge-danger">Atrasado</span>
                                @else
                                    <span class="badge badge-warning">No prazo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($emp->anexos->isNotEmpty())
                                    <a href="{{ $emp->anexos->first()->url() }}" target="_blank" class="btn btn-xs btn-outline-secondary" title="{{ $emp->anexos->count() }} foto(s) da avaria{{ $emp->observacoes ? ' — '.$emp->observacoes : '' }}"><i class="fa fa-camera"></i></a>
                                @endif
                                @if($emp->estaAtivo())
                                    @can('emprestimo.devolver')
                                        <button type="button" class="btn btn-xs btn-success btn-devolver" title="Registrar devolução"
                                            data-url="{{ route('emprestimo.devolver', $emp->id) }}"
                                            data-livro="{{ $emp->livro->titulo ?? 'Livro #'.$emp->livro_id }}"
                                            data-aluno="{{ $emp->aluno->pessoa->nome ?? 'Aluno #'.$emp->aluno_id }}"><i class="fa fa-check"></i></button>
                                    @endcan
                                    @can('emprestimo.destroy')
                                        <x-delete-button :action="route('emprestimo.destroy', $emp->id)" titulo="Excluir" mensagem="Excluir este empréstimo? O livro voltará a ficar disponível." />
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-book-reader fa-2x mb-2 d-block"></i>
                                Nenhum empréstimo encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($emprestimos->hasPages())
        <div class="card-footer">
            {{ $emprestimos->links() }}
        </div>
    @endif
</div>

{{-- Modal de devolucao: condicao do livro + fotos de avaria --}}
<div class="modal fade" id="modalDevolucao" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#15803d,#22c55e);color:#fff;border-bottom:none;">
                <h5 class="modal-title" style="font-size:.95rem;"><i class="fa fa-undo mr-1"></i> Registrar devolução</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color:#fff;opacity:.85;text-shadow:none;"><span>&times;</span></button>
            </div>
            <form id="formDevolucao" method="POST" action="#" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3" style="gap:.6rem;">
                        <span style="width:38px;height:38px;border-radius:10px;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-book-open"></i></span>
                        <div>
                            <div class="font-weight-bold" id="devLivro" style="line-height:1.2;"></div>
                            <small class="text-muted" id="devAluno"></small>
                        </div>
                    </div>
                    <label class="form-label" style="font-size:.78rem;">Condição do livro / avarias</label>
                    <textarea name="observacoes" class="form-control" rows="3" maxlength="1000"
                        placeholder="Ex.: capa rasgada, páginas soltas, livro molhado... Deixe em branco se estiver íntegro."></textarea>
                    <label class="form-label mt-2" style="font-size:.78rem;">Fotos da avaria <span class="text-muted">(opcional, até 5)</span></label>
                    <input type="file" name="fotos[]" class="form-control-file" accept="image/jpeg,image/png,image/webp" multiple>
                    <small class="text-muted">JPG, PNG ou WEBP — máx. 4 MB por foto.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-check mr-1"></i> Confirmar devolução</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(function () {
    var $modal = $('#modalDevolucao');
    $(document).on('click', '.btn-devolver', function () {
        $('#formDevolucao').attr('action', this.dataset.url);
        $('#devLivro').text(this.dataset.livro);
        $('#devAluno').text('Devolvido por ' + this.dataset.aluno);
        $('#formDevolucao textarea[name="observacoes"]').val('');
        $('#formDevolucao input[type="file"]').val('');
        $modal.modal('show');
    });
});
</script>
@endpush
