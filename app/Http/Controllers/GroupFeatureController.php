<?php

namespace App\Http\Controllers;

use App\Services\{GroupFeature}Service;
use Illuminate\Http\Request;

class GroupFeatureController extends Controller
{
    protected $group_featuresService;

    public function __construct(GroupFeatureService $group_featuresService)
    {
        $this->group_featuresService = $group_featuresService;
    }

    public function index()
    {
        $group_featuress = $this->group_featuresService->all();
        return response()->json($group_featuress);
    }

    public function show($id)
    {
        $group_features = $this->group_featuresService->find($id);
        return response()->json($group_features);
    }

    public function store(Request $request)
    {
        $group_features = $this->group_featuresService->create($request->all());
        return response()->json($group_features, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->group_featuresService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->group_featuresService->delete($id);
        return response()->json(['success' => $success]);
    }
}