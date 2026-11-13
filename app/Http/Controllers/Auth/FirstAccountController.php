<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class FirstAccountController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (! $this->installationIsOpen()) {
            return redirect()->route('login');
        }

        return view('auth.setup');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->installationIsOpen(), 404);

        $seededUser = User::query()->where('email', 'test@example.com')->where('name', 'Test User')->first();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($seededUser?->id)],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        $user = DB::transaction(function () use ($data, $seededUser): User {
            abort_unless($this->installationIsOpen(), 404);

            if ($seededUser) {
                $seededUser->update($data + ['role' => 'admin']);

                return $seededUser;
            }

            return User::create([...$data, 'role' => 'admin']);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    private function installationIsOpen(): bool
    {
        if (! User::query()->exists()) {
            return true;
        }

        return User::query()->count() === 1
            && User::query()->where('email', 'test@example.com')->where('name', 'Test User')->exists();
    }
}
