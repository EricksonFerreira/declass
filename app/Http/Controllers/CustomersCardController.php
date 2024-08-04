<?php

namespace App\Http\Controllers;

use App\Services\{CustomersCard}Service;
use Illuminate\Http\Request;

class CustomersCardController extends Controller
{
    protected $customers_cardsService;

    public function __construct(CustomersCardService $customers_cardsService)
    {
        $this->customers_cardsService = $customers_cardsService;
    }

    public function index()
    {
        $customers_cardss = $this->customers_cardsService->all();
        return response()->json($customers_cardss);
    }

    public function show($id)
    {
        $customers_cards = $this->customers_cardsService->find($id);
        return response()->json($customers_cards);
    }

    public function store(Request $request)
    {
        $customers_cards = $this->customers_cardsService->create($request->all());
        return response()->json($customers_cards, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->customers_cardsService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->customers_cardsService->delete($id);
        return response()->json(['success' => $success]);
    }
}