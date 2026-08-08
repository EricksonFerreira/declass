<?php

namespace App\Actions\Clockify\TimeOff;

use App\Models\ClockifyTimeOff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ListarTimeOffsAction
{
    public function handle(): LengthAwarePaginator
    {
        $query = ClockifyTimeOff::with('user');

        if (!Auth::user()->can('gerenciar-empresa')) {
            $query->where('user_id', Auth::id());
        }

        return $query->orderBy('data_inicio', 'desc')->paginate(15);
    }
}
