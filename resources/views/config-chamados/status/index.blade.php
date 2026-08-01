@extends('adminlte::page')
@section('title', 'Status de Chamados')
@section('content_header')
    <x-breadcrumb :breadcrumbs="[
        ['name' => 'Config. Chamados', 'url' => route('config_chamados.status.index')],
        ['name' => 'Status'],
    ]" />
    <x-page-title title="Status de Chamados" :createUrl="route('config_chamados.status.create')" />
@stop
@section('content')
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>Cor</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statuses as $status)
                        <tr>
                            <td>{{ $status->nome }}</td>
                            <td>{{ $status->slug }}</td>
                            <td>{{ $status->cor }}</td>
                            <td><span class="badge badge-{{ $status->ativo ? 'success' : 'danger' }}">{{ $status->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                            <td>
                                <a class="btn btn-xs btn-warning" href="{{ route('config_chamados.status.edit', $status) }}">Editar</a>
                                <x-delete-button :action="route('config_chamados.status.destroy', $status)" />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Nenhum status cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $statuses->links() }}</div>
    </div>
@stop
