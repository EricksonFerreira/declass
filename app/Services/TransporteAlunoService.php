<?php

namespace App\Services;

use App\Models\ParadaTransporte;
use App\Models\TransporteAluno;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class TransporteAlunoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(TransporteAluno $transporteAluno)
    {
        $this->model = $transporteAluno;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof TransporteAluno) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\TransporteAluno');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        $this->validar($data);

        return parent::create($data);
    }

    public function update($id, array $data)
    {
        $registro = $this->getById($id);
        abort_if(! $registro, 403, 'Sem acesso a este registro.');

        $this->validar($data, $registro->id);

        return parent::update($id, $data);
    }

    /**
     * Regras: a parada precisa pertencer a rota informada e o
     * aluno so pode ter UMA atribuicao ativa de transporte.
     */
    private function validar(array $data, ?int $ignorarId = null): void
    {
        if (! empty($data['parada_id'])) {
            $paradaDaRota = ParadaTransporte::query()
                ->where('id', $data['parada_id'])
                ->where('rota_id', $data['rota_id'])
                ->exists();

            if (! $paradaDaRota) {
                throw ValidationException::withMessages([
                    'parada_id' => 'A parada selecionada não pertence a esta rota.',
                ]);
            }
        }

        $ativo = ($data['ativo'] ?? 'S') === 'S';
        if (! $ativo) {
            return;
        }

        $duplicado = TransporteAluno::query()
            ->where('aluno_id', $data['aluno_id'])
            ->where('ativo', 'S')
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->exists();

        if ($duplicado) {
            throw ValidationException::withMessages([
                'aluno_id' => 'Este aluno já possui uma atribuição ativa de transporte.',
            ]);
        }
    }
}
