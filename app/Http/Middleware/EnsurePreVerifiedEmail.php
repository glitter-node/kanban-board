<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePreVerifiedEmail
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('pre_verified_email')) {
            return $next($request);
        }

        return redirect()->route('email.pre-verify.create')
            ->with('status', 'Verify your email before creating an account.');
    }
}
