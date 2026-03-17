<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PreVerifyMail;
use App\Models\EmailPreVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmailPreVerificationController extends Controller
{
    public function create(): View
    {
        return view('auth.pre-verify-email');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $email = Str::lower($validated['email']);
        $token = Str::random(64);

        EmailPreVerification::query()->updateOrCreate(
            ['email' => $email],
            [
                'token' => $token,
                'verified_at' => null,
                'expires_at' => now()->addMinutes(30),
            ],
        );

        Mail::to($email)->send(new PreVerifyMail(
            verificationUrl: route('email.pre-verify.confirm', ['token' => $token]),
            email: $email,
        ));

        return back()->with('status', 'A verification link has been sent to your email address.');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $token = (string) $request->query('token', '');

        $preVerification = EmailPreVerification::query()
            ->where('token', $token)
            ->first();

        if ($preVerification === null) {
            return redirect()->route('email.pre-verify.create')
                ->withErrors(['email' => 'The verification link is invalid or has already been used.']);
        }

        if ($preVerification->expires_at->isPast()) {
            $preVerification->delete();

            return redirect()->route('email.pre-verify.create')
                ->withErrors(['email' => 'The verification link has expired.']);
        }

        $email = Str::lower($preVerification->email);

        $preVerification->forceFill([
            'verified_at' => now(),
            'token' => Str::random(64),
        ])->save();

        $request->session()->put('pre_verified_email', $email);

        return redirect()->route('register')
            ->with('status', 'Your email has been pre-verified. Complete registration to continue.');
    }
}
