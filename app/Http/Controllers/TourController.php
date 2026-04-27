<?php

namespace App\Http\Controllers;

use App\Models\TourTela;
use App\Models\TourRealizacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TourController extends Controller
{
    public function status(string $chave): JsonResponse
    {
        $tela = TourTela::where('chave', $chave)->first();

        if (!$tela) {
            // Tela não cadastrada — cria automaticamente e libera o tour
            $tela = TourTela::create(['chave' => $chave, 'nome_exibicao' => $chave]);
        }

        $realizacao = TourRealizacao::where('user_id', Auth::id())
            ->where('tour_tela_id', $tela->id)
            ->first();

        return response()->json([
            'feito' => $realizacao?->feito ?? false,
        ]);
    }

    public function concluir(string $chave): JsonResponse
    {
        $tela = TourTela::firstOrCreate(
            ['chave' => $chave],
            ['nome_exibicao' => $chave]
        );

        TourRealizacao::updateOrCreate(
            ['user_id' => Auth::id(), 'tour_tela_id' => $tela->id],
            ['feito' => true, 'feito_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    public function resetar(string $chave): JsonResponse
    {
        $tela = TourTela::where('chave', $chave)->first();

        if ($tela) {
            TourRealizacao::where('user_id', Auth::id())
                ->where('tour_tela_id', $tela->id)
                ->update(['feito' => false, 'feito_at' => null]);
        }

        return response()->json(['ok' => true]);
    }
}
