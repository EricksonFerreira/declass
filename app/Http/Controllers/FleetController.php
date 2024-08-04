<?php

namespace App\Http\Controllers;

use App\Services\{Fleet}Service;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    protected $fleetsService;

    public function __construct(FleetService $fleetsService)
    {
        $this->fleetsService = $fleetsService;
    }

    public function index()
    {
        $fleetss = $this->fleetsService->all();
        return response()->json($fleetss);
    }

    public function show($id)
    {
        $fleets = $this->fleetsService->find($id);
        return response()->json($fleets);
    }

    public function store(Request $request)
    {
        $fleets = $this->fleetsService->create($request->all());
        return response()->json($fleets, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->fleetsService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->fleetsService->delete($id);
        return response()->json(['success' => $success]);
    }
}