@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('usuario.create')" :createCan="'usuario.create'" />
@stop

@section('content')

    @if (!empty($filters))
        <x-filter
            :filters="$filters"
            :additionalFilters="$additionalFilters"
            :itensFiltered="$itensFiltered"
            :nomeFiltro="$nomeFiltro"
        />
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header px-3 py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <h3 class="card-title mb-0 font-weight-bold">
                    <i class="fas fa-users mr-1 text-primary"></i>
                    REGISTROS
                    <span class="badge badge-success ml-2">{{ $usuarios->total() }}</span>
                </h3>

                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <form action="{{ route('usuario.setPerPage') }}" method="POST" class="mb-0">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>

                    {{ $usuarios->links() }}
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0 tabela-usuarios">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3" style="min-width: 220px;">Usuário</th>
                            <th style="min-width: 220px;">Contato</th>
                            <th style="width: 140px;">Regra</th>
                            <th>Cidade</th>
                            <th >Cadastro</th>
                            <th class="text-center" style="min-width: 300px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                            @php
                                $canMetodoPagamento = auth()->user()->hasPermissao('usuario.metodoPagamento');
                                $canEditar = auth()->user()->hasPermissao('usuario.edit');
                                $canAlterarSenha = auth()->user()->hasPermissao('usuario.alterarSenha');
                                $canExcluir = auth()->user()->hasPermissao('usuario.destroy');
                                $temAlgumaAcao = $canMetodoPagamento || $canEditar || $canAlterarSenha || $canExcluir;
                            @endphp

                            <tr>
                                <td class="pl-3 align-middle">
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark">{{ $usuario->nome }}</span>
                                        <small class="text-muted">ID: #{{ $usuario->id }}</small>
                                    </div>
                                </td>

                                <td class="align-middle">
                                    <div class="d-flex flex-column">
                                        <span>{{ $usuario->email ?: '—' }}</span>
                                        <small class="text-muted">
                                            <i class="fas fa-phone-alt mr-1"></i>{{ $usuario->celular ?: 'Sem celular' }}
                                        </small>
                                    </div>
                                </td>

                                <td class="align-middle">
                                    @if ($usuario->tipoUsuario)
                                        <span class="badge badge-info px-2 py-1">{{ $usuario->tipoUsuario->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    {{ $usuario->cidade ?: '—' }}
                                </td>

                                <td class="align-middle">
                                    <span class="text-muted">{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : '—' }}</span>
                                </td>

                                <td class="align-middle text-center">
                                    @if ($temAlgumaAcao)
                                        <div class="acoes-usuario">
                                            @if ($canMetodoPagamento)
                                                <a class="btn btn-xs btn-success"
                                                    href="{{ route('usuario.metodoPagamento', $usuario->id) }}">
                                                    <i class="fas fa-credit-card mr-1"></i> Pagamento
                                                </a>
                                            @endif

                                            @if ($canEditar)
                                                <a class="btn btn-xs btn-warning"
                                                    href="{{ route('usuario.edit', $usuario->id) }}">
                                                    <i class="fas fa-edit mr-1"></i> Editar
                                                </a>
                                            @endif

                                            @if ($canAlterarSenha)
                                                <form action="{{ route('usuario.salvarNovaSenha', $usuario->id) }}"
                                                    method="POST"
                                                    class="d-inline js-reset-senha-form"
                                                    id="resetSenhaForm-{{ $usuario->id }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="resetar_para_acesso" value="1">
                                                    <input type="hidden" name="password" value="Senh@123">
                                                    <input type="hidden" name="confirmacao_senha" value="Senh@123">
                                                    <button type="button"
                                                        class="btn btn-xs btn-info js-reset-senha"
                                                        data-user-name="{{ $usuario->nome }}"
                                                        data-form-id="resetSenhaForm-{{ $usuario->id }}">
                                                        <i class="fas fa-key mr-1"></i> Resetar senha
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($canExcluir)
                                                <form action="{{ route('usuario.destroy', $usuario->id) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger">
                                                        <i class="fas fa-trash mr-1"></i> Excluir
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    Nenhum usuário encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($usuarios->hasPages())
            <div class="card-footer py-2 d-flex justify-content-end">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

@stop

@section('css')
    <style>
        .tabela-usuarios thead th {
            vertical-align: middle;
            white-space: nowrap;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .tabela-usuarios tbody td {
            vertical-align: middle;
        }

        .tabela-usuarios tbody tr:hover {
            background-color: #f8fbff;
        }

        .acoes-usuario {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px;
        }

        .acoes-usuario .btn {
            min-width: 95px;
        }

        .tabela-usuarios .badge {
            font-size: 11px;
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('js/sweetAlert.min.js') }}"></script>
    <script>
        $(function () {
            const senhaPadrao = 'Senh@123';

            function escaparHtml(texto) {
                return String(texto ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function mostrarSucesso(mensagem) {
                const options = {
                    icon: 'success',
                    title: 'Senha atualizada',
                    html: `
                        <div style="text-align:left">
                            <p class="mb-2">${escaparHtml(mensagem)}</p>
                            <p class="mb-0">Oriente o usuário a entrar com a senha padrão e trocar no primeiro acesso.</p>
                        </div>
                    `,
                    confirmButtonText: 'Entendi',
                    confirmButtonColor: '#16a34a',
                };

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire(options);
                    return;
                }

                if (typeof window.alertConfirmPequena === 'function') {
                    window.alertConfirmPequena(options.title, mensagem, 'success');
                    return;
                }

                window.alert(options.title + '\n\n' + mensagem);
            }

            function confirmarReset($form, userName) {
                const texto = `
                    <div style="text-align:left">
                        <p class="mb-2">Tem certeza que deseja resetar a senha de <strong>${escaparHtml(userName)}</strong>?</p>
                        <p class="mb-2">A senha será definida como <strong>${senhaPadrao}</strong>.</p>
                        <p class="mb-0">No próximo acesso, o colaborador será direcionado para criar uma nova senha.</p>
                    </div>
                `;

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Resetar senha?',
                        html: texto,
                        showCancelButton: true,
                        confirmButtonText: 'Sim, resetar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#6b7280',
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $form.trigger('submit');
                        }
                    });
                    return;
                }

                if (window.confirm('Tem certeza que deseja resetar a senha de ' + userName + ' para ' + senhaPadrao + '?')) {
                    $form.trigger('submit');
                }
            }

            $(document).on('click', '.js-reset-senha', function () {
                const $btn = $(this);
                const userName = String($btn.data('user-name') || 'este usuário');
                const formId = String($btn.data('form-id') || '');
                const $form = $('#' + formId);

                if (!$form.length) {
                    return;
                }

                confirmarReset($form, userName);
            });

            const resetMessage = @json(session('usuarioResetSenhaMensagem'));
            if (resetMessage) {
                mostrarSucesso(resetMessage);
                return;
            }

            const successMessage = @json(session('success'));
            if (successMessage) {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: successMessage,
                        confirmButtonColor: '#16a34a',
                    });
                } else if (typeof window.alertConfirmPequena === 'function') {
                    window.alertConfirmPequena('Sucesso', successMessage, 'success');
                } else {
                    window.alert('Sucesso\n\n' + successMessage);
                }
            }
        });
    </script>
