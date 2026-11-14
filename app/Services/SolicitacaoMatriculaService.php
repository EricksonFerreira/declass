<?php

namespace App\Services;

use App\Mail\AnaliseMatriculaMail;
use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\SolicitacaoMatricula;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SolicitacaoMatriculaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(
        SolicitacaoMatricula $solicitacao,
        private readonly EmailService $emailService,
    ) {
        $this->model = $solicitacao;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof SolicitacaoMatricula) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\SolicitacaoMatricula');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /** Edicao permitida apenas enquanto a solicitacao nao foi decidida. */
    public function update($id, array $data)
    {
        $solicitacao = $this->getById($id);
        abort_if($solicitacao && ! $solicitacao->estaPendente(), 403, 'Solicitação já analisada não pode ser editada.');

        return parent::update($id, $data);
    }

    public function delete($id)
    {
        $solicitacao = $this->getById($id);
        abort_if($solicitacao && ! $solicitacao->estaPendente(), 403, 'Solicitação já analisada não pode ser excluída.');

        return parent::delete($id);
    }

    /**
     * Aprova a solicitacao: cria Pessoa+Aluno, Matricula ativa na
     * turma escolhida e Responsavel legal — tudo em transacao.
     * O numero de chamada e o proximo livre da turma.
     */
    public function aprovar(SolicitacaoMatricula $solicitacao, array $data, User $user): SolicitacaoMatricula
    {
        if (! $solicitacao->estaPendente()) {
            throw ValidationException::withMessages([
                'solicitacao' => 'Esta solicitação já foi analisada.',
            ]);
        }

        $turma = Turma::query()->find($data['turma_id'] ?? 0);
        if (! $turma) {
            throw ValidationException::withMessages(['turma_id' => 'Turma não encontrada.']);
        }
        if ((int) $turma->escola_id !== (int) $solicitacao->escola_id) {
            throw ValidationException::withMessages([
                'turma_id' => 'A turma deve ser da mesma escola da solicitação.',
            ]);
        }
        if ($turma->finalizada()) {
            throw ValidationException::withMessages([
                'turma_id' => 'A turma está inativa ou o ano letivo está encerrado.',
            ]);
        }

        $solicitacao = DB::transaction(function () use ($solicitacao, $turma, $user) {
            // 1. aluno pretendido (pessoas.sexo e M/F/O)
            $pessoaAluno = Pessoa::create([
                'nome' => $solicitacao->nome_aluno,
                'data_nascimento' => $solicitacao->data_nascimento_aluno,
                'sexo' => self::mapaSexo($solicitacao->sexo_aluno),
                'empresa_id' => $solicitacao->empresa_id,
            ]);

            $aluno = Aluno::create([
                'empresa_id' => $solicitacao->empresa_id,
                'pessoa_id' => $pessoaAluno->id,
                'escola_id' => $solicitacao->escola_id,
                'data_ingresso' => now()->toDateString(),
                'ativo' => 'S',
            ]);
            $aluno->update(['registro_academico' => 'RA-'.str_pad((string) $aluno->id, 6, '0', STR_PAD_LEFT)]);

            // 2. responsavel (reaproveita por CPF quando informado)
            $responsavel = $this->resolverResponsavel($solicitacao);

            // 3. matricula ativa com proximo numero de chamada
            $numeroChamada = ((int) Matricula::withTrashed()
                ->where('turma_id', $turma->id)
                ->max('numero_chamada')) + 1;

            $matricula = Matricula::create([
                'empresa_id' => $solicitacao->empresa_id,
                'aluno_id' => $aluno->id,
                'turma_id' => $turma->id,
                'ano_letivo_id' => $turma->ano_letivo_id,
                'status' => 'ativa',
                'numero_chamada' => $numeroChamada,
                'data_matricula' => now()->toDateString(),
            ]);

            // 4. vinculo responsavel legal principal
            DB::table('aluno_responsavel')->insert([
                'aluno_id' => $aluno->id,
                'responsavel_id' => $responsavel->id,
                'tipo' => 'legal',
                'principal' => true,
                'pode_retirar' => true,
                'autoriza_contato' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $solicitacao->update([
                'status' => 'aprovada',
                'turma_id' => $turma->id,
                'ano_letivo_id' => $turma->ano_letivo_id,
                'etapa_id' => $turma->etapa_id ?? $solicitacao->etapa_id,
                'aluno_id' => $aluno->id,
                'responsavel_id' => $responsavel->id,
                'matricula_id' => $matricula->id,
                'analisado_por' => $user->id,
                'data_analise' => now(),
                'motivo_recusa' => null,
            ]);

            return $solicitacao->fresh(['aluno.pessoa', 'responsavel.pessoa', 'matricula.turma']);
        });

        $this->notificarAnalise($solicitacao);

        return $solicitacao;
    }

    /** Reprova a solicitacao informando o motivo. */
    public function recusar(SolicitacaoMatricula $solicitacao, string $motivo, User $user): SolicitacaoMatricula
    {
        if (! $solicitacao->estaPendente()) {
            throw ValidationException::withMessages([
                'solicitacao' => 'Esta solicitação já foi analisada.',
            ]);
        }
        if (trim($motivo) === '') {
            throw ValidationException::withMessages(['motivo_recusa' => 'Informe o motivo da recusa.']);
        }

        $solicitacao->update([
            'status' => 'recusada',
            'motivo_recusa' => $motivo,
            'analisado_por' => $user->id,
            'data_analise' => now(),
        ]);

        $solicitacao = $solicitacao->fresh();

        $this->notificarAnalise($solicitacao);

        return $solicitacao;
    }

    /** Avisa o responsavel por e-mail sobre a decisao. */
    private function notificarAnalise(SolicitacaoMatricula $solicitacao): void
    {
        $this->emailService->enviar(
            $solicitacao->email_responsavel,
            new AnaliseMatriculaMail($solicitacao),
            "pré-matrícula #{$solicitacao->codigo}",
        );
    }

    /** Cancela uma solicitacao ainda nao decidida. */
    public function cancelar(SolicitacaoMatricula $solicitacao, User $user): SolicitacaoMatricula
    {
        if (! $solicitacao->estaPendente()) {
            throw ValidationException::withMessages([
                'solicitacao' => 'Esta solicitação já foi analisada.',
            ]);
        }

        $solicitacao->update([
            'status' => 'cancelada',
            'analisado_por' => $user->id,
            'data_analise' => now(),
        ]);

        return $solicitacao->fresh();
    }

    /** sexo_aluno (masculino/feminino/outro) -> pessoas.sexo (M/F/O). */
    private static function mapaSexo(?string $sexo): ?string
    {
        return match ($sexo) {
            'masculino' => 'M',
            'feminino' => 'F',
            'outro' => 'O',
            default => null,
        };
    }

    /**
     * Responsavel do pedido: reutiliza cadastro existente por CPF
     * (mesmo tenant); senao cria Pessoa + Responsavel novos.
     */
    private function resolverResponsavel(SolicitacaoMatricula $solicitacao): Responsavel
    {
        if ($solicitacao->cpf_responsavel) {
            $existente = Responsavel::query()
                ->where('empresa_id', $solicitacao->empresa_id)
                ->whereHas('pessoa', fn ($q) => $q->where('cpf', $solicitacao->cpf_responsavel))
                ->first();
            if ($existente) {
                return $existente;
            }
        }

        $pessoa = Pessoa::create([
            'nome' => $solicitacao->nome_responsavel,
            'cpf' => $solicitacao->cpf_responsavel,
            'telefone' => $solicitacao->telefone_responsavel,
            'email' => $solicitacao->email_responsavel,
            'empresa_id' => $solicitacao->empresa_id,
        ]);

        return Responsavel::create([
            'empresa_id' => $solicitacao->empresa_id,
            'pessoa_id' => $pessoa->id,
            'ativo' => 'S',
        ]);
    }
}
