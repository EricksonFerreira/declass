<?php

namespace App\Support;

/**
 * Converte valor monetario para extenso em portugues
 * (uso em recibos/comprovantes).
 */
class ValorExtenso
{
    private const UNIDADES = ['', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];

    private const DEZENAS = ['', '', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];

    private const ESPECIAIS = [
        10 => 'dez', 11 => 'onze', 12 => 'doze', 13 => 'treze', 14 => 'catorze', 15 => 'quinze',
        16 => 'dezesseis', 17 => 'dezessete', 18 => 'dezoito', 19 => 'dezenove',
    ];

    private const CENTENAS = [
        100 => 'cem', 200 => 'duzentos', 300 => 'trezentos', 400 => 'quatrocentos',
        500 => 'quinhentos', 600 => 'seiscentos', 700 => 'setecentos', 800 => 'oitocentos',
        900 => 'novecentos',
    ];

    public static function converter(float $valor): string
    {
        $valor = round($valor, 2);
        $inteiro = (int) floor($valor);
        $centavos = (int) round(($valor - $inteiro) * 100);

        $partes = [];
        $milhoes = intdiv($inteiro, 1000000);
        $milhares = intdiv($inteiro % 1000000, 1000);
        $resto = $inteiro % 1000;

        if ($milhoes > 0) {
            $partes[] = self::ateMil($milhoes).' '.($milhoes === 1 ? 'milhão' : 'milhões');
        }
        if ($milhares > 0) {
            $partes[] = $milhares === 1 ? 'mil' : self::ateMil($milhares).' mil';
        }
        if ($resto > 0) {
            $partes[] = self::ateMil($resto);
        }
        if ($partes === []) {
            $partes[] = 'zero';
        }

        $texto = implode(' e ', array_filter($partes));
        $texto .= ' real'.($inteiro === 1 ? '' : 'is');

        if ($centavos > 0) {
            $texto .= ' e '.self::ateMil($centavos).' centavo'.($centavos === 1 ? '' : 's');
        }

        return $texto;
    }

    private static function ateMil(int $n): string
    {
        if ($n < 20) {
            return self::ESPECIAIS[$n] ?? self::UNIDADES[$n];
        }

        if ($n === 20) {
            return 'vinte';
        }

        if ($n < 100) {
            $dezena = intdiv($n, 10);
            $unidade = $n % 10;
            $base = self::DEZENAS[$dezena];

            return $unidade ? $base.' e '.self::UNIDADES[$unidade] : $base;
        }

        if ($n === 100) {
            return 'cem';
        }

        $centena = intdiv($n, 100) * 100;
        $resto = $n % 100;

        $texto = self::CENTENAS[$centena];
        if ($resto > 0) {
            $texto .= ' e '.self::ateMil($resto);
        }

        return $texto;
    }
}
