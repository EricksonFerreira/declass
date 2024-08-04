<?php

namespace App\Http\Controllers;

use App\Services\{FleetFeature}Service;
use Illuminate\Http\Request;

class FleetFeatureController extends Controller
{
    protected $fleet_featuresService;

    public function __construct(FleetFeatureService $fleet_featuresService)
    {
        $this->fleet_featuresService = $fleet_featuresService;
    }

    public function index()
    {
        $fleet_featuress = $this->fleet_featuresService->all();
        return response()->json($fleet_featuress);
    }

    public function show($id)
    {
        $fleet_features = $this->fleet_featuresService->find($id);
        return response()->json($fleet_features);
    }

    public function store(Request $request)
    {
        $fleet_features = $this->fleet_featuresService->create($request->all());
        return response()->json($fleet_features, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->fleet_featuresService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->fleet_featuresService->delete($id);
        return response()->json(['success' => $success]);
    }
}