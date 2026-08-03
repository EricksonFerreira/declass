<?php

namespace App\Actions\Veiculo;

use App\Models\Veiculo;
use App\Services\VeiculoService;
use Carbon\Carbon;

class AdicionarDisponibilidadeVeiculoAction
{
    public function __construct(
        private readonly VeiculoService $veiculoService,
    ) {}

    public function handle(int $veiculoId, array $data): array
    {
        $veiculo = $this->veiculoService->getById($veiculoId, ['localDisponibilidade']);

        if (! $veiculo) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Veículo não encontrado.',
            ];
        }

        if (! empty($veiculo->local_disponibilidade)) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Este veículo já possui uma estação vinculada e a disponibilidade não pode ser cadastrada novamente.',
            ];
        }

        $veiculo = $this->veiculoService->update($veiculoId, [
            'local_disponibilidade' => $data['local_disponibilidade'],
            'data_hora_disponibilidade' => $data['data_hora_disponibilidade'],
        ]);

        if (! $veiculo) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'ão foi possível salvar a disponibilidade do veículo.',
            ];
        }

        $veiculo->refresh();

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Disponibilidade cadastrada com sucesso.',
            'payload' => [
                'local_disponibilidade_id' => $veiculo->local_disponibilidade,
                'local_disponibilidade_nome' => $veiculo->localDisponibilidade?->nome,
                'data_hora_disponibilidade' => filled($veiculo->data_hora_disponibilidade)
                    ? Carbon::parse($veiculo->data_hora_disponibilidade)->format('Y-m-d H:i:s')
                    : null,
            ],
        ];
    }
}
