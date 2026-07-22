<?php

namespace App\Services;

use App\Models\DisponibilidadeInicialVeiculo;
use App\Models\Movimentacao;
use App\Models\ReservaCodigo;
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

    public function create(array $data)
    {
        if (empty($data['reserva_codigo_id']) && !empty($data['reserva_id'])) {
            $reservaCodigoId = $this->resolverReservaCodigoId($data['reserva_id']);

            if ($reservaCodigoId) {
                $data['reserva_codigo_id'] = $reservaCodigoId;
            }
        }

        return parent::create($data);
    }

    public function obterDanosPorVeiculoEReserva($veiculo_id, $reserva_id)
    {
        $reservaCodigoId = $this->resolverReservaCodigoId($reserva_id);

        if (!$reservaCodigoId && !is_numeric($reserva_id)) {
            return [];
        }

        return $this->model->where('veiculo_id', $veiculo_id)
            ->where(function ($query) use ($reserva_id, $reservaCodigoId) {
                if ($reservaCodigoId) {
                    $query->where('reserva_codigo_id', $reservaCodigoId);
                }

                if (is_numeric($reserva_id)) {
                    $query->orWhere('reserva_id', (int) $reserva_id);
                }
            })
            ->with(['imagem', 'dano.categoria', 'usuarioCorrecao'])
            ->orderBy('id')
            ->get()
            ->map(fn (VeiculoDanos $d) => $this->mapearDanoParaJson($d))
            ->values()
            ->all();
    }

    public function obterDanosPorVeiculoExcetoReserva($veiculo_id, $reserva_id)
    {
        $reservaCodigoId = $this->resolverReservaCodigoId($reserva_id);

        return $this->model->where('veiculo_id', $veiculo_id)
            ->where(function ($query) use ($reserva_id, $reservaCodigoId) {
                if ($reservaCodigoId) {
                    $query->where(function ($sub) use ($reservaCodigoId) {
                        $sub->where('reserva_codigo_id', '!=', $reservaCodigoId)
                            ->orWhereNull('reserva_codigo_id');
                    });
                }

                if (is_numeric($reserva_id)) {
                    $query->where(function ($sub) use ($reserva_id) {
                        $sub->where('reserva_id', '!=', (int) $reserva_id)
                            ->orWhereNull('reserva_id');
                    });
                }
            })
            ->with(['imagem', 'dano.categoria', 'usuarioCorrecao'])
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
                    $normalizedPath = ltrim(str_replace('\\', '/', (string) $path), '/');
                    $url = Storage::disk('public')->exists($normalizedPath)
                        ? '/storage/' . $normalizedPath
                        : null;
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
            'nome_dano' => $d->dano?->nome,
            'categoria' => $d->dano?->categoria?->nome,
            'local_dano' => $d->local_dano,
            'localizacao_problema' => $d->localizacao_problema,
            'custo_estimado' => $d->custo_estimado,
            'quilometragem_veiculo' => $d->quilometragem_veiculo,
            'created_at' => $d->created_at?->format('d/m/Y H:i'),
            'imagens' => $imagens,
            'correcao_descricao' => $d->correcao_descricao,
            'correcao_data' => $d->correcao_data?->format('d/m/Y H:i'),
            'correcao_usuario' => $d->usuarioCorrecao?->nome,
            'correcao_realizada' => $d->correcao_data !== null,
        ];
    }

    private function resolverReservaCodigoId(mixed $referencia): ?int
    {
        if ($referencia === null || $referencia === '') {
            return null;
        }

        $query = ReservaCodigo::query();

        if (is_numeric($referencia)) {
            $query->where('reserva_id', (int) $referencia);
        } else {
            $query->where('codigo', (string) $referencia);
        }

        $id = $query->value('id');

        return $id ? (int) $id : null;
    }
}
