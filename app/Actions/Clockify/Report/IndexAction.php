<?php

namespace App\Actions\Clockify\Report;

use Illuminate\Support\Facades\View;

class IndexAction
{
    public function handle()
    {
        return View::make('relatorios.index');
    }
}
