@extends('layouts.app')

@section('title', 'Modifier un compte')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a class="back-link" href="{{ route('users.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux comptes</a>
        <h1 class="h3 mt-3 mb-1">Modifier le compte</h1>
        <p class="text-muted mb-0">{{ $user->name }} · {{ $user->email }}</p>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('users._form', ['editing' => true])
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>Enregistrer les modifications</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
