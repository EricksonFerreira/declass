<?php

namespace App\Services;

use App\Models\DisponibilidadeInicialVeiculo;
use App\Models\Movimentacao;
use App\Models\Veiculo;
use App\Models\VeiculoDanos;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

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

    public function obterDanosPorVeiculoEReserva($veiculo_id,$reserva_id){
        $dados = $this->model->where('veiculo_id', $veiculo_id)
            ->where('reserva_id', $reserva_id)
            ->leftJoin('veiculo_danos_imagens', 'veiculo_danos.id', '=', 'veiculo_danos_imagens.dano_veiculo_id')
            ->select('veiculo_danos.local_dano AS localizacao', 'veiculo_danos.observacoes AS descricao', 'veiculo_danos_imagens.nome_imagem')
            ->orderBy('veiculo_danos.id','asc')
            ->get();

        $result = $this->formatDanos($dados);

        return $result;

    }

    public function obterDanosPorVeiculoExcetoReserva($veiculo_id, $reserva_id)
    {
            $dados = $this->model->where('veiculo_id', $veiculo_id)
                ->where(function($query) use ($reserva_id) {
                    $query->where('reserva_id', '!=', $reserva_id)
                          ->orWhereNull('reserva_id');
                })
                ->leftJoin('veiculo_danos_imagens', 'veiculo_danos.id', '=', 'veiculo_danos_imagens.dano_veiculo_id')
                ->select('veiculo_danos.local_dano AS localizacao', 'veiculo_danos.observacoes AS descricao', 'veiculo_danos_imagens.nome_imagem')
                ->orderBy('veiculo_danos.id','asc')
                ->get();

            $result = $this->formatDanos($dados);

            return $result;

    }

    private function formatDanos($dados)
    {
    $result = [];
    foreach ($dados as $dado) {
        if (!isset($result[$dado->localizacao])) {
        $imagem =  $dado->nome_imagem ? $dado->nome_imagem : [];
        $localizacao = $dado->localizacao == 'atras' ? 'ATRÁS' : strtoupper($dado->localizacao);
        $result[] = [
            'localizacao' => $localizacao,
            'descricao' => $dado->descricao,
            'imagens' => $imagem
        ];
        }
    }
    return $result;
    }
}
