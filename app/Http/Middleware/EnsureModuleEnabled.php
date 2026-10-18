<?php

namespace App\Http\Middleware;

use App\Support\ModuleGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    /**
     * Blokir akses ke route yang termasuk modul non-aktif.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (ModuleGate::isRouteBlocked($request->route()?->getName()) || ModuleGate::isPathBlocked($request->path())) {
            abort(404);
        }

        return $next($request);
    }
}
