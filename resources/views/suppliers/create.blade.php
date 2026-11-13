@extends('layouts.app')

@section('title', 'Nouveau fournisseur')

@section('content')
<div class="container-fluid"><div class="mb-4"><a href="{{ route('fournisseurs.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux fournisseurs</a><h1 class="h3 mt-3 mb-1">Nouveau fournisseur</h1><p class="text-muted mb-0">Ajoutez les coordonnées de votre partenaire commercial.</p></div><div class="card"><div class="card-body p-4">@include('suppliers._form')</div></div></div>
@endsection
