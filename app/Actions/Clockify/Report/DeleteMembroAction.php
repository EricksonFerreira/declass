<?php

namespace App\Actions\Clockify\Report;

use App\Models\User;

class DeleteMembroAction
{
    public function handle(User $user): void
    {
        $user->delete();
    }
}
