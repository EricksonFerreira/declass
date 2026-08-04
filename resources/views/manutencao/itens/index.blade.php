@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb ?? []" />
    <x-page-title :title="$titulo" :createUrl="route('manutencao.itens.create')" />
@stop

@section('content')
    <div class="card shadow-sm border-0 manutencao-card">
        <div class="card-header manutencao-card__header d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
            <div>
                <div class="manutencao-card__eyebrow">Itens de manutenção preventiva</div>
                <h3 class="card-title mb-0 text-sm text-bold">REGISTROS <span class="badge badge-success">{{ $items->total() }}</span></h3>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                <a href="{{ route('manutencao.itens.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i>Novo item
                </a>
                {{ $items->links() }}
            </div>
        </div>
        <div class="card-body p-0 table-responsive manutencao-table-wrap">
            <table class="table table-striped table-hover table-bordered table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="font-weight-bold">{{ $item->nome }}</td>
                            <td>{{ $item->descricao ?? '-' }}</td>
                            <td style="width: 180px;">
                                <div class="d-flex flex-wrap" style="gap:.35rem;">
                                    <a href="{{ route('manutencao.itens.edit', $item->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                    <x-delete-button :action="route('manutencao.itens.destroy', $item->id)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Nenhum item cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
<style>
    .manutencao-card { border-radius: 16px; overflow: hidden; }
    .manutencao-card__header {
        background: linear-gradient(135deg, #f7fafc 0%, #eef4ff 55%, #f7fbff 100%);
        border-bottom: 1px solid #e8edf5;
        padding: 1rem 1.25rem;
    }
    .manutencao-card__eyebrow {
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #5b6b7f;
        margin-bottom: .25rem;
    }
    .manutencao-table-wrap .table {
        min-width: 640px;
    }
</style>
@endsection
