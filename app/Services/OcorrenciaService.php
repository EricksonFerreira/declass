<?php

namespace App\Services;

use App\Models\Ocorrencia;
use Illuminate\Database\Eloquent\Model;

class OcorrenciaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Ocorrencia $ocorrencia)
    {
        $this->model = $ocorrencia;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Ocorrencia) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Ocorrencia');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        $data['user_id'] = $data['user_id'] ?? auth()->id();
        $ocorrencia = parent::create($this->normalizar($data));

        $this->notificarResponsaveis($ocorrencia);

        return $ocorrencia;
    }

    public function update($id, array $data)
    {
        $antes = $this->getById($id);
        $ocorrencia = parent::update($id, $this->normalizar($data));

        // so notifica na edicao quando a flag esta sendo ativada agora
        if ($ocorrencia && ! ($antes?->notifica_responsavel)) {
            $this->notificarResponsaveis($ocorrencia);
        }

        return $ocorrencia;
    }

    /**
     * Elogio nao tem gravidade: grava 'leve' como valor neutro
     * (a coluna e NOT NULL com default 'leve').
     */
    private function normalizar(array $data): array
    {
        if (($data['tipo'] ?? null) === 'elogio') {
            $data['gravidade'] = 'leve';
        }

        return $data;
    }

    /**
     * Envia aviso no sistema para os responsaveis com conta (e a
     * conta do proprio aluno) quando a ocorrencia pede notificacao.
     */
    private function notificarResponsaveis(?Ocorrencia $ocorrencia): void
    {
        if (! $ocorrencia || ! $ocorrencia->notifica_responsavel || ! $ocorrencia->estaAtivo()) {
            return;
        }

        $aluno = $ocorrencia->aluno()->with(['pessoa', 'responsaveis'])->first();
        if (! $aluno) {
            return;
        }

        $userIds = $aluno->responsaveis
            ->pluck('user_id')
            ->push($aluno->user_id)
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $titulo = $ocorrencia->tipo === 'elogio'
            ? 'Elogio registrado'
            : $ocorrencia->tipoLabel().' registrada';
        $mensagem = ($aluno->pessoa?->nome ?? 'O aluno').': '.$ocorrencia->tipoLabel()
            .' em '.$ocorrencia->data->format('d/m/Y')
            .($ocorrencia->tipo !== 'elogio' ? ' — gravidade '.$ocorrencia->gravidadeLabel() : '').'.';

        [$icone, $cor] = match ($ocorrencia->tipo) {
            'elogio' => ['fas fa-star', 'success'],
            'suspensao' => ['fas fa-ban', 'danger'],
            default => ['fas fa-exclamation-triangle', 'warning'],
        };

        \App\Models\User::query()->whereIn('id', $userIds)->get()->each(
            fn (\App\Models\User $u) => $u->notify(new \App\Notifications\AvisoSistemaNotification(
                $titulo, $mensagem, route('portal.alertas'), $icone, $cor,
                'ocorrencia-'.$ocorrencia->id, 'ocorrencia'
            ))
        );
    }
}
