@extends('layouts.app')

@section('title', 'Nouveau produit')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('produits.index') }}" class="back-link"><i class="bi bi-arrow-left me-1"></i>Retour au catalogue</a>
        <h1 class="h3 mt-2 mb-1">Nouveau produit</h1>
        <p class="text-muted mb-0">Renseignez les informations du produit et son niveau de stock initial.</p>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            @include('products._form')
        </div>
    </div>
</div>
@endsection
