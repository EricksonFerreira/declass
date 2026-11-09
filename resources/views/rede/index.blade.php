@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Todas as empresas da plataforma em um so painel" />
@stop

@section('content')
<form method="GET" action="{{ route('rede.index') }}" class="mb-3">
    <div class="row justify-content-end">
        <div class="col-md-2 col-5">
            <select name="ano" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Todos os anos</option>
                @foreach($anos as $anoOpcao)
                    <option value="{{ $anoOpcao }}" @selected($ano === $anoOpcao)>{{ $anoOpcao }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-lg col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-building"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Empresas ativas</span>
                <span class="info-box-number">{{ $dados['kpis']['empresas_ativas'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-school"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Escolas</span>
                <span class="info-box-number">{{ $dados['kpis']['escolas'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-purple"><i class="fas fa-user-graduate"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Alunos</span>
                <span class="info-box-number">{{ $dados['kpis']['alunos'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Matrículas{{ $ano ? " ($ano)" : '' }}</span>
                <span class="info-box-number">{{ $dados['kpis']['matriculas'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-chalkboard"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Turmas{{ $ano ? " ($ano)" : '' }}</span>
                <span class="info-box-number">{{ $dados['kpis']['turmas'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-teal"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Professores</span>
                <span class="info-box-number">{{ $dados['kpis']['professores'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-secondary"><i class="fas fa-user-cog"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Usuários</span>
                <span class="info-box-number">{{ $dados['kpis']['usuarios'] }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-network-wired mr-1"></i> Empresas da rede <span class="badge badge-primary ml-1">{{ $dados['linhas']->count() }}</span></h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th class="text-center">Escolas</th>
                    <th class="text-center">Alunos</th>
                    <th class="text-center">Matrículas</th>
                    <th class="text-center">Turmas</th>
                    <th class="text-center">Professores</th>
                    <th class="text-center">Usuários</th>
                    <th>Domínio</th>
                    <th class="text-center">Site</th>
                    <th class="text-center">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($dados['linhas'] as $linha)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($linha->empresa->logo_url)
                                    <img src="{{ $linha->empresa->logo_url }}" alt="" class="mr-2" style="width:30px;height:30px;object-fit:contain">
                                @else
                                    <i class="fas fa-building text-muted mr-2"></i>
                                @endif
                                <div>
                                    <strong>{{ $linha->empresa->nome }}</strong><br>
                                    <small class="text-muted">{{ $linha->empresa->slug }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">{{ $linha->escolas }}</td>
                        <td class="text-center">{{ $linha->alunos }}</td>
                        <td class="text-center">{{ $linha->matriculas }}</td>
                        <td class="text-center">{{ $linha->turmas }}</td>
                        <td class="text-center">{{ $linha->professores }}</td>
                        <td class="text-center">{{ $linha->usuarios }}</td>
                        <td>
                            @if($linha->empresa->dominio)
                                <small>{{ $linha->empresa->dominio }}</small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($linha->empresa->site_ativo === 'S')
                                <span class="badge badge-success">No ar</span>
                            @else
                                <span class="badge badge-secondary">Off</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($linha->empresa->ativo === 'S')
                                <span class="badge badge-primary">Ativa</span>
                            @else
                                <span class="badge badge-danger">Inativa</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('empresa.show', $linha->empresa) }}" class="btn btn-xs btn-default" title="Ver empresa">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('empresa.edit', $linha->empresa) }}" class="btn btn-xs btn-default" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">Nenhuma empresa cadastrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
