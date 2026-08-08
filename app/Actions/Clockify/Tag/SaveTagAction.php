<?php

namespace App\Actions\Clockify\Tag;

use App\Models\ClockifyTag;

class SaveTagAction
{
    public function handle(array $data): ClockifyTag
    {
        return ClockifyTag::create(array_merge($data, ['ativo' => true]));
    }
}
