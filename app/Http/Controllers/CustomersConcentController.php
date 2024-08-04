<?php

namespace App\Http\Controllers;

use App\Services\{CustomersConcent}Service;
use Illuminate\Http\Request;

class CustomersConcentController extends Controller
{
    protected $customers_concentsService;

    public function __construct(CustomersConcentService $customers_concentsService)
    {
        $this->customers_concentsService = $customers_concentsService;
    }

    public function index()
    {
        $customers_concentss = $this->customers_concentsService->all();
        return response()->json($customers_concentss);
    }

    public function show($id)
    {
        $customers_concents = $this->customers_concentsService->find($id);
        return response()->json($customers_concents);
    }

    public function store(Request $request)
    {
        $customers_concents = $this->customers_concentsService->create($request->all());
        return response()->json($customers_concents, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->customers_concentsService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->customers_concentsService->delete($id);
        return response()->json(['success' => $success]);
    }
}