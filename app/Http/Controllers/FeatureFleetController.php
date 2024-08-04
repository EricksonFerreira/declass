<?php

namespace App\Http\Controllers;

use App\Services\{FeatureFleet}Service;
use Illuminate\Http\Request;

class FeatureFleetController extends Controller
{
    protected $feature_fleetsService;

    public function __construct(FeatureFleetService $feature_fleetsService)
    {
        $this->feature_fleetsService = $feature_fleetsService;
    }

    public function index()
    {
        $feature_fleetss = $this->feature_fleetsService->all();
        return response()->json($feature_fleetss);
    }

    public function show($id)
    {
        $feature_fleets = $this->feature_fleetsService->find($id);
        return response()->json($feature_fleets);
    }

    public function store(Request $request)
    {
        $feature_fleets = $this->feature_fleetsService->create($request->all());
        return response()->json($feature_fleets, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->feature_fleetsService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->feature_fleetsService->delete($id);
        return response()->json(['success' => $success]);
    }
}