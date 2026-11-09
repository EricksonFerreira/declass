@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Requerimentos que as famílias escolhem no portal — cada tipo pode exigir anexo e campos extras"
        :create-url="auth()->user()->hasPermissao('tipo_solicitacao.create') ? route('tipo_solicitacao.create') : null" :create-can="'tipo_solicitacao.create'" />
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nome</th>
                        <th>Escola</th>
                        <th>Anexo</th>
                        <th>Campos extras</th>
                        <th>Solicitações</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tipos as $tipo)
                        <tr>
                            <td>
                                <strong>{{ $tipo->nome }}</strong>
                                @if($tipo->descricao)
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($tipo->descricao, 90) }}</small>
                                @endif
                            </td>
                            <td>{{ $tipo->escola->nome ?? 'Rede toda' }}</td>
                            <td>
                                @if($tipo->exige_anexo)
                                    <span class="badge badge-warning">Obrigatório</span>
                                @else
                                    <span class="badge badge-secondary">Opcional</span>
                                @endif
                            </td>
                            <td>
                                {{ count($tipo->campos ?? []) }}
                                @if($tipo->campos)
                                    <br><small class="text-muted">{{ collect($tipo->campos)->pluck('rotulo')->take(3)->implode(', ') }}{{ count($tipo->campos) > 3 ? '…' : '' }}</small>
                                @endif
                            </td>
                            <td>{{ $tipo->solicitacoes_count }}</td>
                            <td>
                                <span class="badge badge-{{ $tipo->ativo ? 'success' : 'secondary' }}">{{ $tipo->ativo ? 'Ativo' : 'Inativo' }}</span>
                            </td>
                            <td class="text-right text-nowrap">
                                @can('tipo_solicitacao.edit')
                                    <a href="{{ route('tipo_solicitacao.edit', $tipo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('tipo_solicitacao.destroy')
                                    <x-delete-button :action="route('tipo_solicitacao.destroy', $tipo->id)" titulo="Excluir" mensagem="Excluir o tipo &quot;{{ $tipo->nome }}&quot;? Solicitações já feitas mantêm o nome registrado." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-list-ul fa-2x mb-2 d-block"></i>
                                Nenhum tipo cadastrado. Cadastre o primeiro para liberar o formulário do portal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($tipos->hasPages())
        <div class="card-footer">{{ $tipos->links() }}</div>
    @endif
</div>
@stop
