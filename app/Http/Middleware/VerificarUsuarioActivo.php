<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarUsuarioActivo
{
    /**
     * Verifica que el usuario autenticado tenga estado activo.
     * Si fue desactivado mientras tenía sesión abierta, lo expulsa.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->estado) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'error',
                'Su cuenta ha sido desactivada. Contacte al administrador.'
            );
        }

        return $next($request);
    }
}
