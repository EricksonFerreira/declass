<?php

namespace App\Actions\Clockify\Time;

use App\Models\ClockifyTime;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarTimesAction
{
    public function handle(?string $search): LengthAwarePaginator
    {
        return ClockifyTime::with('users')
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%");
            })
            ->orderBy('nome')
            ->paginate(15);
    }
}
