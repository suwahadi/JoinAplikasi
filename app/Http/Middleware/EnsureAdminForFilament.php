<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminForFilament
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow unauthenticated users to reach Filament's login page; Authenticate middleware will handle redirect
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if ($user->role !== UserRole::ADMIN) {
            // For non-admin authenticated users, redirect to home
            return redirect()->to('/');
        }

        return $next($request);
    }
}
