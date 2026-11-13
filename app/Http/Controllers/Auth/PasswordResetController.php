<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PasswordResetController extends Controller
{
    /**
     * Show the "forgot password" form.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Email a reset link. The answer is the same whether or not the address exists, so accounts cannot be guessed.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $exception) {
            Log::error('Échec de l’envoi du lien de réinitialisation.', ['exception' => $exception]);

            throw ValidationException::withMessages([
                'email' => 'Le courriel n’a pas pu être envoyé. Réessayez plus tard ou contactez votre administrateur.',
            ]);
        }

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => 'Un lien vient déjà d’être envoyé. Patientez une minute avant de réessayer.',
            ]);
        }

        return back()->with('status', 'Si un compte correspond à cette adresse, un lien de réinitialisation vient de lui être envoyé.');
    }

    /**
     * Show the form to choose a new password.
     */
    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    /**
     * Save the new password.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'Ce lien de réinitialisation est invalide ou a expiré. Demandez-en un nouveau.',
            ]);
        }

        return redirect()->route('login')->with('status', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');
    }
}
