@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$usuario->nome ?? 'Usuário'" :subtitle="$prof ? 'Professor · visão docente e conta de acesso' : 'Conta de acesso do tenant'" />
@stop

@push('css')
<style>
    .up-tabs { display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:1rem; }
    .up-tab { display:inline-flex; align-items:center; gap:.4rem; padding:.42rem 1rem; border-radius:999px; border:1.5px solid #e2e8f0; background:#fff; font-size:.78rem; font-weight:700; color:#64748b; cursor:pointer; }
    .up-tab:hover { border-color:#c7d2fe; color:#4f46e5; }
    .up-tab.is-on { background:#4f46e5; border-color:#4f46e5; color:#fff; }
    .up-tab .cnt { background:rgba(100,116,139,.12); border-radius:999px; padding:0 .45rem; font-size:.68rem; }
    .up-tab.is-on .cnt { background:rgba(255,255,255,.22); }
    .up-tab .cnt.warn { background:#fef3c7; color:#b45309; }
    .up-tab.is-on .cnt.warn { background:rgba(255,255,255,.22); color:#fde68a; }

    .up-stat { border:none; border-radius:12px; box-shadow:0 1px 4px rgba(11,18,32,.06); }
    .up-stat .card-body { display:flex; align-items:center; gap:.7rem; padding:.6rem .8rem; }
    .up-ico { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.95rem; color:#fff; flex-shrink:0; }
    .up-num { font-size:1.25rem; font-weight:800; line-height:1; }
    .up-lbl { font-size:.68rem; color:#64748b; }

    .up-turma-card { border:1px solid #e8edf4; border-radius:12px; padding:.75rem .9rem; height:100%; background:#fff; }
    .up-turma-card:hover { border-color:#c7d2fe; }
    .up-chip { display:inline-flex; align-items:center; gap:.3rem; font-size:.7rem; font-weight:600; border-radius:999px; padding:2px 9px; margin:0 4px 4px 0; }
    .up-chip-disc { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .up-chip-papel { background:#f1f5f9; color:#475569; }
    .up-chip-principal { background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe; }
    .up-chip-optativa { background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd; }

    .up-grade { width:100%; border-collapse:separate; border-spacing:3px; table-layout:fixed; }
    .up-grade th { font-size:.68rem; text-transform:uppercase; letter-spacing:.05em; color:#64748b; text-align:center; padding:4px 2px; }
    .up-grade td { vertical-align:top; }
    .up-slot { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:4px 6px; font-size:.7rem; line-height:1.25; min-height:38px; }
    .up-slot .d { font-weight:700; color:#1e40af; }
    .up-slot .t { color:#64748b; font-size:.64rem; }
    .up-empty { background:#f8fafc; border:1px dashed #e2e8f0; border-radius:8px; min-height:38px; }
    .up-ord { font-size:.68rem; font-weight:800; color:#94a3b8; width:34px; text-align:center; vertical-align:middle !important; }

    .up-av { display:flex; align-items:center; gap:.7rem; border:1px solid #e8edf4; border-radius:10px; padding:.6rem .8rem; margin-bottom:.5rem; background:#fff; }
    .up-av:hover { border-color:#c7d2fe; }
    .up-av .prog { height:5px; border-radius:99px; background:#e9edf3; overflow:hidden; width:90px; flex-shrink:0; }
    .up-av .prog > div { height:100%; background:linear-gradient(90deg,#2563eb,#22c55e); }
    .up-av .prog.pend > div { background:linear-gradient(90deg,#f59e0b,#ef4444); }

    .up-pend { display:flex; align-items:center; gap:.7rem; border:1px solid #fde68a; border-left:4px solid #f59e0b; border-radius:10px; padding:.6rem .8rem; margin-bottom:.5rem; background:#fffdf5; }
    .up-pend.tarefa { border-color:#fecaca; border-left-color:#ef4444; background:#fff8f8; }
    .up-pend .n { font-size:1.1rem; font-weight:800; color:#b45309; min-width:34px; text-align:center; }
    .up-pend.tarefa .n { color:#b91c1c; }

    .up-filtro { background:#fff; border:1px solid #e8edf4; border-radius:12px; padding:.55rem .8rem; display:flex; flex-wrap:wrap; align-items:center; gap:.55rem; margin-bottom:.9rem; }
    .up-filtro select { width:auto; max-width:100%; }
    .up-filtro .info { font-size:.72rem; color:#94a3b8; margin-left:auto; }
    @media (max-width:575.98px) {
        .up-filtro select { flex:1; }
        .up-filtro .info { width:100%; margin-left:0; }
    }
</style>
@endpush

@section('content')
@php $tipo = $usuario->tipo_conta; @endphp

@if($prof)
    {{-- Faixa de resumo docente --}}
    <div class="row mb-1">
        <div class="col-md-3 col-6 mb-2">
            <div class="card up-stat mb-0"><div class="card-body">
                <div class="up-ico" style="background:#2563eb;"><i class="fa fa-users"></i></div>
                <div><div class="up-num" data-up-stat="turmas">{{ $prof['stats']['turmas'] }}</div><div class="up-lbl">Turmas</div></div>
            </div></div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card up-stat mb-0"><div class="card-body">
                <div class="up-ico" style="background:#7c3aed;"><i class="fa fa-book"></i></div>
                <div><div class="up-num" data-up-stat="disciplinas">{{ $prof['stats']['disciplinas'] }}</div><div class="up-lbl">Disciplinas lecionadas</div></div>
            </div></div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card up-stat mb-0"><div class="card-body">
                <div class="up-ico" style="background:#0ea5e9;"><i class="fa fa-calendar-alt"></i></div>
                <div><div class="up-num" data-up-stat="aulas">{{ $prof['stats']['aulas_semana'] }}</div><div class="up-lbl">Aulas por semana</div></div>
            </div></div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card up-stat mb-0"><div class="card-body">
                <div class="up-ico" style="background:#f59e0b;"><i class="fa fa-exclamation-circle"></i></div>
                <div><div class="up-num" data-up-stat="pendencias">{{ $prof['stats']['pendencias'] }}</div><div class="up-lbl">Pendências</div></div>
            </div></div>
        </div>
    </div>

    <div class="up-filtro">
        <span class="small font-weight-bold text-muted"><i class="fa fa-filter mr-1"></i>Ver por:</span>
        <select id="profFiltroEsc" class="form-control form-control-sm">
            <option value="">Todas as escolas</option>
            @foreach($prof['escolas_filtro'] as $eid => $enome)
                <option value="{{ $eid }}">{{ $enome }}</option>
            @endforeach
        </select>
        @if(count($prof['periodos_filtro']))
            <select id="profFiltroPer" class="form-control form-control-sm">
                <option value="">Período geral</option>
                @foreach($prof['periodos_filtro'] as $p)
                    <option value="{{ $p['id'] }}">{{ $p['rotulo'] }}</option>
                @endforeach
            </select>
        @endif
        <span class="info" id="profFiltroInfo"></span>
    </div>

    <div class="up-tabs" role="tablist">
        <button type="button" class="up-tab is-on" data-pane="up-perfil"><i class="fa fa-user"></i> Perfil</button>
        <button type="button" class="up-tab" data-pane="up-turmas"><i class="fa fa-users"></i> Turmas <span class="cnt">{{ count($prof['turmas']) }}</span></button>
        <button type="button" class="up-tab" data-pane="up-disciplinas"><i class="fa fa-book"></i> Disciplinas <span class="cnt">{{ count($prof['habilitacoes']) }}</span></button>
        <button type="button" class="up-tab" data-pane="up-horario"><i class="fa fa-calendar-alt"></i> Horário</button>
        <button type="button" class="up-tab" data-pane="up-avaliacoes"><i class="fa fa-star"></i> Avaliações <span class="cnt">{{ count($prof['avaliacoes']) }}</span></button>
        <button type="button" class="up-tab" data-pane="up-pendencias"><i class="fa fa-exclamation-circle"></i> Pendências <span class="cnt {{ $prof['stats']['pendencias'] ? 'warn' : '' }}">{{ $prof['stats']['pendencias'] }}</span></button>
    </div>
@endif

{{-- ABA: Perfil (dados da conta) --}}
<div class="up-pane" id="up-perfil">
<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header font-weight-bold">Dados da Conta</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nome</dt><dd class="col-sm-8">{{ $usuario->nome ?? '—' }}</dd>
                    <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8">{{ $usuario->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Login alternativo</dt><dd class="col-sm-8">{{ $usuario->usuario ?? '—' }}</dd>
                    <dt class="col-sm-4">CPF / documento</dt><dd class="col-sm-8">{{ $usuario->numero_identificacao ?? '—' }}</dd>
                    <dt class="col-sm-4">Nascimento</dt><dd class="col-sm-8">{{ optional($usuario->data_nascimento)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-4">Telefone</dt><dd class="col-sm-8">{{ $usuario->telefone ?? '—' }}</dd>
                    <dt class="col-sm-4">Celular</dt><dd class="col-sm-8">{{ $usuario->celular ?? '—' }}</dd>
                    <dt class="col-sm-4">Cidade</dt><dd class="col-sm-8">{{ $usuario->cidade ?? '—' }}</dd>
                    <dt class="col-sm-4">Cargo / função</dt><dd class="col-sm-8">{{ $usuario->cargo ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold d-flex align-items-center">
                Acesso e vínculos
                <div class="ml-auto d-flex" style="gap:.35rem;">
                    @can('usuario.alterarSenha')
                        <button type="button" class="btn btn-outline-secondary btn-xs" id="btnSenhaAcesso"
                                data-url="{{ route('usuario.salvarNovaSenha', $usuario->id) }}"
                                data-csrf="{{ csrf_token() }}" data-nome="{{ $usuario->nome }}">
                            <i class="fa fa-key mr-1"></i>Senha
                        </button>
                    @endcan
                    @can('usuario.edit')
                        <a href="{{ route('usuario.edit', $usuario->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-edit mr-1"></i>Editar</a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Tipo de conta</dt>
                    <dd class="col-sm-8">
                        @if($tipo === 'aluno')
                            <span class="badge badge-primary">Aluno</span>
                        @elseif($tipo === 'responsavel')
                            <span class="badge badge-info">Responsável</span>
                        @else
                            <span class="badge badge-secondary">Colaborador</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4">Perfil de acesso</dt><dd class="col-sm-8">{{ $usuario->regra->name ?? '—' }}</dd>
                    @if($usuario->aluno)
                        <dt class="col-sm-4">Aluno vinculado</dt>
                        <dd class="col-sm-8"><a href="{{ route('aluno.show', $usuario->aluno->id) }}">{{ $usuario->aluno->nome }}</a></dd>
                    @elseif($usuario->responsavel)
                        <dt class="col-sm-4">Responsável vinculado</dt>
                        <dd class="col-sm-8"><a href="{{ route('responsavel.show', $usuario->responsavel->id) }}">{{ $usuario->responsavel->nome }}</a></dd>
                    @endif
                    <dt class="col-sm-4">Escolas / unidades</dt>
                    <dd class="col-sm-8">{{ $usuario->escolas->pluck('nome')->join(', ') ?: '—' }}</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($usuario->ativo)
                            <span class="badge badge-success">Ativo</span>
                        @else
                            <span class="badge badge-secondary">Inativo</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4">Troca de senha</dt>
                    <dd class="col-sm-8">{{ $usuario->forcar_troca_senha ? 'Obrigatória no próximo login' : 'Não pendente' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header font-weight-bold">Foto de perfil</div>
            <div class="card-body text-center">
                <img src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nome }}" class="img-circle" style="width:128px;height:128px;object-fit:cover;">
            </div>
        </div>
    </div>
</div>
</div>

@if($prof)
{{-- ABA: Turmas atribuídas --}}
<div class="up-pane d-none" id="up-turmas">
    <div class="card">
        <div class="card-header font-weight-bold"><i class="fa fa-users text-primary mr-1"></i> Turmas onde leciona</div>
        <div class="card-body">
            <div class="row">
                @forelse($prof['turmas'] as $t)
                    <div class="col-md-6 col-xl-4 mb-3 prof-item" data-grupo="turma" data-esc="{{ $t['esc'] }}" data-ano="{{ $t['ano_letivo'] }}">
                        <a href="{{ route('turma.show', $t['id']) }}" class="text-decoration-none d-block h-100">
                            <div class="up-turma-card">
                                <div class="font-weight-bold text-dark">{{ $t['nome'] }}</div>
                                <div class="small text-muted mb-2">{{ $t['escola'] }}{{ $t['ano'] ? ' · '.$t['ano'] : '' }}</div>
                                @foreach($t['disciplinas'] as $d)
                                    <div class="d-flex align-items-center mb-1" style="gap:.4rem;" data-disc="{{ $d['nome'] }}">
                                        <span class="up-chip up-chip-disc"><i class="fa fa-book"></i> {{ $d['nome'] }}</span>
                                        <span class="badge badge-{{ $d['papel_cor'] }}">{{ $d['papel'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4"><i class="fa fa-users fa-2x mb-2 d-block"></i>Nenhuma turma atribuída ainda.</div>
                @endforelse
            </div>
            <div class="prof-vazio text-center text-muted py-4 d-none"><i class="fa fa-filter fa-2x mb-2 d-block"></i>Nenhuma turma neste filtro.</div>
        </div>
    </div>
</div>

{{-- ABA: Disciplinas habilitadas --}}
<div class="up-pane d-none" id="up-disciplinas">
    <div class="card">
        <div class="card-header font-weight-bold"><i class="fa fa-book text-primary mr-1"></i> Disciplinas que leciona</div>
        <div class="card-body">
            @forelse($prof['habilitacoes'] as $h)
                <span class="up-chip {{ $h['tipo'] === 'optativa' ? 'up-chip-optativa' : 'up-chip-principal' }}" style="font-size:.8rem;padding:4px 12px;">
                    <i class="fa {{ $h['tipo'] === 'optativa' ? 'fa-star-half-alt' : 'fa-star' }}"></i> {{ $h['nome'] }}
                    <span style="font-weight:400;opacity:.75;">· {{ $h['tipo'] === 'optativa' ? 'optativa' : 'principal' }}</span>
                </span>
            @empty
                <div class="text-center text-muted py-4">
                    <i class="fa fa-book-open fa-2x mb-2 d-block"></i>
                    Nenhuma disciplina habilitada — configure em
                    <a href="{{ route('usuario.edit', $usuario->id) }}">Editar usuário</a>.
                </div>
            @endforelse
            @if(count($prof['habilitacoes']))
                <div class="mt-3 small text-muted">
                    <i class="fa fa-star" style="color:#6d28d9;"></i> <strong>Principal</strong>: formação/contrato.
                    <i class="fa fa-star-half-alt ml-2" style="color:#0369a1;"></i> <strong>Optativa</strong>: pode assumir se preciso.
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ABA: Quadro de horário semanal --}}
<div class="up-pane d-none" id="up-horario">
    <div class="card">
        <div class="card-header font-weight-bold"><i class="fa fa-calendar-alt text-primary mr-1"></i> Quadro de horário semanal</div>
        <div class="card-body p-2">
            @if($prof['max_ordem'] > 0)
                <div class="table-responsive">
                    <table class="up-grade">
                        <thead>
                            <tr><th></th>@foreach($prof['dias_semana'] as $d)<th>{{ $d }}</th>@endforeach</tr>
                        </thead>
                        <tbody>
                            @for($ordem = 1; $ordem <= $prof['max_ordem']; $ordem++)
                                <tr>
                                    <td class="up-ord">{{ $ordem }}ª</td>
                                    @foreach(array_keys($prof['dias_semana']) as $dia)
                                        <td>
                                            @if(isset($prof['grade'][$dia][$ordem]))
                                                @php $slot = $prof['grade'][$dia][$ordem]; @endphp
                                                <div class="up-slot prof-item" data-grupo="slot" data-esc="{{ $slot['esc'] }}" data-ano="{{ $slot['ano_letivo'] }}" title="{{ $slot['disciplina'] }} · {{ $slot['turma'] }}{{ $slot['sala'] ? ' · sala '.$slot['sala'] : '' }}">
                                                    <div class="d">{{ $slot['disciplina'] }}</div>
                                                    <div class="t">{{ $slot['turma'] }}{{ $slot['sala'] ? ' · '.$slot['sala'] : '' }}</div>
                                                </div>
                                            @else
                                                <div class="up-empty"></div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-4"><i class="fa fa-calendar-times fa-2x mb-2 d-block"></i>Nenhum horário cadastrado no quadro das turmas.</div>
            @endif
            <div class="prof-vazio text-center text-muted py-4 d-none"><i class="fa fa-filter fa-2x mb-2 d-block"></i>Nenhuma aula neste filtro.</div>
        </div>
    </div>
</div>

{{-- ABA: Avaliações criadas --}}
<div class="up-pane d-none" id="up-avaliacoes">
    <div class="card">
        <div class="card-header font-weight-bold"><i class="fa fa-star text-primary mr-1"></i> Avaliações criadas</div>
        <div class="card-body">
            @forelse($prof['avaliacoes'] as $av)
                <div class="up-av prof-item" data-grupo="av" data-esc="{{ $av['esc'] }}" data-per="{{ $av['per'] }}">
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="font-weight-bold text-truncate">{{ $av['nome'] }}</div>
                        <div class="small text-muted">{{ $av['tipo'] }} · {{ $av['turma'] }} · {{ $av['disciplina'] }} · {{ $av['data'] }}</div>
                    </div>
                    <div class="text-center flex-shrink-0">
                        <div class="prog {{ $av['pendente'] ? 'pend' : '' }}"><div style="width:{{ $av['total'] ? min(100, round($av['lancadas'] / $av['total'] * 100)) : 0 }}%"></div></div>
                        <div class="small text-muted mt-1">{{ $av['lancadas'] }}/{{ $av['total'] }} notas</div>
                    </div>
                    <a href="{{ $av['url'] }}" class="btn btn-xs {{ $av['pendente'] ? 'btn-warning' : 'btn-outline-primary' }} flex-shrink-0">
                        <i class="fa fa-edit mr-1"></i>{{ $av['pendente'] ? 'Lançar' : 'Ver' }}
                    </a>
                </div>
            @empty
                <div class="text-center text-muted py-4"><i class="fa fa-star-half-alt fa-2x mb-2 d-block"></i>Nenhuma avaliação criada ainda.</div>
            @endforelse
            <div class="prof-vazio text-center text-muted py-4 d-none"><i class="fa fa-filter fa-2x mb-2 d-block"></i>Nenhuma avaliação neste filtro.</div>
        </div>
    </div>
</div>

{{-- ABA: Pendências (notas faltando + entregas a corrigir) --}}
<div class="up-pane d-none" id="up-pendencias">
    <div class="card">
        <div class="card-header font-weight-bold"><i class="fa fa-exclamation-circle text-warning mr-1"></i> O que falta fazer</div>
        <div class="card-body">
            <div id="pendSecAv">
            @if(count($prof['avaliacoes_pendentes']))
                <div class="small font-weight-bold text-uppercase text-muted mb-2"><i class="fa fa-star mr-1"></i>Notas a lançar</div>
                @foreach($prof['avaliacoes_pendentes'] as $av)
                    <a href="{{ $av['url'] }}" class="text-decoration-none prof-item" data-grupo="pend" data-esc="{{ $av['esc'] }}" data-per="{{ $av['per'] }}" data-n="{{ $av['total'] - $av['lancadas'] }}">
                        <div class="up-pend">
                            <div class="n">{{ $av['total'] - $av['lancadas'] }}</div>
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="font-weight-bold text-dark text-truncate">{{ $av['nome'] }}</div>
                                <div class="small text-muted">{{ $av['turma'] }} · {{ $av['disciplina'] }} · faltam notas</div>
                            </div>
                            <i class="fa fa-chevron-right text-muted"></i>
                        </div>
                    </a>
                @endforeach
            @endif
            </div>

            <div id="pendSecTf">
            @if(count($prof['tarefas_pendentes']))
                <div class="small font-weight-bold text-uppercase text-muted mb-2 mt-3"><i class="fa fa-tasks mr-1"></i>Entregas a corrigir</div>
                @foreach($prof['tarefas_pendentes'] as $tf)
                    <a href="{{ $tf['url'] }}" class="text-decoration-none prof-item" data-grupo="pend" data-esc="{{ $tf['esc'] }}" data-per="" data-n="{{ $tf['pendentes'] }}">
                        <div class="up-pend tarefa">
                            <div class="n">{{ $tf['pendentes'] }}</div>
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="font-weight-bold text-dark text-truncate">{{ $tf['titulo'] }}</div>
                                <div class="small text-muted">{{ $tf['turma'] }} · entregue(s) aguardando nota</div>
                            </div>
                            <i class="fa fa-chevron-right text-muted"></i>
                        </div>
                    </a>
                @endforeach
            @endif
            </div>

            <div class="prof-vazio text-center text-muted py-4 d-none"><i class="fa fa-filter fa-2x mb-2 d-block"></i>Nenhuma pendência neste filtro.</div>
            @if(!count($prof['avaliacoes_pendentes']) && !count($prof['tarefas_pendentes']))
                <div class="prof-tudodia text-center text-success py-4"><i class="fa fa-check-circle fa-2x mb-2 d-block"></i>Tudo em dia — nenhuma pendência.</div>
            @endif
        </div>
    </div>
</div>
@endif

@if($permissoesGrupos)
<div class="card card-outline card-warning">
    <div class="card-header">
        <span class="font-weight-bold"><i class="fa fa-key mr-1"></i> Permissões de acesso</span>
        <span class="badge badge-danger ml-2">somente superadmin</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('usuario.permissoes', $usuario->id) }}">
            @csrf
            @method('PUT')
            <div class="alert alert-light border small mb-3">
                <i class="fa fa-info-circle mr-1"></i>
                <strong>Herdar do perfil</strong> mantém o padrão do perfil <em>{{ $usuario->regra->nome ?? '—' }}</em>;
                <strong class="text-success">Permitir</strong> concede a tela/ação mesmo fora do perfil;
                <strong class="text-danger">Negar</strong> remove mesmo que o perfil conceda.
            </div>
            <div class="row">
                @foreach($permissoesGrupos as $modulo => $nomes)
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-2 h-100">
                            <div class="font-weight-bold small text-uppercase text-muted mb-1">
                                {{ str_replace('_', ' ', $modulo) }}
                            </div>
                            @foreach($nomes as $nome)
                                @php
                                    $acao = explode('.', $nome, 2)[1] ?? $nome;
                                    $estado = in_array($nome, $permEstado['permitir']) ? 'permitir'
                                        : (in_array($nome, $permEstado['negar']) ? 'negar' : 'herdar');
                                    $noPerfil = $permPerfil->contains($nome);
                                @endphp
                                <div class="d-flex align-items-center mb-1" style="gap:.4rem;">
                                    <span class="small flex-grow-1 {{ $noPerfil ? '' : 'text-muted' }}" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $nome }}">
                                        {{ $acao }}
                                        @if($noPerfil)
                                            <i class="fa fa-user-check text-muted" title="vem do perfil" style="font-size:.65rem;"></i>
                                        @endif
                                    </span>
                                    <select name="perm[{{ $nome }}]" class="form-control form-control-sm" style="width:6.2rem;font-size:.72rem;">
                                        <option value="herdar" {{ $estado === 'herdar' ? 'selected' : '' }}>Herdar</option>
                                        <option value="permitir" {{ $estado === 'permitir' ? 'selected' : '' }}>Permitir</option>
                                        <option value="negar" {{ $estado === 'negar' ? 'selected' : '' }}>Negar</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-warning btn-sm"><i class="fa fa-save mr-1"></i> Salvar permissões diretas</button>
        </form>
    </div>
</div>
@endif

@if($prof)
<script>
document.querySelectorAll('.up-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.up-tab').forEach(function (t) { t.classList.remove('is-on'); });
        tab.classList.add('is-on');
        document.querySelectorAll('.up-pane').forEach(function (p) { p.classList.add('d-none'); });
        document.getElementById(tab.dataset.pane).classList.remove('d-none');
    });
});

// Filtro do painel: escola + periodo letivo ("geral" = tudo).
(function () {
    const PERIODOS = @json(collect($prof['periodos_filtro'])->keyBy('id')->map(fn ($p) => ['ano' => $p['ano_letivo'], 'esc' => $p['esc']])->all());
    const selEsc = document.getElementById('profFiltroEsc');
    const selPer = document.getElementById('profFiltroPer');
    const info = document.getElementById('profFiltroInfo');

    function aplicar() {
        const esc = selEsc ? selEsc.value : '';
        const perId = selPer ? selPer.value : '';
        const per = perId && PERIODOS[perId] ? PERIODOS[perId] : null;

        const vis = { turma: 0, slot: 0, av: 0, pend: 0 };
        const discs = new Set();
        let pendTotal = 0;

        document.querySelectorAll('.prof-item').forEach(function (el) {
            const g = el.dataset.grupo;
            let ok = true;
            if (esc && String(el.dataset.esc) !== esc) { ok = false; }
            if (ok && per) {
                if (g === 'av') {
                    ok = String(el.dataset.per) === String(perId);
                } else if (g === 'pend') {
                    // Tarefa nao tem periodo: so avaliacao filtra pelo id.
                    ok = !el.dataset.per || String(el.dataset.per) === String(perId);
                } else {
                    // Turma/horario: pertence ao ano letivo do periodo.
                    ok = String(el.dataset.ano) === String(per.ano);
                }
            }
            el.classList.toggle('d-none', !ok);
            if (ok) {
                if (vis[g] !== undefined) { vis[g]++; }
                if (g === 'turma') {
                    el.querySelectorAll('[data-disc]').forEach(function (d) { discs.add(d.dataset.disc); });
                }
                if (g === 'pend') { pendTotal += parseInt(el.dataset.n || '0', 10); }
            }
        });

        // Stats no topo
        const set = function (k, v) {
            const el = document.querySelector('[data-up-stat="' + k + '"]');
            if (el) { el.textContent = v; }
        };
        set('turmas', vis.turma);
        set('disciplinas', discs.size);
        set('aulas', vis.slot);
        set('pendencias', pendTotal);

        // Contadores nas abas
        const cnt = function (pane, v) {
            const t = document.querySelector('.up-tab[data-pane="' + pane + '"] .cnt');
            if (t) { t.textContent = v; }
        };
        cnt('up-turmas', vis.turma);
        cnt('up-avaliacoes', vis.av);
        cnt('up-pendencias', pendTotal);

        // Estados vazios por aba
        [['up-turmas', vis.turma], ['up-horario', vis.slot], ['up-avaliacoes', vis.av]].forEach(function (par) {
            const v = document.querySelector('#' + par[0] + ' .prof-vazio');
            if (v) { v.classList.toggle('d-none', par[1] > 0); }
        });

        // Pendencias: secoes escondem sozinhas + "tudo em dia" vs "nada no filtro"
        const secAv = document.getElementById('pendSecAv');
        const secTf = document.getElementById('pendSecTf');
        let pendRows = 0;
        [secAv, secTf].forEach(function (sec) {
            if (!sec) { return; }
            const n = sec.querySelectorAll('.prof-item:not(.d-none)').length;
            sec.classList.toggle('d-none', n === 0);
            pendRows += n;
        });
        const pv = document.querySelector('#up-pendencias .prof-vazio');
        const td = document.querySelector('#up-pendencias .prof-tudodia');
        if (pv) { pv.classList.toggle('d-none', !(pendRows === 0 && !td)); }
        if (td) { td.classList.toggle('d-none', pendRows > 0); }

        info.textContent = (esc || perId)
            ? 'Filtro ativo — ' + (esc ? 'escola' : '') + (esc && perId ? ' + ' : '') + (perId ? 'período' : '')
            : 'Visão geral de todas as escolas e períodos';
    }

    if (selEsc) { selEsc.addEventListener('change', aplicar); }
    if (selPer) { selPer.addEventListener('change', aplicar); }
    aplicar();
})();
</script>
@endif

<script>
// Senha: Swal escolhe entre resetar para Senh@123 (troca no 1o login)
// ou enviar o link de redefinicao por e-mail.
(function () {
    const btn = document.getElementById('btnSenhaAcesso');
    if (!btn || typeof Swal === 'undefined') { return; }

    btn.addEventListener('click', function () {
        Swal.fire({
            title: 'Senha de ' + (btn.dataset.nome || 'usuário'),
            html: 'Como o usuário deve receber o novo acesso?<br>' +
                '<small class="text-muted"><b>Senh@123</b> obriga a criar senha nova no primeiro login; ' +
                'o <b>e-mail</b> envia um link para redefinir.</small>',
            icon: 'question',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Definir Senh@123',
            denyButtonText: 'Enviar por e-mail',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4f46e5',
            denyButtonColor: '#0ea5e9',
            reverseButtons: true,
        }).then(function (r) {
            if (r.isConfirmed) { enviar('padrao'); }
            else if (r.isDenied) { enviar('email'); }
        });
    });

    function enviar(modo) {
        Swal.fire({ title: 'Aguarde...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
        fetch(btn.dataset.url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': btn.dataset.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ modo: modo }),
        })
            .then(function (resp) {
                return resp.json().then(function (j) { return { ok: resp.ok, msg: j.message || 'Operação concluída.' }; });
            })
            .then(function (r) {
                Swal.fire({ icon: r.ok ? 'success' : 'error', title: r.ok ? 'Pronto' : 'Ops', text: r.msg });
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Ops', text: 'Falha de comunicação. Tente novamente.' });
            });
    }
})();
</script>
@stop
