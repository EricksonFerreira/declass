<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    protected $bookingsService;

    // public function __construct(BookingService $bookingsService)
    // {
    //     $this->bookingsService = $bookingsService;
    // }

    public function index()
    {
        // $bookings = $this->bookingsService->getAll();
        // return view('bookings.index',compact('bookings'));
        return view('bookings.index');
    }

    public function show($id)
    {
        $bookings = $this->bookingsService->getById($id);
        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $bookings = $this->bookingsService->create($request->all());
        return response()->json($bookings, 201);
    }

    public function update(Request $request, $id)
    {
        $success = $this->bookingsService->update($request->all(), $id);
        return response()->json(['success' => $success]);
    }

    public function destroy($id)
    {
        $success = $this->bookingsService->delete($id);
        return response()->json(['success' => $success]);
    }
}
