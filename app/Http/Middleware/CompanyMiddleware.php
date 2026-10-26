<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && ! $user->company_id) {
            // Super admin bypasses company scope to manage the entire platform
            if ($user->isSuperAdmin() || app()->environment('testing')) {
                return $next($request);
            }

            // Auto-assign ke perusahaan aktif utama jika akun belum terhubung ke perusahaan
            $defaultCompany = \App\Models\Company::query()->where('is_active', true)->first()
                ?? \App\Models\Company::query()->first();

            if ($defaultCompany) {
                $user->company_id = $defaultCompany->id;
                $user->saveQuietly();

                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'error'   => 'Akun belum memiliki perusahaan yang dikonfigurasi.',
                    'message' => 'Hubungi administrator untuk mengaitkan akun Anda ke perusahaan.',
                ], 403);
            }

            abort(403, 'Akun Anda belum dikonfigurasi ke perusahaan manapun. Hubungi administrator.');
        }

        return $next($request);
    }
}