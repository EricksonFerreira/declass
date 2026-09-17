<?php

namespace App\Actions\Clockify\Report;

use Illuminate\Support\Facades\View;

class IndexAction
{
    public function handle()
    {
        $breadcrumb = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Análise'],
            ['name' => 'Relatórios', 'active' => true],
        ];
        return View::make('relatorios.index', ['breadcrumb' => $breadcrumb]);
    }
}
