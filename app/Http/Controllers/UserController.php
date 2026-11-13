<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('name')->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'vendeur'])],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);
        User::create($data);

        return redirect()->route('users.index')->with('success', 'Le compte a été créé.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'vendeur'])],
            'password' => ['nullable', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        if ($user->role === 'admin' && $data['role'] !== 'admin' && User::query()->where('role', 'admin')->count() === 1) {
            return back()->withErrors(['role' => 'Le dernier administrateur ne peut pas être rétrogradé.'])->withInput();
        }

        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Le compte a été modifié.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte connecté.');
        }

        if ($user->role === 'admin' && User::query()->where('role', 'admin')->count() === 1) {
            return back()->with('error', 'Le dernier administrateur ne peut pas être supprimé.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Le compte a été supprimé.');
    }
}
