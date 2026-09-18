<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class FiltroQuery
{
    /**
     * Aplica os itens filtrados (sessao) a uma query, restrito as colunas permitidas.
     */
    public static function aplicar(Builder $query, array $itensFiltered, array $colunasPermitidas): Builder
    {
        foreach ($itensFiltered as $field => $value) {
            if (! in_array($field, $colunasPermitidas, true)) {
                continue;
            }

            if (! is_array($value) || ! array_key_exists('valor', $value)) {
                continue;
            }

            $typeWhere = $value['typeWhere'] ?? 'where';
            $val = $value['valor'];

            if ($val === null || $val === '') {
                continue;
            }

            if ($typeWhere === 'between') {
                $from = $val['from'] ?? null;
                $to = $val['to'] ?? null;
                if ($from && $to) {
                    $query->whereBetween($field, [$from, $to]);
                } elseif ($from) {
                    $query->where($field, '>=', $from);
                } elseif ($to) {
                    $query->where($field, '<=', $to);
                }
            } elseif ($typeWhere === 'like') {
                $query->where($field, 'like', '%'.(is_array($val) ? ($val[0] ?? '') : $val).'%');
            } else {
                is_array($val) ? $query->whereIn($field, $val) : $query->where($field, $val);
            }
        }

        return $query;
    }
}
