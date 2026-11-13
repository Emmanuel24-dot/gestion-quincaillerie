@extends('layouts.app')

@section('title', 'Comptes utilisateurs')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Comptes utilisateurs</h1>
            <p class="text-muted mb-0">Gérez les administrateurs et les vendeurs autorisés.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('users.create') }}"><i class="bi bi-person-plus me-1" aria-hidden="true"></i>Ajouter un compte</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h2 class="h6 mb-0">Comptes</h2>
            <span class="text-muted small">{{ $users->total() }} compte(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-4">Nom</th><th>Courriel</th><th>Rôle</th><th class="text-end pe-4">Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            @php($isCurrentUser = auth()->user()->is($user))
                            <tr>
                                <td class="ps-4"><strong>{{ $user->name }}</strong>@if ($isCurrentUser)<small class="text-muted ms-1">(vous)</small>@endif</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge {{ $user->role === 'admin' ? 'text-bg-primary' : 'text-bg-secondary' }}">{{ $user->role === 'admin' ? 'Administrateur' : 'Vendeur' }}</span></td>
                                <td class="text-end pe-4 text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('users.edit', $user) }}" aria-label="Modifier {{ $user->name }}"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                                    @unless ($isCurrentUser)
                                        <form class="d-inline" method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Supprimer ce compte ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Supprimer {{ $user->name }}"><i class="bi bi-trash" aria-hidden="true"></i></button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3">{{ $users->links() }}</div>
        </div>
    </div>
</div>
@endsection
