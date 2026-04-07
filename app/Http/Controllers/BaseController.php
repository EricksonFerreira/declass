<?php

namespace App\Http\Controllers;

class BaseController extends Controller
{
    protected $nomeFiltro;
    protected $titulo;

    protected function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'tipotransacao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'tipotransacao.index');
    }
}
