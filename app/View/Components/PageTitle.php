<?php
namespace App\View\Components;

use Illuminate\View\Component;

class PageTitle extends Component
{
    public $title;
    public $subtitle;
    public $createUrl;

    public function __construct($title, $createUrl=null, $subtitle=null)
    {
        $this->title = $title;
        $this->createUrl = $createUrl;
        $this->subtitle = $subtitle;
    }

    public function render()
    {
        return view('components.page-title');
    }
}
