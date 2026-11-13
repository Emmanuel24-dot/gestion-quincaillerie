@extends('layouts.app')

@section('title', 'Ajouter un compte')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a class="back-link" href="{{ route('users.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux comptes</a>
        <h1 class="h3 mt-3 mb-1">Nouvel utilisateur</h1>
        <p class="text-muted mb-0">Créez un accès pour un vendeur ou un administrateur.</p>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('users._form')
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>Créer le compte</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
