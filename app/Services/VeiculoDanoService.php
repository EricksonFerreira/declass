<?php

namespace App\Services;

use App\Models\DisponibilidadeInicialVeiculo;
use App\Models\Movimentacao;
use App\Models\Veiculo;
use App\Models\VeiculoDanos;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Illuminate\Support\Facades\Storage;

class VeiculoDanoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(VeiculoDanos $veiculoDanos)
    {
        $this->model = $veiculoDanos;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof VeiculoDanos) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\VeiculoDanos");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function obterDanosPorVeiculoEReserva($veiculo_id, $reserva_id)
    {
        return $this->model->where('veiculo_id', $veiculo_id)
            ->where('reserva_id', $reserva_id)
            ->with('imagem')
            ->orderBy('id')
            ->get()
            ->map(fn (VeiculoDanos $d) => $this->mapearDanoParaJson($d))
            ->values()
            ->all();
    }

    public function obterDanosPorVeiculoExcetoReserva($veiculo_id, $reserva_id)
    {
        return $this->model->where('veiculo_id', $veiculo_id)
            ->where(function ($query) use ($reserva_id) {
                $query->where('reserva_id', '!=', $reserva_id)
                    ->orWhereNull('reserva_id');
            })
            ->with('imagem')
            ->orderBy('id')
            ->get()
            ->map(fn (VeiculoDanos $d) => $this->mapearDanoParaJson($d))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapearDanoParaJson(VeiculoDanos $d): array
    {
        $loc = $d->local_dano === 'atras' ? 'ATRÁS' : strtoupper((string) $d->local_dano);
        $imagens = $d->imagem->map(function ($img) {
            $path = $img->caminho_imagem ?: $img->nome_imagem;
            $url = null;
            if (!empty($path)) {
                try {
                    $url = Storage::disk('public')->url($path);
                } catch (\Throwable $e) {
                    $url = null;
                }
            }

            return [
                'nome' => $img->nome_imagem,
                'path' => $img->caminho_imagem,
                'url'  => $url,
            ];
        })->values()->all();

        return [
            'id' => $d->id,
            'localizacao' => $loc,
            'descricao' => $d->observacoes,
            'tipo_dano' => $d->tipo_dano,
            'imagens' => $imagens,
            'correcao_descricao' => $d->correcao_descricao,
            'correcao_data' => $d->correcao_data?->format('Y-m-d H:i'),
            'correcao_realizada' => $d->correcao_data !== null,
        ];
    }
}
