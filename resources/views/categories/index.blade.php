@extends('layouts.app')

@section('title', 'Catégories')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Catégories</h1>
            <p class="text-muted mb-0">Gérez les catégories de produits de la quincaillerie.</p>
        </div>
        <a href="{{ route('categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nouvelle catégorie</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h2 class="h6 mb-0">Liste des catégories</h2>
            <span class="text-muted small">{{ $categories->count() }} catégorie(s)</span>
        </div>
        <div class="card-body p-0">
            @if ($categories->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-4">Nom</th><th>Description</th><th>Produits</th><th>Statut</th><th>Créée le</th><th class="text-end pe-4">Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td class="ps-4"><strong>{{ $category->nom }}</strong></td>
                                    <td>{{ $category->description ?: '—' }}</td>
                                    <td>{{ $category->products_count }}</td>
                                    <td><span class="badge {{ $category->statut ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $category->statut ? 'Active' : 'Inactive' }}</span></td>
                                    <td class="text-nowrap">{{ $category->created_at?->format('d/m/Y') }}</td>
                                    <td class="text-end pe-4 text-nowrap">
                                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" aria-label="Modifier {{ $category->nom }}"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" @disabled($category->products_count > 0) title="{{ $category->products_count > 0 ? 'Catégorie utilisée par des produits' : 'Supprimer cette catégorie' }}" aria-label="Supprimer {{ $category->nom }}"><i class="bi bi-trash" aria-hidden="true"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 px-3">
                    <i class="bi bi-tags display-6 text-muted" aria-hidden="true"></i>
                    <h3 class="h5 mt-3">Aucune catégorie</h3>
                    <p class="text-muted">Commencez par créer votre première catégorie.</p>
                    <a href="{{ route('categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Créer une catégorie</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
