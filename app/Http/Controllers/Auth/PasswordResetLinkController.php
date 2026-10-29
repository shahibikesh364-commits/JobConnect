<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the forgot-password page.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Temporary development-only password reset.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        // Create a temporary reset token without sending an email.
        $token = bin2hex(random_bytes(32));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $validated['email']],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $validated['email'],
        ]);
    }
}
