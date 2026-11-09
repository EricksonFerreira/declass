<?php

namespace App\Support;

/**
 * Paleta do site publico derivada das cores da empresa, com ajuste de
 * contraste (WCAG 2.2 AA): cores muito claras ganham uma variante
 * escurecida para texto sobre branco e o texto sobre a cor e escolhido
 * entre branco e grafite pelo maior contraste.
 */
class SiteTema
{
    private const PRIMARIA_PADRAO = '#1e3a8a';

    private const SECUNDARIA_PADRAO = '#b45309';

    private const GRAFITE = '#111827';

    public static function paleta(?string $primaria, ?string $secundaria): array
    {
        $c1 = self::normalizar($primaria) ?? self::PRIMARIA_PADRAO;
        $c2 = self::normalizar($secundaria) ?? self::SECUNDARIA_PADRAO;

        return [
            'c1' => $c1,
            'c1_texto' => self::legivelSobreBranco($c1),
            'c1_sobre' => self::textoSobre($c1),
            'c1_escura' => self::misturar($c1, '#000000', self::luminancia($c1) > 0.4 ? 0.55 : 0.35),
            'c1_suave' => self::misturar($c1, '#ffffff', 0.92),
            'c2' => $c2,
            'c2_texto' => self::legivelSobreBranco($c2),
            'c2_sobre' => self::textoSobre($c2),
            'c2_suave' => self::misturar($c2, '#ffffff', 0.9),
        ];
    }

    public static function contraste(string $a, string $b): float
    {
        [$claro, $escuro] = [max(self::luminancia($a), self::luminancia($b)), min(self::luminancia($a), self::luminancia($b))];

        return ($claro + 0.05) / ($escuro + 0.05);
    }

    /** Escurece a cor ate atingir 4.5:1 sobre branco (texto normal AA). */
    public static function legivelSobreBranco(string $cor): string
    {
        $passo = 0.0;
        $resultado = $cor;
        while (self::contraste($resultado, '#ffffff') < 4.5 && $passo < 1) {
            $passo += 0.05;
            $resultado = self::misturar($cor, '#000000', $passo);
        }

        return $resultado;
    }

    public static function textoSobre(string $cor): string
    {
        return self::contraste($cor, '#ffffff') >= self::contraste($cor, self::GRAFITE) ? '#ffffff' : self::GRAFITE;
    }

    public static function normalizar(?string $cor): ?string
    {
        $cor = strtolower(trim((string) $cor));
        if (preg_match('/^#([0-9a-f]{3})$/', $cor, $m)) {
            $cor = '#'.preg_replace('/(.)/', '$1$1', $m[1]);
        }

        return preg_match('/^#[0-9a-f]{6}$/', $cor) ? $cor : null;
    }

    /** Mistura $cor com $alvo na proporcao $peso (0 = cor original, 1 = alvo). */
    public static function misturar(string $cor, string $alvo, float $peso): string
    {
        $a = self::rgb($cor);
        $b = self::rgb($alvo);

        return sprintf('#%02x%02x%02x', ...array_map(
            fn ($i) => (int) round($a[$i] + ($b[$i] - $a[$i]) * $peso),
            [0, 1, 2]
        ));
    }

    public static function luminancia(string $cor): float
    {
        [$r, $g, $b] = array_map(function ($canal) {
            $c = $canal / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, self::rgb($cor));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    private static function rgb(string $cor): array
    {
        $hex = ltrim(self::normalizar($cor) ?? '#000000', '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
