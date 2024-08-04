<?php

namespace App\Http\Controllers;

use App\Services\{Consent}Service;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    protected $consentsService;

    public function __construct(ConsentService $consentsService)
    {
        $this->consentsService = $consentsService;
    }

    public function index()
    {
        $consentss = $this->consentsService->all();
        return response()->json($consentss);
    }

    public function show($id)
    {
        $consents = $this->consentsService->find($id);
        return response()->json($consents);
    }

    public function store(Request $request)
    {
        $consents = $this->consentsService->create($request->all());
        return response()->json($consents, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->consentsService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->consentsService->delete($id);
        return response()->json(['success' => $success]);
    }
}