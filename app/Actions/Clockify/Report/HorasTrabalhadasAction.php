<?php

namespace App\Actions\Clockify\Report\HorasTrabalhadasAction;

use Illuminate\Support\Facades\View;

class HorasTrabalhadasAction
{
    public function handle()
    {
        return View::make('relatorios.horas-trabalhadas');
    }
}
