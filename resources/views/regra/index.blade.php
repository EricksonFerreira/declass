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
                                    <span class="badge badge-secondary">
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
                            <td colspan="4" class="p-0">
                                <div class="p-3 bg-light border-bottom">
                                    <p class="mb-3 text-bold text-muted text-uppercase text-sm">
                                        <i class="fas fa-shield-alt mr-1"></i> Permissões atribuídas a {{ $regra->name }}:
                                    </p>
                                    @php
                                        $groupedPerms = $regra->permissoes->groupBy(
                                            fn($p) => $p->grupo_efetivo ?: explode('.', $p->name)[0],
                                        );
                                    @endphp
                                    <div class="row">
                                        @foreach ($groupedPerms as $grupoName => $perms)
                                            <div class="col-md-3 col-sm-6 mb-3">
                                                <div class="card shadow-none border mb-0 h-100">
                                                    <div class="card-header py-1 bg-white border-bottom">
                                                        <span
                                                            class="text-sm text-bold text-uppercase text-primary">{{ $grupoName }}</span>
                                                    </div>
                                                    <ul class="list-group list-group-flush text-sm">
                                                        @foreach ($perms as $p)
                                                            <li class="list-group-item py-1 px-2 border-0"
                                                                title="{{ $p->descricao ?? $p->name }}">
                                                                <i class="fas fa-check-circle text-success mr-1"></i>
                                                                {{ $p->descricao ?? (str_contains($p->name, '.') ? explode('.', $p->name)[1] : $p->name) }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
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
