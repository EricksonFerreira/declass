<?php

namespace App\Http\Controllers;

use App\Services\{Customer}Service;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $customersService;

    public function __construct(CustomerService $customersService)
    {
        $this->customersService = $customersService;
    }

    public function index()
    {
        $customerss = $this->customersService->all();
        return response()->json($customerss);
    }

    public function show($id)
    {
        $customers = $this->customersService->find($id);
        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $customers = $this->customersService->create($request->all());
        return response()->json($customers, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->customersService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->customersService->delete($id);
        return response()->json(['success' => $success]);
    }
}