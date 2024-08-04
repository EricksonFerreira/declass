<?php

namespace App\Http\Controllers;

use App\Services\{CustomersDrivingLicence}Service;
use Illuminate\Http\Request;

class CustomersDrivingLicenceController extends Controller
{
    protected $customers_driving_licenceService;

    public function __construct(CustomersDrivingLicenceService $customers_driving_licenceService)
    {
        $this->customers_driving_licenceService = $customers_driving_licenceService;
    }

    public function index()
    {
        $customers_driving_licences = $this->customers_driving_licenceService->all();
        return response()->json($customers_driving_licences);
    }

    public function show($id)
    {
        $customers_driving_licence = $this->customers_driving_licenceService->find($id);
        return response()->json($customers_driving_licence);
    }

    public function store(Request $request)
    {
        $customers_driving_licence = $this->customers_driving_licenceService->create($request->all());
        return response()->json($customers_driving_licence, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->customers_driving_licenceService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->customers_driving_licenceService->delete($id);
        return response()->json(['success' => $success]);
    }
}