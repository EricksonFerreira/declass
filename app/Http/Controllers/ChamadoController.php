<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chamado\StoreChamadoRequest;
use App\Models\Chamado;
use App\Models\ChamadoAnexo;
use App\Models\ChamadoCategoria;
use App\Models\ChamadoLog;
use App\Models\ChamadoStatus;
use App\Models\ChamadoSubcategoria;
use App\Models\User;
use App\Support\ArquivoAnexoHelper;
use App\Services\BreadcrumbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Throwable;

class ChamadoController extends Controller
{
    public function __construct(private readonly BreadcrumbService $breadcrumbService) {}

    public function criar()
    {
        $empresaId = $this->empresaId();
        $categorias = ChamadoCategoria::query()->where('empresa_id', $empresaId)->orWhereNull('empresa_id')->where('ativo', true)->orderBy('nome')->get();
        $statusAbertura = ChamadoStatus::query()->where(function ($q) use ($empresaId) { $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id'); })->where('slug', Chamado::STATUS_ABERTO)->first();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Suporte', route('chamado.create')),
            new \App\DTO\BreadCrumbItem('Criar chamado'),
        ]);
        return view('chamado.create', compact('breadcrumb', 'categorias', 'statusAbertura'));
    }

    public function store(StoreChamadoRequest $request)
    {
        try {
            $empresaId = $this->empresaId();
            $status = $this->statusPadrao($empresaId, Chamado::STATUS_ABERTO);
            $sub = $request->filled('chamado_subcategoria_id') ? ChamadoSubcategoria::find($request->integer('chamado_subcategoria_id')) : null;

            DB::transaction(function () use ($request, $empresaId, $status, $sub) {
                $prazoHoras = (int) ($sub?->prazo_horas ?? 24);
                $chamado = Chamado::create([
                    'empresa_id' => $empresaId,
                    'user_id' => Auth::id(),
                    'criado_por_user_id' => Auth::id(),
                    'chamado_categoria_id' => $request->integer('chamado_categoria_id'),
                    'chamado_subcategoria_id' => $request->filled('chamado_subcategoria_id') ? $request->integer('chamado_subcategoria_id') : null,
                    'chamado_status_id' => $status->id,
                    'descricao' => trim((string) $request->input('descricao')),
                    'prazo_atendimento_em' => $this->adicionarHorasUteis(now(), $prazoHoras),
                    'aberto_em' => now(),
                ]);

                foreach ((array) $request->file('anexos', []) as $file) {
                    $armazenado = ArquivoAnexoHelper::armazenarRastreado($file, 'chamados/'.date('Y/m'), [
                        'modulo' => 'chamado',
                        'entidade' => Chamado::class,
                        'entidade_id' => $chamado->id,
                        'campo' => 'anexo',
                        'contexto' => 'chamado.abertura',
                    ]);
                    if (! is_array($armazenado) || empty($armazenado['path'])) {
                        continue;
                    }
                    ChamadoAnexo::create([
                        'chamado_id' => $chamado->id,
                        'user_id' => Auth::id(),
                        'path' => $armazenado['path'],
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $armazenado['size'] ?? (int) $file->getSize(),
                    ]);
                    ChamadoLog::create([
                        'chamado_id' => $chamado->id,
                        'user_id' => Auth::id(),
                        'acao' => 'Anexo adicionado',
                        'detalhes' => json_encode([
                            'arquivo' => $file->getClientOriginalName(),
                        ], JSON_UNESCAPED_UNICODE),
                        'nivel' => 'info',
                    ]);
                }

                ChamadoLog::create([
                    'chamado_id' => $chamado->id,
                    'user_id' => Auth::id(),
                    'acao' => 'Chamado aberto',
                    'detalhes' => json_encode([
                        'categoria' => $chamado->categoria?->nome,
                        'subcategoria' => $chamado->subcategoria?->nome,
                        'descricao' => $chamado->descricao,
                    ], JSON_UNESCAPED_UNICODE),
                    'nivel' => 'success',
                ]);
            });

            return redirect()->route('chamado.meus')->with('success', 'Chamado aberto com sucesso.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Não foi possível abrir o chamado agora. Verifique os campos e tente novamente.');
        }
    }

    public function meus(Request $request)
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Suporte', route('chamado.meus')),
            new \App\DTO\BreadCrumbItem('Meus chamados'),
        ]);

        $filtros = [
            'status' => trim((string) $request->input('status', '')),
            'termo' => trim((string) $request->input('termo', '')),
            'categoria_id' => (int) $request->input('categoria_id', 0),
            'data_inicio' => trim((string) $request->input('data_inicio', '')),
            'data_fim' => trim((string) $request->input('data_fim', '')),
        ];

        $categorias = ChamadoCategoria::query()
            ->where(function ($query) {
                $query->where('empresa_id', Auth::user()?->empresa_id)->orWhereNull('empresa_id');
            })
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        $chamados = Chamado::query()->with(['categoria', 'subcategoria', 'status', 'anexos', 'logs.usuario', 'solicitante', 'responsavel'])
            ->where('user_id', Auth::id())
            ->when($filtros['categoria_id'] > 0, function ($query) use ($filtros) {
                $query->where('chamado_categoria_id', $filtros['categoria_id']);
            })
            ->when($filtros['status'] !== '', function ($query) use ($filtros) {
                $query->whereHas('status', fn ($q) => $q->where('slug', $filtros['status']));
            })
            ->when($filtros['termo'] !== '', function ($query) use ($filtros) {
                $termo = $filtros['termo'];
                $query->where(function ($q) use ($termo) {
                    $q->where('descricao', 'like', '%'.$termo.'%')
                        ->orWhereHas('categoria', fn ($sub) => $sub->where('nome', 'like', '%'.$termo.'%'))
                        ->orWhereHas('subcategoria', fn ($sub) => $sub->where('nome', 'like', '%'.$termo.'%'));
                });
            })
            ->when($filtros['data_inicio'] !== '', function ($query) use ($filtros) {
                $query->whereDate('created_at', '>=', $filtros['data_inicio']);
            })
            ->when($filtros['data_fim'] !== '', function ($query) use ($filtros) {
                $query->whereDate('created_at', '<=', $filtros['data_fim']);
            })
            ->orderByDesc('id')
            ->paginate(15);

        return view('chamado.meus', compact('breadcrumb', 'chamados', 'filtros', 'categorias'));
    }

    public function atendimento(Request $request)
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);

        return $this->atendimentoVisao('visao-geral');
    }

    public function atendimentoFilaTotal()
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);
        return $this->atendimentoVisao('fila-total');
    }

    public function atendimentoAguardando()
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);
        return $this->atendimentoVisao('aguardando-analise');
    }

    public function atendimentoEmAtendimento()
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);
        return $this->atendimentoVisao('em-atendimento');
    }

    public function atendimentoConcluidos()
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);
        return $this->atendimentoVisao('concluidos');
    }

    public function assumirAtendimento(Request $request, Chamado $chamado)
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);

        $data = $request->validate([
            'responsavel_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $responsavel = User::query()
            ->where('id', $data['responsavel_user_id'])
            ->where('tipo_usuario_id', 1)
            ->firstOrFail();

        $statusAtendimento = $this->statusPadrao($this->empresaId(), Chamado::STATUS_ATENDIMENTO);

        $chamado->update([
            'chamado_status_id' => $statusAtendimento->id,
            'responsavel_user_id' => $responsavel->id,
            'em_atendimento_em' => now(),
        ]);

        ChamadoLog::create([
            'chamado_id' => $chamado->id,
            'user_id' => Auth::id(),
            'acao' => 'Chamado atribuído e iniciado atendimento',
            'detalhes' => json_encode([
                'responsavel' => $responsavel->nome,
                'status' => 'em_atendimento',
            ], JSON_UNESCAPED_UNICODE),
            'nivel' => 'info',
        ]);

        return back()->with('success', 'Chamado atribuído e movido para em atendimento.');
    }

    public function cancelarAtendimento(Request $request, Chamado $chamado)
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);

        $data = $request->validate([
            'cancelamento_justificativa' => ['required', 'string', 'min:3'],
            'anexos.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $statusCancelado = $this->statusPadrao($this->empresaId(), Chamado::STATUS_CANCELADO);

        DB::transaction(function () use ($request, $chamado, $statusCancelado, $data) {
            $chamado->update([
                'chamado_status_id' => $statusCancelado->id,
                'cancelado_em' => now(),
                'cancelamento_justificativa' => trim($data['cancelamento_justificativa']),
                'aguardando_solicitante_em' => null,
            ]);

            $log = ChamadoLog::create([
                'chamado_id' => $chamado->id,
                'user_id' => Auth::id(),
                'acao' => 'Chamado cancelado',
                'detalhes' => json_encode([
                    'justificativa' => trim($data['cancelamento_justificativa']),
                    'status' => 'cancelado',
                ], JSON_UNESCAPED_UNICODE),
                'nivel' => 'danger',
            ]);

            $this->salvarAnexosLog($request, $chamado, $log, 'cancelamento');
        });

        return back()->with('success', 'Chamado cancelado com sucesso.');
    }

    public function enviarMensagem(Request $request, Chamado $chamado)
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);

        $data = $request->validate([
            'mensagem' => ['required', 'string', 'min:1'],
            'anexos.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $mensagem = trim($data['mensagem']);

        if ($mensagem === '') {
            return back()
                ->withInput()
                ->withErrors(['mensagem' => 'A mensagem não pode estar vazia.']);
        }

        DB::transaction(function () use ($request, $chamado, $mensagem) {
            $log = ChamadoLog::create([
                'chamado_id' => $chamado->id,
                'user_id' => Auth::id(),
                'acao' => 'Mensagem enviada',
                'detalhes' => json_encode([
                    'mensagem' => $mensagem,
                    'destino' => 'solicitante',
                ], JSON_UNESCAPED_UNICODE),
                'nivel' => 'primary',
            ]);

            $anexosSalvos = $this->salvarAnexosLog($request, $chamado, $log, 'mensagem');

            if ($request->hasFile('anexos') && $anexosSalvos === 0) {
                ChamadoLog::create([
                    'chamado_id' => $chamado->id,
                    'user_id' => Auth::id(),
                    'acao' => 'Anexo recebido sem gravação',
                    'detalhes' => json_encode([
                        'contexto' => 'mensagem',
                        'arquivo_enviado' => true,
                        'anexos_salvos' => 0,
                    ], JSON_UNESCAPED_UNICODE),
                    'nivel' => 'warning',
                ]);
            }

            if ($chamado->status?->slug === Chamado::STATUS_AGUARDANDO_SOLICITANTE && $chamado->aguardando_solicitante_em) {
                $minutosUtil = $this->calcularTempoUtilEntre($chamado->aguardando_solicitante_em, now());
                $statusAtendimento = $this->statusPadrao($this->empresaId(), Chamado::STATUS_ATENDIMENTO);

                $chamado->update([
                    'chamado_status_id' => $statusAtendimento->id,
                    'aguardando_solicitante_em' => null,
                    'prazo_atendimento_em' => $chamado->prazo_atendimento_em?->copy()->addMinutes($minutosUtil),
                    'em_atendimento_em' => now(),
                ]);

                ChamadoLog::create([
                    'chamado_id' => $chamado->id,
                    'user_id' => Auth::id(),
                    'acao' => 'Solicitante respondeu',
                    'detalhes' => json_encode([
                        'minutos_util_aguardando' => $minutosUtil,
                        'status' => 'em_atendimento',
                    ], JSON_UNESCAPED_UNICODE),
                    'nivel' => 'info',
                ]);
            }
        });

        return back()->with('success', 'Mensagem enviada com sucesso.');
    }

    public function enviarMensagemCliente(Request $request, Chamado $chamado)
    {
        abort_unless((int) $chamado->user_id === (int) Auth::id(), 403);

        $status = $chamado->status?->slug;
        abort_if(in_array($status, [Chamado::STATUS_CANCELADO, Chamado::STATUS_CONCLUIDO], true), 422);

        $data = $request->validate([
            'mensagem' => ['nullable', 'string'],
            'anexos.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $mensagem = trim((string) ($data['mensagem'] ?? ''));
        $arquivos = array_values(array_filter((array) $request->file('anexos', [])));

        if ($mensagem === '' && empty($arquivos)) {
            return back()
                ->withInput()
                ->withErrors(['mensagem' => 'Envie uma mensagem ou pelo menos um anexo.']);
        }

        DB::transaction(function () use ($request, $chamado, $mensagem) {
            $log = ChamadoLog::create([
                'chamado_id' => $chamado->id,
                'user_id' => Auth::id(),
                'acao' => 'Mensagem do solicitante',
                'detalhes' => json_encode([
                    'mensagem' => $mensagem,
                    'destino' => 'suporte',
                ], JSON_UNESCAPED_UNICODE),
                'nivel' => 'primary',
            ]);

            $this->salvarAnexosLog($request, $chamado, $log, 'mensagem');
        });

        return back()->with('success', 'Mensagem enviada com sucesso.');
    }

    public function concluirAtendimento(Request $request, Chamado $chamado)
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);

        $data = $request->validate([
            'justificativa' => ['required', 'string', 'min:3'],
            'anexos.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $statusConcluido = $this->statusPadrao($this->empresaId(), Chamado::STATUS_CONCLUIDO);

        DB::transaction(function () use ($request, $chamado, $data, $statusConcluido) {
            $chamado->update([
                'chamado_status_id' => $statusConcluido->id,
                'concluido_em' => now(),
                'solucao' => trim($data['justificativa']),
            ]);

            foreach ((array) $request->file('anexos', []) as $file) {
                $path = $file->store('chamados/'.date('Y/m'), 'public');
                ChamadoAnexo::create([
                    'chamado_id' => $chamado->id,
                    'user_id' => Auth::id(),
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            ChamadoLog::create([
                'chamado_id' => $chamado->id,
                'user_id' => Auth::id(),
                'acao' => 'Chamado concluído',
                'detalhes' => json_encode([
                    'justificativa' => trim($data['justificativa']),
                    'anexos' => count((array) $request->file('anexos', [])),
                    'status' => 'concluido',
                ], JSON_UNESCAPED_UNICODE),
                'nivel' => 'success',
            ]);
        });

        return back()->with('success', 'Chamado concluído com sucesso.');
    }

    public function aguardarSolicitante(Request $request, Chamado $chamado)
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);

        $data = $request->validate([
            'justificativa' => ['required', 'string', 'min:3'],
            'anexos.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $statusAguardando = $this->statusPadrao($this->empresaId(), Chamado::STATUS_AGUARDANDO_SOLICITANTE);

        DB::transaction(function () use ($request, $chamado, $data, $statusAguardando) {
            $chamado->update([
                'chamado_status_id' => $statusAguardando->id,
                'aguardando_solicitante_em' => now(),
            ]);

            $log = ChamadoLog::create([
                'chamado_id' => $chamado->id,
                'user_id' => Auth::id(),
                'acao' => 'Aguardando solicitante',
                'detalhes' => json_encode([
                    'justificativa' => trim($data['justificativa']),
                    'status' => 'aguardando_solicitante',
                ], JSON_UNESCAPED_UNICODE),
                'nivel' => 'warning',
            ]);

            $this->salvarAnexosLog($request, $chamado, $log, 'aguardando_solicitante');
        });

        return back()->with('success', 'Chamado pausado aguardando resposta do solicitante.');
    }

    public function atualizarStatusAtendimento(Request $request, Chamado $chamado)
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', [
                Chamado::STATUS_ATENDIMENTO,
                Chamado::STATUS_CONCLUIDO,
                Chamado::STATUS_CANCELADO,
                Chamado::STATUS_AGUARDANDO_SOLICITANTE,
            ])],
            'descricao' => ['required', 'string', 'min:3'],
            'anexos.*' => ['nullable', 'file', 'max:10240'],
        ]);

        return match ($data['status']) {
            Chamado::STATUS_CONCLUIDO => $this->concluirAtendimento($request, $chamado),
            Chamado::STATUS_CANCELADO => $this->cancelarAtendimento($request, $chamado),
            Chamado::STATUS_AGUARDANDO_SOLICITANTE => $this->aguardarSolicitante($request, $chamado),
            default => $this->manterEmAtendimento($request, $chamado),
        };
    }

    private function manterEmAtendimento(Request $request, Chamado $chamado)
    {
        return back()->with('success', 'Status mantido em atendimento.');
    }

    public function transferirAtendimento(Request $request, Chamado $chamado)
    {
        abort_unless(Auth::user()?->can('acessar-superadmin'), 403);

        $data = $request->validate([
            'responsavel_user_id' => ['required', 'integer', 'exists:users,id'],
            'descricao' => ['required', 'string', 'min:3'],
        ]);

        $responsavel = User::query()
            ->where('id', $data['responsavel_user_id'])
            ->where('tipo_usuario_id', 1)
            ->firstOrFail();

        $chamado->update([
            'responsavel_user_id' => $responsavel->id,
            'em_atendimento_em' => now(),
        ]);

        ChamadoLog::create([
            'chamado_id' => $chamado->id,
            'user_id' => Auth::id(),
            'acao' => 'Chamado transferido',
            'detalhes' => json_encode([
                'responsavel' => $responsavel->nome,
                'descricao' => trim($data['descricao']),
                'status' => 'em_atendimento',
            ], JSON_UNESCAPED_UNICODE),
            'nivel' => 'info',
        ]);

        return back()->with('success', 'Chamado transferido com sucesso.');
    }

    public function subcategorias(Request $request)
    {
        return ChamadoSubcategoria::query()->where('chamado_categoria_id', $request->integer('categoria_id'))->where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'prazo_horas']);
    }

    private function empresaId(): ?int
    {
        return Auth::user()?->empresa_id ? (int) Auth::user()->empresa_id : null;
    }

    private function salvarAnexosLog(Request $request, Chamado $chamado, ChamadoLog $log, string $contexto): int
    {
        $salvos = 0;
        foreach (array_values(array_filter((array) $request->file('anexos', []))) as $file) {
            $armazenado = ArquivoAnexoHelper::armazenarRastreado($file, 'chamados/'.date('Y/m'), [
                'modulo' => 'chamado',
                'entidade' => Chamado::class,
                'entidade_id' => $chamado->id,
                'campo' => 'anexo',
                'contexto' => $contexto,
                'meta' => [
                    'log_id' => $log->id,
                ],
            ]);
            if (! is_array($armazenado) || empty($armazenado['path'])) {
                continue;
            }

            ChamadoAnexo::create([
                'chamado_id' => $chamado->id,
                'chamado_log_id' => $log->id,
                'contexto' => $contexto,
                'user_id' => Auth::id(),
                'path' => $armazenado['path'],
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $armazenado['size'] ?? (int) $file->getSize(),
            ]);

            $salvos++;
        }

        return $salvos;
    }

    private function calcularTempoUtilEntre(Carbon $inicio, Carbon $fim): int
    {
        $inicio = $inicio->copy();
        $fim = $fim->copy();
        $minutos = 0;

        while ($inicio->lt($fim)) {
            if (! $inicio->isWeekend() && (($inicio->hour >= 8 && $inicio->hour < 12) || ($inicio->hour >= 13 && $inicio->hour < 17))) {
                $minutos++;
            }
            $inicio->addMinute();
        }

        return $minutos;
    }

    private function statusPadrao(?int $empresaId, string $slug): ChamadoStatus
    {
        return ChamadoStatus::firstOrCreate([
            'empresa_id' => $empresaId,
            'slug' => $slug,
        ], [
            'nome' => match ($slug) {
                Chamado::STATUS_ABERTO => 'Aguardando análise',
                Chamado::STATUS_ATENDIMENTO => 'Em atendimento',
                Chamado::STATUS_CANCELADO => 'Cancelado',
                default => 'Concluído',
            },
            'cor' => match ($slug) {
                Chamado::STATUS_ABERTO => 'warning',
                Chamado::STATUS_ATENDIMENTO => 'info',
                Chamado::STATUS_CANCELADO => 'danger',
                default => 'success',
            },
            'ativo' => true,
        ]);
    }

    private function atendimentoVisao(string $modo)
    {
        $baseQuery = Chamado::query()
            ->with(['categoria', 'subcategoria', 'status', 'anexos', 'logs.usuario', 'solicitante', 'responsavel'])
            ->latest('id');

        $total = (clone $baseQuery)->count();
        $aguardandoAnalise = (clone $baseQuery)->whereHas('status', fn ($q) => $q->where('slug', Chamado::STATUS_ABERTO))->count();
        $emAtendimento = (clone $baseQuery)->whereHas('status', fn ($q) => $q->where('slug', Chamado::STATUS_ATENDIMENTO))->count();
        $concluidos = (clone $baseQuery)->whereHas('status', fn ($q) => $q->where('slug', Chamado::STATUS_CONCLUIDO))->count();
        $meusEmAtendimento = (clone $baseQuery)->where('responsavel_user_id', Auth::id())->whereHas('status', fn ($q) => $q->where('slug', Chamado::STATUS_ATENDIMENTO))->count();
        $meusConcluidos = (clone $baseQuery)->where('responsavel_user_id', Auth::id())->whereHas('status', fn ($q) => $q->where('slug', Chamado::STATUS_CONCLUIDO))->count();

        $chamadosTotal = (clone $baseQuery)->paginate(15, ['*'], 'total_page');
        $chamadosAguardandoAnalise = (clone $baseQuery)->whereHas('status', fn ($q) => $q->where('slug', Chamado::STATUS_ABERTO))->paginate(15, ['*'], 'aguardando_page');
        $chamadosEmAtendimento = (clone $baseQuery)->whereHas('status', fn ($q) => $q->where('slug', Chamado::STATUS_ATENDIMENTO))->paginate(15, ['*'], 'em_atendimento_page');
        $chamadosConcluidos = (clone $baseQuery)->whereHas('status', fn ($q) => $q->where('slug', Chamado::STATUS_CONCLUIDO))->paginate(15, ['*'], 'concluidos_page');

        $breadcrumbMap = [
            'visao-geral' => ['Atendimento chamado', route('chamado.atendimento')],
            'fila-total' => ['Fila total', route('chamado.atendimento.total')],
            'aguardando-analise' => ['Aguardando análise', route('chamado.atendimento.aguardando')],
            'em-atendimento' => ['Em atendimento', route('chamado.atendimento.em')],
            'concluidos' => ['Concluídos', route('chamado.atendimento.concluidos')],
        ];

        [$titulo, $urlAtual] = $breadcrumbMap[$modo] ?? $breadcrumbMap['visao-geral'];

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Atendimento chamado', route('chamado.atendimento')),
            new \App\DTO\BreadCrumbItem($titulo),
        ]);

        $superadmins = User::query()
            ->where('tipo_usuario_id', 1)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return view('chamado.atendimento', compact(
            'breadcrumb',
            'modo',
            'titulo',
            'total',
            'aguardandoAnalise',
            'emAtendimento',
            'concluidos',
            'meusEmAtendimento',
            'meusConcluidos',
            'chamadosTotal',
            'chamadosAguardandoAnalise',
            'chamadosEmAtendimento',
            'chamadosConcluidos',
            'superadmins'
        ));
    }

    private function adicionarHorasUteis(Carbon $inicio, int $horas): Carbon
    {
        $inicio = $inicio->copy();
        $restante = max(0, $horas);

        while ($restante > 0) {
            $inicio = $this->proximoHorarioUtil($inicio);

            $fimManha = $inicio->copy()->setTime(12, 0, 0);
            $fimTarde = $inicio->copy()->setTime(17, 0, 0);

            if ($inicio->hour < 12) {
                $limiteAtual = $fimManha;
            } elseif ($inicio->hour >= 13 && $inicio->hour < 17) {
                $limiteAtual = $fimTarde;
            } else {
                $inicio = $this->proximoHorarioUtil($inicio->addDay()->startOfDay());
                continue;
            }

            $minutosDisponiveis = $inicio->diffInMinutes($limiteAtual, false);

            if ($minutosDisponiveis <= 0) {
                $inicio = $this->proximoHorarioUtil($inicio->addMinute());
                continue;
            }

            $minutosConsumidos = min($restante * 60, $minutosDisponiveis);
            $inicio = $inicio->copy()->addMinutes($minutosConsumidos);
            $restante -= (int) ceil($minutosConsumidos / 60);
        }

        return $inicio;
    }

    private function proximoHorarioUtil(Carbon $momento): Carbon
    {
        $momento = $momento->copy();

        while ($momento->isWeekend()) {
            $momento->addDay()->startOfDay();
        }

        if ($momento->hour < 8) {
            return $momento->setTime(8, 0, 0);
        }

        if ($momento->hour >= 12 && $momento->hour < 13) {
            return $momento->setTime(13, 0, 0);
        }

        if ($momento->hour >= 17) {
            $momento->addDay()->startOfDay();
            while ($momento->isWeekend()) {
                $momento->addDay()->startOfDay();
            }
            return $momento->setTime(8, 0, 0);
        }

        return $momento;
    }
}
