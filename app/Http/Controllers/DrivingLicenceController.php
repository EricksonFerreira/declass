<?php

namespace App\Http\Controllers;

use App\Services\{DrivingLicence}Service;
use Illuminate\Http\Request;

class DrivingLicenceController extends Controller
{
    protected $driving_licenceService;

    public function __construct(DrivingLicenceService $driving_licenceService)
    {
        $this->driving_licenceService = $driving_licenceService;
    }

    public function index()
    {
        $driving_licences = $this->driving_licenceService->all();
        return response()->json($driving_licences);
    }

    public function show($id)
    {
        $driving_licence = $this->driving_licenceService->find($id);
        return response()->json($driving_licence);
    }

    public function store(Request $request)
    {
        $driving_licence = $this->driving_licenceService->create($request->all());
        return response()->json($driving_licence, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->driving_licenceService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->driving_licenceService->delete($id);
        return response()->json(['success' => $success]);
    }
}