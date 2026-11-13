@extends('layouts.app')

@section('title', 'Nouvelle unité')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('unites.index') }}" class="back-link"><i class="bi bi-arrow-left me-1"></i>Retour aux unités</a>
        <h1 class="h3 mt-2 mb-1">Nouvelle unité</h1>
        <p class="text-muted mb-0">Définissez un nom clair et un symbole court pour vos produits.</p>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">@include('units._form')</div>
    </div>
</div>
@endsection
