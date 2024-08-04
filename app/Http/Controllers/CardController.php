<?php

namespace App\Http\Controllers;

use App\Services\{Card}Service;
use Illuminate\Http\Request;

class CardController extends Controller
{
    protected $cardsService;

    public function __construct(CardService $cardsService)
    {
        $this->cardsService = $cardsService;
    }

    public function index()
    {
        $cardss = $this->cardsService->all();
        return response()->json($cardss);
    }

    public function show($id)
    {
        $cards = $this->cardsService->find($id);
        return response()->json($cards);
    }

    public function store(Request $request)
    {
        $cards = $this->cardsService->create($request->all());
        return response()->json($cards, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->cardsService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->cardsService->delete($id);
        return response()->json(['success' => $success]);
    }
}