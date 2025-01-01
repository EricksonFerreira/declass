<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pagamento\CobrarPagamentoRequest;
use App\Models\Transacao;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\Pagamentos\MercadoLivreService;
use Illuminate\Http\Request;

class PagamentoController extends Controller
{
    protected $gerenciadorDePagamentos;

    // public function __construct(GerenciadorDePagamentos $gerenciadorDePagamentos)
    // {
    //     $this->gerenciadorDePagamentos = $gerenciadorDePagamentos;
    // }

    public function teste2()
    {
        return view('pagamento.teste2');
    }

    public function teste(Request $request)
    {
        $mercadoLivreService = new MercadoLivreService();
        return response()->json($mercadoLivreService->teste($request));
    }

    public function cobrar(CobrarPagamentoRequest $cobrarPagamentoRequest)
    {

        $resultado = $this->gerenciadorDePagamentos
            ->resolver($cobrarPagamentoRequest->gateway)
            ->cobrarPagamento($cobrarPagamentoRequest->all());


        if($resultado['status'] === 'success') {
            Transacao::create([
                'reserva_id' => $cobrarPagamentoRequest->reserva_id,
                'valor' => $cobrarPagamentoRequest->valor,
                'metodo_pagamento' => $cobrarPagamentoRequest->gateway,
                'status' => 'Completa',
            ]);

            return response()->json(['message' => 'Pagamento efetuado com sucesso.']);
        }

        return response()->json(['error' => 'Falha no pagamento.'], 400);
    }
}
