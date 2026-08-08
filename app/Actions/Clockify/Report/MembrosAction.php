<?php

namespace App\Actions\Clockify\Report\MembrosAction;

use App\Models\User;
use Illuminate\Support\Facades\View;

class MembrosAction
{
    public function handle()
    {
        $users = User::paginate(15);
        return View::make('relatorios.membros', ['users' => $users, 'breadcrumb' => []]);
    }
}
