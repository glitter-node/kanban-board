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

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:'.User::class,
                function (string $attribute, mixed $value, \Closure $fail) use ($preVerifiedEmail): void {
                    $email = Str::lower((string) $value);

                    if ($preVerifiedEmail === '') {
                        $fail('Email pre-verification is required.');

                        return;
                    }

                    if ($email !== $preVerifiedEmail) {
                        $fail('The email must match the pre-verified email address.');

                        return;
                    }

                    $exists = EmailPreVerification::query()
                        ->where('email', $email)
                        ->whereNotNull('verified_at')
                        ->exists();

                    if (! $exists) {
                        $fail('The email address has not been pre-verified.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        EmailPreVerification::query()
            ->where('email', Str::lower($request->email))
            ->delete();

        $request->session()->forget('pre_verified_email');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
