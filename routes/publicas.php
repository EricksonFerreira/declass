<?php

use App\Http\Controllers\MatriculaPublicaController;
use App\Http\Controllers\SiteEmpresaController;
use Illuminate\Support\Facades\Route;

// Formulario publico de solicitacao de matricula (visitante, sem login).
// A escola e resolvida pelos slugs empresa/escola na URL.
Route::get('matricula/{empresaSlug}/{escolaSlug}', [MatriculaPublicaController::class, 'create'])
    ->name('matricula_publica.create');
Route::post('matricula/{empresaSlug}/{escolaSlug}', [MatriculaPublicaController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('matricula_publica.store');

// Site publico da empresa ("rede de escolas") no dominio da plataforma.
// No dominio personalizado da empresa o mesmo controller serve a raiz.
Route::get('rede/{empresa:slug}', [SiteEmpresaController::class, 'show'])
    ->name('rede.site');

// Webhook de cobrancas — o gateway avisa quando a mensalidade e paga.
Route::post('webhooks/asaas', [\App\Http\Controllers\WebhookAsaasController::class, 'handle'])
    ->name('webhook.asaas');
