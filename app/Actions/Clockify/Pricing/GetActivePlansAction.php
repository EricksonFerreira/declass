<?php
namespace App\Actions\Clockify\Pricing;

use App\Models\Plano;
use Illuminate\Support\Collection;

class GetActivePlansAction
{
    /**
     * Return active planos with essential data for pricing display.
     */
    public function handle(): Collection
    {
        return Plano::ativos()->get()->map(function (Plano $plano) {
            return [
                'id' => $plano->id,
                'nome' => $plano->nome,
                'slug' => $plano->slug,
                'descricao' => $plano->descricao,
                'valor_mensal' => $plano->valor_mensal,
                'valor_anual' => $plano->valor_anual,
                'desconto_anual_percentual' => $plano->desconto_anual_percentual,
                'economia_anual' => $plano->economia_anual,
                'destaque' => $plano->destaque,
                'features' => $plano->featuresDetalhadas(),
            ];
        });
    }
}