<x-page-tour tour-id="usuario-index" :steps="[
    [
        'element' => '.page-title',
        'popover' => [
            'title' => 'Lista de Usuários',
            'description' => 'Aqui você gerencia todos os usuários do sistema: clientes, colaboradores, gerentes e administradores. Cada usuário tem uma regra de acesso que define o que ele pode ver e fazer.',
        ],
    ],
    [
        'element' => '.btn-create-resource, a[href*=\"usuario/create\"]',
        'popover' => [
            'title' => 'Adicionar usuário',
            'description' => 'Clique aqui para cadastrar um novo usuário. O formulário é dividido em etapas: Dados Pessoais, Contato e Endereço, Perfil e Acesso, Carta de Condução e Segurança (senha).',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => '.tabela-usuarios thead tr',
        'popover' => [
            'title' => 'Tabela de usuários',
            'description' => 'Exibe nome, e-mail, telefone, regra de acesso, cidade e data de cadastro. Clique no cabeçalho para ordenar.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => '.tabela-usuarios tbody tr:first-child td',
        'popover' => [
            'title' => 'Regra de acesso',
            'description' => 'O badge colorido indica o perfil do usuário (Cliente, Colaborador, Gerente, Administrador). A regra controla quais módulos e ações cada usuário pode acessar.',
            'side' => 'right',
        ],
    ],
    [
        'element' => '.tabela-usuarios tbody tr:first-child td:last-child',
        'popover' => [
            'title' => 'Ações disponíveis',
            'description' => 'Para cada usuário: <b>Pagamento</b> (gerenciar cartões Stripe), <b>Editar</b> (alterar dados), <b>Resetar senha</b> (voltar para senha padrão) e <b>Excluir</b>.',
            'side' => 'left',
        ],
    ],
]" />
@stop
