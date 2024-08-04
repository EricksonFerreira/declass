<?php

namespace App\Http\Controllers;

use App\Services\{FeatureGroupFleet}Service;
use Illuminate\Http\Request;

class FeatureGroupFleetController extends Controller
{
    protected $feature_group_fleetService;

    public function __construct(FeatureGroupFleetService $feature_group_fleetService)
    {
        $this->feature_group_fleetService = $feature_group_fleetService;
    }

    public function index()
    {
        $feature_group_fleets = $this->feature_group_fleetService->all();
        return response()->json($feature_group_fleets);
    }

    public function show($id)
    {
        $feature_group_fleet = $this->feature_group_fleetService->find($id);
        return response()->json($feature_group_fleet);
    }

    public function store(Request $request)
    {
        $feature_group_fleet = $this->feature_group_fleetService->create($request->all());
        return response()->json($feature_group_fleet, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->feature_group_fleetService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->feature_group_fleetService->delete($id);
        return response()->json(['success' => $success]);
    }
}