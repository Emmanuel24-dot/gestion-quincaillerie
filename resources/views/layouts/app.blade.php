<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f4b70">
    <title>@yield('title', 'Gestion quincaillerie')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/quincapro-icon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/quincapro-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    @vite(['resources/css/vendor.css', 'resources/css/icons.css', 'resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="admin-shell">
    <div class="admin-backdrop" data-sidebar-backdrop></div>

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="brand-link" aria-label="QuincaPro, tableau de bord">
                <span class="brand-mark"><img src="{{ asset('images/quincapro-icon.svg') }}" alt=""></span>
                <span class="brand-copy">
                    <strong>Quinca<span>Pro</span></strong>
                    <small>Gestion commerciale</small>
                </span>
            </a>
            <button class="sidebar-close" type="button" data-sidebar-close aria-label="Fermer le menu">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        @php($catalogIsActive = request()->routeIs('produits.*', 'categories.*', 'unites.*', 'stock.*', 'mouvements-stock.*'))
        @php($partnersAreActive = request()->routeIs('clients.*', 'fournisseurs.*'))
        @php($isAdmin = auth()->user()?->role === 'admin')
        @php($purchasesAreActive = request()->routeIs('achats.*'))
        @php($reportsIsActive = request()->routeIs('statistiques'))

        <nav class="sidebar-nav" aria-label="Navigation principale">
            <a href="{{ route('dashboard') }}" class="sidebar-link sidebar-link-main {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard" @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i><span class="nav-text">Dashboard</span>
            </a>

            <div class="sidebar-group {{ request()->routeIs('ventes.*') ? 'is-open' : '' }}" data-sidebar-group>
                <button class="nav-section-label {{ request()->routeIs('ventes.*') ? 'is-expanded' : '' }}" type="button" data-sidebar-section aria-controls="sales-submenu" aria-expanded="{{ request()->routeIs('ventes.*') ? 'true' : 'false' }}" title="Ventes">
                    <i class="section-icon bi bi-cart-check-fill" aria-hidden="true"></i><span class="nav-text">Ventes</span><i class="section-chevron bi bi-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="sidebar-submenu" id="sales-submenu" data-sidebar-submenu @if (!request()->routeIs('ventes.*')) hidden @endif>
                    <a href="{{ route('ventes.create') }}" class="sidebar-link {{ request()->routeIs('ventes.create', 'ventes.store') ? 'active' : '' }}" title="Nouvelle vente" @if (request()->routeIs('ventes.create')) aria-current="page" @endif>
                        <i class="bi bi-cart-plus" aria-hidden="true"></i><span class="nav-text">Nouvelle vente</span>
                    </a>
                    <a href="{{ route('ventes.index') }}" class="sidebar-link {{ request()->routeIs('ventes.index') ? 'active' : '' }}" title="Historique des ventes" @if (request()->routeIs('ventes.index')) aria-current="page" @endif>
                        <i class="bi bi-clock-history" aria-hidden="true"></i><span class="nav-text">Historique des ventes</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-group {{ $partnersAreActive ? 'is-open' : '' }}" data-sidebar-group>
                <button class="nav-section-label {{ $partnersAreActive ? 'is-expanded' : '' }}" type="button" data-sidebar-section aria-controls="partners-submenu" aria-expanded="{{ $partnersAreActive ? 'true' : 'false' }}" title="Partenaires">
                    <i class="section-icon bi bi-people-fill" aria-hidden="true"></i><span class="nav-text">Partenaires</span><i class="section-chevron bi bi-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="sidebar-submenu" id="partners-submenu" data-sidebar-submenu @if (!$partnersAreActive) hidden @endif>
                    <a href="{{ route('clients.index') }}" class="sidebar-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" title="Clients" @if (request()->routeIs('clients.*')) aria-current="page" @endif>
                        <i class="bi bi-person-vcard" aria-hidden="true"></i><span class="nav-text">Clients</span>
                    </a>
                    @if ($isAdmin)<a href="{{ route('fournisseurs.index') }}" class="sidebar-link {{ request()->routeIs('fournisseurs.*') ? 'active' : '' }}" title="Fournisseurs" @if (request()->routeIs('fournisseurs.*')) aria-current="page" @endif>
                        <i class="bi bi-truck" aria-hidden="true"></i><span class="nav-text">Fournisseurs</span>
                    </a>@endif
                </div>
            </div>

            @if ($isAdmin)<div class="sidebar-group {{ $purchasesAreActive ? 'is-open' : '' }}" data-sidebar-group>
                <button class="nav-section-label {{ $purchasesAreActive ? 'is-expanded' : '' }}" type="button" data-sidebar-section aria-controls="purchases-submenu" aria-expanded="{{ $purchasesAreActive ? 'true' : 'false' }}" title="Achats">
                    <i class="section-icon bi bi-bag-check-fill" aria-hidden="true"></i><span class="nav-text">Achats</span><i class="section-chevron bi bi-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="sidebar-submenu" id="purchases-submenu" data-sidebar-submenu @if (!$purchasesAreActive) hidden @endif>
                    <a href="{{ route('achats.create') }}" class="sidebar-link {{ request()->routeIs('achats.create') ? 'active' : '' }}" title="Nouvel achat" @if (request()->routeIs('achats.create')) aria-current="page" @endif>
                        <i class="bi bi-plus-circle" aria-hidden="true"></i><span class="nav-text">Nouvel achat</span>
                    </a>
                    <a href="{{ route('achats.index') }}" class="sidebar-link {{ request()->routeIs('achats.index', 'achats.show') ? 'active' : '' }}" title="Historique des achats" @if (request()->routeIs('achats.index', 'achats.show')) aria-current="page" @endif>
                        <i class="bi bi-clock-history" aria-hidden="true"></i><span class="nav-text">Historique des achats</span>
                    </a>
                </div>
            </div>@endif

            <div class="sidebar-group {{ request()->routeIs('factures.*') ? 'is-open' : '' }}" data-sidebar-group>
                <button class="nav-section-label {{ request()->routeIs('factures.*') ? 'is-expanded' : '' }}" type="button" data-sidebar-section aria-controls="invoices-submenu" aria-expanded="{{ request()->routeIs('factures.*') ? 'true' : 'false' }}" title="Facturation">
                    <i class="section-icon bi bi-receipt-cutoff" aria-hidden="true"></i><span class="nav-text">Facturation</span><i class="section-chevron bi bi-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="sidebar-submenu" id="invoices-submenu" data-sidebar-submenu @if (!request()->routeIs('factures.*')) hidden @endif>
                    <a href="{{ route('factures.index') }}" class="sidebar-link {{ request()->routeIs('factures.*') ? 'active' : '' }}" title="Factures" @if (request()->routeIs('factures.index')) aria-current="page" @endif>
                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i><span class="nav-text">Factures</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-group {{ $catalogIsActive ? 'is-open' : '' }}" data-sidebar-group>
                <button class="nav-section-label {{ $catalogIsActive ? 'is-expanded' : '' }}" type="button" data-sidebar-section aria-controls="catalog-submenu" aria-expanded="{{ $catalogIsActive ? 'true' : 'false' }}" title="Gestion des produits">
                    <i class="section-icon bi bi-box-seam-fill" aria-hidden="true"></i><span class="nav-text">Gestion des produits</span><i class="section-chevron bi bi-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="sidebar-submenu" id="catalog-submenu" data-sidebar-submenu @if (!$catalogIsActive) hidden @endif>
                    <a href="{{ route('produits.index') }}" class="sidebar-link {{ request()->routeIs('produits.*') ? 'active' : '' }}" title="Produits" @if (request()->routeIs('produits.*')) aria-current="page" @endif>
                        <i class="bi bi-box-seam" aria-hidden="true"></i><span class="nav-text">Produits</span>
                    </a>
                    @if ($isAdmin)<a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" title="Catégories" @if (request()->routeIs('categories.*')) aria-current="page" @endif>
                        <i class="bi bi-tags" aria-hidden="true"></i><span class="nav-text">Catégories</span>
                    </a>
                    <a href="{{ route('unites.index') }}" class="sidebar-link {{ request()->routeIs('unites.*') ? 'active' : '' }}" title="Unités" @if (request()->routeIs('unites.*')) aria-current="page" @endif>
                        <i class="bi bi-rulers" aria-hidden="true"></i><span class="nav-text">Unités</span>
                    </a>@endif
                    <a href="{{ route('stock.index') }}" class="sidebar-link {{ request()->routeIs('stock.*') ? 'active' : '' }}" title="Stock" @if (request()->routeIs('stock.*')) aria-current="page" @endif>
                        <i class="bi bi-boxes" aria-hidden="true"></i><span class="nav-text">Stock</span>
                    </a>
                    @if ($isAdmin)<a href="{{ route('mouvements-stock.index') }}" class="sidebar-link {{ request()->routeIs('mouvements-stock.*') ? 'active' : '' }}" title="Mouvements de stock" @if (request()->routeIs('mouvements-stock.*')) aria-current="page" @endif>
                        <i class="bi bi-arrow-left-right" aria-hidden="true"></i><span class="nav-text">Mouvements de stock</span>
                    </a>@endif
                </div>
            </div>

            @if ($isAdmin)<div class="sidebar-group {{ $reportsIsActive ? 'is-open' : '' }}" data-sidebar-group>
                <button class="nav-section-label {{ $reportsIsActive ? 'is-expanded' : '' }}" type="button" data-sidebar-section aria-controls="reports-submenu" aria-expanded="{{ $reportsIsActive ? 'true' : 'false' }}" title="Rapports">
                    <i class="section-icon bi bi-bar-chart-line-fill" aria-hidden="true"></i><span class="nav-text">Rapports</span><i class="section-chevron bi bi-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="sidebar-submenu" id="reports-submenu" data-sidebar-submenu @if (!$reportsIsActive) hidden @endif>
                    <a href="{{ route('statistiques') }}" class="sidebar-link {{ $reportsIsActive ? 'active' : '' }}" title="Rapport des ventes" @if ($reportsIsActive) aria-current="page" @endif>
                        <i class="bi bi-graph-up" aria-hidden="true"></i><span class="nav-text">Rapport des ventes</span>
                    </a>
                </div>
            </div>@endif

            @if ($isAdmin)
                <a href="{{ route('users.index') }}" class="sidebar-link sidebar-link-main {{ request()->routeIs('users.*') ? 'active' : '' }}" title="Gestion des utilisateurs" @if (request()->routeIs('users.*')) aria-current="page" @endif>
                    <i class="bi bi-person-gear" aria-hidden="true"></i><span class="nav-text">Gestion des utilisateurs</span>
                </a>
            @endif
        </nav>

    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="menu-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-label="Réduire le menu" aria-expanded="true">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-context">
                <span class="context-title">@yield('title', 'Tableau de bord')</span>
            </div>
            <div class="topbar-actions">
                <button class="pwa-install-button" type="button" data-pwa-install aria-label="Installer QuincaPro" hidden>
                    <i class="bi bi-download" aria-hidden="true"></i><span>Installer</span>
                </button>
                <button class="user-menu" type="button" data-user-menu aria-expanded="false" aria-haspopup="true">
                    <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    <span class="user-details"><strong>{{ auth()->user()->name }}</strong><small>{{ $isAdmin ? 'Administrateur' : 'Vendeur' }}</small></span>
                    <i class="bi bi-chevron-down user-chevron"></i>
                </button>
                <div class="user-dropdown" data-user-dropdown>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="user-dropdown-link user-logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="admin-content">
            @yield('content')
        </main>

        <footer class="admin-footer">
            <span>QuincaPro &middot; Gestion de votre activité</span>
            <span>{{ now()->format('d/m/Y') }}</span>
        </footer>
    </div>

</body>

</html>
