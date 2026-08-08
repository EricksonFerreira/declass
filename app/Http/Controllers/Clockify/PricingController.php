<?php
namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Actions\Clockify\Pricing\GetActivePlansAction;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Show the pricing page with active plans.
     */
    public function index(GetActivePlansAction $action)
    {
        $plans = $action->handle();
        return view('clockify.pricing', compact('plans'));
    }
}
