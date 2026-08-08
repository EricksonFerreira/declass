<?php

namespace App\Actions\Clockify\Tag;

use App\Models\ClockifyTag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarTagsAction
{
    public function handle(?string $search): LengthAwarePaginator
    {
        return ClockifyTag::query()
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%");
            })
            ->orderBy('nome')
            ->paginate(15);
    }
}
