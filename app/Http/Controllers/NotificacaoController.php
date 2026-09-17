<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $notificacoes = $user->notifications()
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('notificacoes.index', [
            'titulo' => 'Notificações',
            'notificacoes' => $notificacoes,
            'naoLidas' => $user->unreadNotifications()->count(),
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Notificações', 'active' => true],
            ],
        ]);
    }

    public function navbar(Request $request): JsonResponse
    {
        $user = Auth::user();
        $naoLidas = $user->unreadNotifications()->count();

        $notificacoes = $user->unreadNotifications()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $html = view('notificacoes.partials.navbar-dropdown', [
            'notificacoes' => $notificacoes,
        ])->render();

        return response()->json([
            'label' => $naoLidas,
            'label_color' => 'warning',
            'icon_color' => $naoLidas > 0 ? 'warning' : 'secondary',
            'dropdown' => $html,
        ]);
    }

    public function marcarLida(string $notificationId)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return back()->with('success', 'Notificação marcada como lida.');
    }

    public function marcarTodasComoLidas()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }
}
