<?php

namespace App\Actions\Clockify\Tag;

use App\Models\ClockifyTag;

class DeleteTagAction
{
    public function handle(int $id): bool
    {
        $tag = ClockifyTag::findOrFail($id);
        return $tag->delete();
    }
}
