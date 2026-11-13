@extends('layouts.app')

@section('title', 'Modifier un client')

@section('content')
<div class="container-fluid"><div class="mb-4"><a href="{{ route('clients.index') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i>Retour aux clients</a><h1 class="h3 mt-3 mb-1">Modifier le client</h1><p class="text-muted mb-0">{{ $customer->nom }}</p></div><div class="card"><div class="card-body p-4">@include('customers._form')</div></div></div>
@endsection
