<?php

namespace App\Traits;

trait AtualizaFiltrosTrait
{
    public function atualizarFiltros(array &$itensFiltrados, array &$filtros)
    {
        foreach ($itensFiltrados as $key => &$item) {
            foreach ($filtros as &$filtro) {
                if (! isset($item['name']) || ! isset($filtro['name']) || $filtro['name'] !== $item['name']) {
                    continue;
                }

                if (isset($filtro['type']) && $filtro['type'] === 'text') {
                    if (array_key_exists('valor', $item)) {
                        $filtro['valor'] = $item['valor'];
                    }
                } elseif (isset($filtro['type']) && $filtro['type'] === 'select' && isset($filtro['options'])) {
                    foreach ($filtro['options'] as &$opcao) {
                        if (isset($opcao['value']) && array_key_exists('valor', $item)) {
                            $val = $item['valor'];
                            if (is_array($val)) {
                                if (in_array($opcao['value'], $val)) {
                                    $opcao['selected'] = true;
                                }
                            } else {
                                if ($opcao['value'] == $val) {
                                    $opcao['selected'] = true;
                                }
                            }
                        }
                    }
                    unset($opcao); // Remove a referencia para evitar problemas potenciais
                }
            }
            unset($filtro); // Remove a referencia para evitar problemas potenciais
        }
        unset($item); // Remove a referencia para evitar problemas potenciais
    }
}
