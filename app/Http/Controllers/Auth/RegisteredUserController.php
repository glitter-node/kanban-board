<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailPreVerification;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $preVerifiedEmail = Str::lower((string) $request->session()->get('pre_verified_email', ''));

        if ($preVerifiedEmail !== '') {
            $request->merge([
                'email' => $preVerifiedEmail,
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($preVerifiedEmail === '') {
            throw ValidationException::withMessages([
                'email' => 'Email pre-verification is required.',
            ]);
        }

        $preVerification = EmailPreVerification::query()
            ->where('email', $preVerifiedEmail)
            ->whereNotNull('verified_at')
            ->first();

        if ($preVerification === null) {
            throw ValidationException::withMessages([
                'email' => 'The email address has not been pre-verified.',
            ]);
        }

        if ($preVerification->expires_at->isPast()) {
            $request->session()->forget('pre_verified_email');
            $preVerification->delete();

            throw ValidationException::withMessages([
                'email' => 'The pre-verification session has expired. Please verify your email again.',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $preVerifiedEmail,
            'password' => Hash::make($request->password),
        ]);

        EmailPreVerification::query()
            ->where('email', $preVerifiedEmail)
            ->delete();

        $request->session()->forget('pre_verified_email');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
