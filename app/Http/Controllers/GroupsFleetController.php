<?php

namespace App\Http\Controllers;

use App\Services\{GroupsFleet}Service;
use Illuminate\Http\Request;

class GroupsFleetController extends Controller
{
    protected $groups_fleetService;

    public function __construct(GroupsFleetService $groups_fleetService)
    {
        $this->groups_fleetService = $groups_fleetService;
    }

    public function index()
    {
        $groups_fleets = $this->groups_fleetService->all();
        return response()->json($groups_fleets);
    }

    public function show($id)
    {
        $groups_fleet = $this->groups_fleetService->find($id);
        return response()->json($groups_fleet);
    }

    public function store(Request $request)
    {
        $groups_fleet = $this->groups_fleetService->create($request->all());
        return response()->json($groups_fleet, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->groups_fleetService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->groups_fleetService->delete($id);
        return response()->json(['success' => $success]);
    }
}