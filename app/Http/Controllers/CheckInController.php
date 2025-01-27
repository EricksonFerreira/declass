<?php

namespace App\Http\Controllers;

use App\Services\ReservaService;
use Illuminate\Http\Request;

class CheckInController extends Controller
{

    private $reservaService;
    public function __construct(ReservaService $reservaService) {
        $this->reservaService = $reservaService;
    }
    public function index(Request $request,$reserva_id)
    {
        $reserva = $this->reservaService->getById($reserva_id);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Reservas');
        return view('reserva.checkin',compact(['reserva', $breadcrumb]))->with('titulo','Check-in da reserva Nº '.$reserva->id);
    }
}
