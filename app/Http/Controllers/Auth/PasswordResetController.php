<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    private const TOKEN_TTL_MINUTES = 60;

    public function showRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:devotees,email'],
        ], [
            'email.exists' => 'No GIYA account is registered with that email address.',
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetUrl = route('password.reset', ['token' => $token]) . '?email=' . urlencode($request->email);

        // Mail is queued through the configured local mailer. When the app runs
        // fully offline set MAIL_MAILER=log — the link is written to the log file.
        try {
            Mail::send(
                'emails.reset-password',
                ['resetUrl' => $resetUrl, 'email' => $request->email],
                fn ($m) => $m->to($request->email)->subject('GIYA — Password Reset Request')
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with('warning',
                'Mail could not be sent. Your reset link was written to storage/logs/laravel.log.');
        }

        return back()->with('success',
            "A reset link has been sent to {$request->email}. It expires in " . self::TOKEN_TTL_MINUTES . ' minutes.');
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email', 'exists:devotees,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'This reset link is invalid. Please request a new one.']);
        }

        if (now()->diffInMinutes($record->created_at) > self::TOKEN_TTL_MINUTES) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withErrors(['token' => 'This reset link has expired. Please request a new one.']);
        }

        User::where('email', $request->email)->update([
            'password_hash' => Hash::make($request->password),
            'updated_at'    => now(),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Password reset successfully. Please sign in.');
    }
}
