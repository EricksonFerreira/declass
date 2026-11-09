@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($aviso) ? 'Editar Aviso' : 'Novo Aviso'" />
@stop

@push('css')
<style>
    .av-sec { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:1rem 0 .6rem; padding-bottom:.25rem; border-bottom:1px solid #e5e7eb; }
    .av-sec:first-child { margin-top:0; }
    .av-sec i { width:16px; text-align:center; margin-right:.15rem; }
    .av-chip { display:inline-flex; align-items:center; gap:.25rem; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; border-radius:999px; padding:.1rem .55rem; font-size:.75rem; margin:.15rem .2rem .15rem 0; }
    .av-chip .x { cursor:pointer; font-weight:700; }
    .av-chk { border:1px solid #eef1f6; border-radius:8px; padding:.4rem .6rem; margin-bottom:.3rem; cursor:pointer; }
    .av-chk:hover { background:#f4f7ff; }
    .av-chk input { margin-right:.4rem; }
    .av-tg { font-size:.68rem; font-weight:700; text-transform:uppercase; color:#9ca3af; margin:.6rem 0 .2rem; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($aviso) ? route('aviso.update', $aviso->id) : route('aviso.store') }}" id="avisoForm">
            @csrf
            @if(isset($aviso))
                @method('PUT')
            @endif

            <div class="av-sec"><i class="fa fa-bullhorn"></i> Destinatários</div>
            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola" :options="$escolas ?? []" :selected="old('escola_id', $aviso->escola_id ?? '')" required />
                </div>
                <div class="col-md-5">
                    <label class="form-label">Turmas</label>
                    <div class="input-group">
                        <input type="text" id="av_turmas_display" class="form-control" readonly tabindex="-1" value="Escola toda">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#avTurmasModal"><i class="fa fa-users"></i> Selecionar</button>
                        </div>
                    </div>
                    <div id="av-chips" class="mt-1"></div>
                    <small class="text-muted">Sem seleção = aviso para toda a escola.</small>
                </div>
                <div class="col-md-3">
                    <x-form-select name="destino" label="Público" :options="$destinos ?? []" :selected="old('destino', $aviso->destino ?? 'familias')" required />
                </div>
            </div>

            <div class="av-sec"><i class="fa fa-pen"></i> Conteúdo</div>
            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="tipo" label="Tipo" :options="$tipos ?? []" :selected="old('tipo', $aviso->tipo ?? 'info')" required />
                </div>
                <div class="col-md-5">
                    <x-form-input name="titulo" label="Título" :value="old('titulo', $aviso->titulo ?? '')" required maxlength="160" placeholder="Ex.: Reunião de pais e mestres" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_publicacao" type="date" label="Data de publicação" :value="old('data_publicacao', isset($aviso) ? optional($aviso->data_publicacao)->format('Y-m-d') : now()->format('Y-m-d'))" required />
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label class="form-label">Mensagem <span class="text-danger">*</span></label>
                    <textarea name="mensagem" rows="6" class="form-control" required maxlength="5000">{{ old('mensagem', $aviso->mensagem ?? '') }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $aviso->ativo ?? 'S')" />
                </div>
            </div>

            <div class="mt-3 pt-3" style="border-top:1px solid #f0f3f8;">
                <a href="{{ route('aviso.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check mr-1"></i> Salvar aviso</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal selecao de turmas --}}
<div class="modal fade" id="avTurmasModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:10px; overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff;">
                <h5 class="modal-title">Selecionar turmas</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" id="avTurmaBusca" class="form-control form-control-sm mb-2" placeholder="Buscar turma...">
                <div class="d-flex mb-2" style="gap:.4rem;">
                    <button type="button" class="btn btn-xs btn-outline-primary" id="avTodas">Marcar todas</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" id="avNenhuma">Limpar</button>
                    <span class="ml-auto text-muted small align-self-center"><span id="avSelCount">0</span> selecionadas</span>
                </div>
                <div id="avTurmasLista" style="max-height:320px; overflow:auto;">
                    @foreach($turmas ?? [] as $tid => $tnome)
                        <label class="av-chk d-block mb-0" data-nome="{{ mb_strtolower($tnome) }}">
                            <input type="checkbox" name="turma_ids[]" form="avisoForm" value="{{ $tid }}" class="av-chk-inp"
                                {{ in_array($tid, old('turma_ids', isset($aviso) ? $aviso->turmas->pluck('id')->all() : [])) ? 'checked' : '' }}>
                            {{ $tnome }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Concluir</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const display = document.getElementById('av_turmas_display');
    const chips = document.getElementById('av-chips');
    const count = document.getElementById('avSelCount');
    const busca = document.getElementById('avTurmaBusca');

    function selecionadas() {
        return Array.from(document.querySelectorAll('.av-chk-inp:checked'));
    }
    function sincronizar() {
        const sel = selecionadas();
        count.textContent = sel.length;
        chips.innerHTML = '';
        sel.forEach(function (c) {
            const nome = c.parentElement.textContent.trim();
            chips.insertAdjacentHTML('beforeend', '<span class="av-chip">' + nome + '</span>');
        });
        display.value = sel.length ? sel.length + ' turma(s) selecionada(s)' : 'Escola toda';
    }
    document.querySelectorAll('.av-chk-inp').forEach(function (c) {
        c.addEventListener('change', sincronizar);
    });
    document.getElementById('avTodas').addEventListener('click', function () {
        document.querySelectorAll('.av-chk').forEach(function (l) {
            if (l.style.display !== 'none') {
                const c = l.querySelector('.av-chk-inp');
                c.checked = true;
            }
        });
        sincronizar();
    });
    document.getElementById('avNenhuma').addEventListener('click', function () {
        document.querySelectorAll('.av-chk-inp').forEach(function (c) { c.checked = false; });
        sincronizar();
    });
    busca.addEventListener('input', function () {
        const t = busca.value.toLowerCase();
        document.querySelectorAll('.av-chk').forEach(function (l) {
            l.style.display = l.dataset.nome.includes(t) ? '' : 'none';
        });
    });
    sincronizar();
})();
</script>
@stop
