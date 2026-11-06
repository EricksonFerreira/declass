<?php

namespace App\Http\Controllers;

use App\Http\Requests\SolicitacaoMatricula\StoreSolicitacaoPublicaRequest;
use App\Models\Empresa;
use App\Models\Escola;
use App\Models\SolicitacaoMatricula;
use App\Models\User;
use App\Notifications\AvisoSistemaNotification;
use Illuminate\Support\Facades\DB;

/**
 * Formulario publico de solicitacao de matricula: visitante
 * (sem login) pede vaga informando escola/rede pela URL.
 * Gera solicitacao com origem "portal" e avisa a equipe.
 */
class MatriculaPublicaController extends BaseController
{
    /** Pagina do formulario — escola resolvida por slugs da URL. */
    public function create(string $empresaSlug, string $escolaSlug)
    {
        [$empresa, $escola] = $this->resolverEscola($empresaSlug, $escolaSlug);

        $anosLetivos = \App\Models\AnoLetivo::query()
            ->where('escola_id', $escola->id)->orderByDesc('ano')->get(['id', 'ano']);
        $etapas = \App\Models\Etapa::query()
            ->where('empresa_id', $empresa->id)->orderBy('nome')->get(['id', 'nome']);

        return view('matricula-publica.form', [
            'titulo' => 'Solicitar matrícula — '.$escola->nome,
            'empresa' => $empresa,
            'escola' => $escola,
            'anosLetivos' => $anosLetivos,
            'etapas' => $etapas,
            'enviada' => false,
        ]);
    }

    /** Recebe o formulario e registra a solicitacao na fila. */
    public function store(StoreSolicitacaoPublicaRequest $request, string $empresaSlug, string $escolaSlug)
    {
        [$empresa, $escola] = $this->resolverEscola($empresaSlug, $escolaSlug);

        $data = $request->validated();

        $solicitacao = SolicitacaoMatricula::create([
            'empresa_id' => $empresa->id,
            'escola_id' => $escola->id,
            'ano_letivo_id' => $data['ano_letivo_id'] ?? null,
            'etapa_id' => $data['etapa_id'] ?? null,
            'turma_id' => null,
            'nome_aluno' => $data['nome_aluno'],
            'data_nascimento_aluno' => $data['data_nascimento_aluno'] ?? null,
            'sexo_aluno' => $data['sexo_aluno'] ?? null,
            'nome_responsavel' => $data['nome_responsavel'],
            'parentesco' => $data['parentesco'] ?? null,
            'telefone_responsavel' => $data['telefone_responsavel'] ?? null,
            'email_responsavel' => $data['email_responsavel'] ?? null,
            'cpf_responsavel' => $data['cpf_responsavel'] ?? null,
            'origem' => 'portal',
            'status' => 'pendente',
            'observacoes' => $data['observacoes'] ?? null,
        ]);

        $this->notificarEquipe($solicitacao, $escola);

        $anosLetivos = \App\Models\AnoLetivo::query()
            ->where('escola_id', $escola->id)->orderByDesc('ano')->get(['id', 'ano']);
        $etapas = \App\Models\Etapa::query()
            ->where('empresa_id', $empresa->id)->orderBy('nome')->get(['id', 'nome']);

        return view('matricula-publica.form', [
            'titulo' => 'Solicitação enviada — '.$escola->nome,
            'empresa' => $empresa,
            'escola' => $escola,
            'anosLetivos' => $anosLetivos,
            'etapas' => $etapas,
            'enviada' => true,
            'protocolo' => $solicitacao->id,
        ]);
    }

    /** Resolve (empresa, escola) pelos slugs; 404 se nao existir/estiver inativa. */
    private function resolverEscola(string $empresaSlug, string $escolaSlug): array
    {
        $empresa = Empresa::query()->where('slug', $empresaSlug)->where('ativo', 'S')->firstOrFail();
        $escola = Escola::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', $escolaSlug)
            ->where('ativa', 'S')
            ->firstOrFail();

        return [$empresa, $escola];
    }

    /** Avisa os usuarios do tenant que podem analisar a fila. */
    private function notificarEquipe(SolicitacaoMatricula $solicitacao, Escola $escola): void
    {
        $userIds = DB::table('users')
            ->join('role_has_permissions', 'users.role_id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('users.empresa_id', $escola->empresa_id)
            ->where('permissions.name', 'solicitacao_matricula.index')
            ->where('permissions.guard_name', 'web')
            ->where('users.ativo', true)
            ->distinct()
            ->pluck('users.id');

        if ($userIds->isEmpty()) {
            return;
        }

        User::query()->whereIn('id', $userIds)->get()->each(
            fn (User $u) => $u->notify(new AvisoSistemaNotification(
                'Nova solicitação de matrícula',
                "{$solicitacao->nome_aluno} solicitou vaga em {$escola->nome} (portal público).",
                route('solicitacao_matricula.show', $solicitacao->id),
                'fas fa-user-plus',
                'success',
                'solicitacao-matricula-'.$solicitacao->id,
                'matricula'
            ))
        );
    }
}
