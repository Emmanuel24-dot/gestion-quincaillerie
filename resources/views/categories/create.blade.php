@extends('layouts.app')

@section('title', 'Nouvelle catégorie')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('categories.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux catégories</a>
        <h1 class="h3 mt-3 mb-1">Nouvelle catégorie</h1>
        <p class="text-muted mb-0">Regroupez vos produits pour les retrouver plus facilement.</p>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">@include('categories._form')</div>
    </div>
</div>
@endsection
