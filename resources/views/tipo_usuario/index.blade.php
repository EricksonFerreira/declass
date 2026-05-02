@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('tipo_usuario.create')" :createCan="'tipo_usuario.create'">
        <small class="text-muted ml-2" style="font-size:.78rem;">Defina quais funcionalidades cada perfil pode acessar</small>
    </x-page-title>
@stop

@section('content')

    @if (!empty($filters))
        <div class="tp-filter-sticky">
            <x-filter
                :filters="$filters"
                :additionalFilters="$additionalFilters"
                :itensFiltered="$itensFiltered"
                :nomeFiltro="$nomeFiltro"
            />
        </div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header px-3 py-2">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">
                    PERFIS
                    <span class="badge badge-success">{{ $tiposUsuario->total() }}</span>
                </h3>

                <div class="align-bottom d-flex flex-row justify-between" style="gap:6px;flex-wrap:wrap;">
                    <form action="{{ route('tipo_usuario.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>

                    <div class="tp-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary active" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i>Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i>Tabela
                        </button>
                    </div>

                    <div class="d-flex align-items-end">
                        {{ $tiposUsuario->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0 tp-index-body" data-view="cards">

            {{-- ── CARDS ── --}}
            <div class="tp-cards p-3">
                @forelse ($tiposUsuario as $tipoUsuario)
                    @php
                        $permCount = $tipoUsuario->permissoes->count();
                        $grupos = $tipoUsuario->permissoes->groupBy(fn($p) => explode('.', $p->name)[0]);
                        $totalGrupos = $grupos->count();
                        $iniciais = collect(preg_split('/\s+/', trim($tipoUsuario->name)))
                            ->filter()->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'PF';
                    @endphp
                    <div class="tp-card">
                        <div class="tp-card__accent"></div>
                        <div class="tp-card__body">
                            <div class="tp-card__head">
                                <div class="tp-card__avatar">{{ $iniciais }}</div>
                                <div class="tp-card__head-info">
                                    <div class="tp-card__name">{{ $tipoUsuario->name }}</div>
                                    <div class="tp-card__sub">{{ $permCount }} permissao(oes) · {{ $totalGrupos }} modulo(s)</div>
                                </div>
                            </div>

                            <div class="tp-card__perms">
                                @foreach ($grupos->keys()->take(8) as $g)
                                    <span class="tp-badge">{{ $g }}</span>
                                @endforeach
                                @if ($totalGrupos > 8)
                                    <span class="tp-badge tp-badge--more">+{{ $totalGrupos - 8 }}</span>
                                @endif
                            </div>

                            <div class="tp-card__actions">
                                <a href="{{ route('tipo_usuario.edit', $tipoUsuario->id) }}"
                                   class="btn btn-xs btn-outline-primary">
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </a>
                                <x-delete-button :action="route('tipo_usuario.destroy', $tipoUsuario->id)" />
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="tp-empty">
                        <i class="fas fa-user-tag"></i>
                        <span>Nenhum perfil encontrado.</span>
                        <a href="{{ route('tipo_usuario.create') }}" class="btn btn-primary btn-sm mt-2">Criar primeiro perfil</a>
                    </div>
                @endforelse
            </div>

            {{-- ── TABELA ── --}}
            <div class="tp-table d-none">
                <table class="table table-hover mb-0 tp-table-table">
                    <thead>
                        <tr>
                            <th>Perfil</th>
                            <th>Modulos</th>
                            <th>Permissoes</th>
                            <th class="text-right" style="width:140px;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tiposUsuario as $tipoUsuario)
                            @php
                                $permCount = $tipoUsuario->permissoes->count();
                                $grupos = $tipoUsuario->permissoes->groupBy(fn($p) => explode('.', $p->name)[0]);
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center" style="gap:.65rem;">
                                        <div class="tp-table__avatar">{{ $iniciais = collect(preg_split('/\s+/', trim($tipoUsuario->name)))->filter()->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'PF' }}</div>
                                        <div>
                                            <div class="font-weight-bold" style="color:#0f172a;">{{ $tipoUsuario->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap" style="gap:.3rem;">
                                        @foreach ($grupos->keys()->take(6) as $g)
                                            <span class="tp-badge">{{ $g }}</span>
                                        @endforeach
                                        @if ($totalGrupos = $grupos->count() > 6)
                                            <span class="tp-badge tp-badge--more">+{{ $grupos->count() - 6 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="badge badge-info">{{ $permCount }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('tipo_usuario.edit', $tipoUsuario->id) }}"
                                       class="btn btn-xs btn-outline-primary mr-1">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>
                                    <x-delete-button :action="route('tipo_usuario.destroy', $tipoUsuario->id)" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-tag fa-2x mb-2 d-block"></i>
                                    Nenhum perfil encontrado.
                                    <a href="{{ route('tipo_usuario.create') }}" class="d-block mt-2">Criar primeiro perfil</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        @if ($tiposUsuario->hasPages())
            <div class="card-footer py-2 d-flex justify-content-end">
                {{ $tiposUsuario->links() }}
            </div>
        @endif
    </div>
@stop

@push('css')
<style>
.tp-filter-sticky { position:sticky; top:0; z-index:10; }

.tp-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:14px; }
.tp-card {
    border:1px solid #e2e8f0; border-radius:14px; overflow:hidden;
    background:#fff; transition:transform .15s,box-shadow .15s,border-color .15s;
}
.tp-card:hover { transform:translateY(-2px); border-color:#cbd5e1; box-shadow:0 8px 24px rgba(15,23,42,.08); }
.tp-card__accent { height:4px; background:linear-gradient(90deg,#4f46e5,#6366f1); }
.tp-card__body { padding:16px; }
.tp-card__head { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
.tp-card__avatar {
    width:40px; height:40px; border-radius:10px;
    background:#eef2ff; color:#4f46e5;
    display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:.85rem; flex-shrink:0;
}
.tp-card__name { font-weight:700; font-size:.9rem; color:#0f172a; }
.tp-card__sub { font-size:.74rem; color:#64748b; margin-top:1px; }
.tp-card__perms { display:flex; flex-wrap:wrap; gap:4px; margin-bottom:12px; }
.tp-badge {
    display:inline-flex; font-size:.68rem; font-weight:600;
    padding:2px 8px; border-radius:6px;
    background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;
}
.tp-badge--more { background:#f8fafc; color:#94a3b8; border-style:dashed; }
.tp-card__actions { display:flex; gap:6px; flex-wrap:wrap; padding-top:10px; border-top:1px solid #f1f5f9; }

.tp-empty {
    grid-column:1/-1; text-align:center; padding:48px 16px;
    color:#94a3b8;
}
.tp-empty i { font-size:2.5rem; display:block; margin-bottom:10px; opacity:.4; }
.tp-empty span { display:block; font-size:.9rem; margin-bottom:6px; }

.tp-table-table { font-size:.85rem; }
.tp-table-table th {
    padding:10px 12px; font-weight:700; font-size:.72rem;
    text-transform:uppercase; letter-spacing:.06em; color:#475569;
    background:#f8fafc; border-bottom:2px solid #e2e8f0;
}
.tp-table-table td { padding:10px 12px; vertical-align:middle; border-bottom:1px solid #f1f5f9; }
.tp-table__avatar {
    width:32px; height:32px; border-radius:8px;
    background:#eef2ff; color:#4f46e5;
    display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:.75rem; flex-shrink:0;
}
</style>
@endpush

@push('js')
<script>
(function () {
    var STORAGE_KEY = 'tp_index_view';

    function toggleView(view) {
        document.querySelectorAll('.tp-view-toggle .btn').forEach(function (b) {
            b.classList.toggle('active', b.dataset.view === view);
        });
        document.querySelectorAll('.tp-cards, .tp-table').forEach(function (el) {
            el.classList.toggle('d-none', !el.classList.contains('tp-' + view));
        });
        try { localStorage.setItem(STORAGE_KEY, view); } catch (e) {}
    }

    document.querySelectorAll('.tp-view-toggle .btn').forEach(function (btn) {
        btn.addEventListener('click', function () { toggleView(this.dataset.view); });
    });

    var saved = null;
    try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) {}
    if (saved === 'table' || saved === 'cards') toggleView(saved);
})();
</script>
@endpush
