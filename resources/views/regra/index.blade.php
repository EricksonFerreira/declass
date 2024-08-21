@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('regra.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $regras->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('regra.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $regras->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Nome da Política de Segurança</th>
                        <th>Quantidade de Permissões</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($regras as $regra)
                        <tr>
                            <td>{{ $regra->name }}</td>
                            <td>{{ $regra->guard_name }}</td>
                            <td>
                                @if (count($regra->permissoes) > 0)
                                    <a data-toggle="collapse" href="#collapsePermissoes{{ $regra->id }}" role="button"
                                        data-toggle="tooltip" data-placement="top" title="Ver as permissões"
                                        aria-expanded="false" aria-controls="collapsePermissoes{{ $regra->id }}">
                                        <span class="badge badge-info">
                                            {{ count($regra->permissoes) }} Permissões <i class="fa fa-eye"></i>
                                        </span>
                                    </a>
                                @else
                                    <span class="badge badge-secondary" >
                                        {{ count($regra->permissoes) }} Permissões
                                    </span>
                                @endif
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('regra.edit', $regra->id) }}" class="btn btn-xs btn-warning"><i
                                        class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('regra.destroy', $regra->id)" />
                            </td>
                        </tr>
                        <tr class="collapse" id="collapsePermissoes{{ $regra->id }}">
                            <td colspan="2">
                                    <p class="mb-1 pl-1">
                                        <strong>PERMISSÕES:</strong>
                                    </p>
                                    <ul class="list-group d-flex align-self-start">
                                        @foreach ($regra->permissoes as $permissao)
                                            <li class="list-group-item py-0">{{ $permissao->name }}</li>
                                        @endforeach
                                    </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('[data-toggle="collapse"]').on('click', function() {
                var icon = $(this).find('.fa-eye, .fa-eye-slash');
                if (icon.length) {
                    icon.toggleClass('fa-eye fa-eye-slash');
                }
            });
        });
    </script>
@stop
