<?php

namespace App\Actions\Clockify\Tag;

use App\Models\ClockifyTag;

class UpdateTagAction
{
    public function handle(int $id, array $data): ClockifyTag
    {
        $tag = ClockifyTag::findOrFail($id);
        $tag->update($data);
        return $tag;
    }
}
