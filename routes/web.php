<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\FirstAccountController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalePaymentController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(User::query()->exists() ? 'login' : 'setup'));
Route::get('/connexion', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/connexion', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
Route::middleware('guest')->group(function (): void {
    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'store'])->middleware('throttle:password-reset')->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'update'])->middleware('throttle:password-reset')->name('password.update');
});
Route::get('/configuration-initiale', [FirstAccountController::class, 'create'])->name('setup');
Route::post('/configuration-initiale', [FirstAccountController::class, 'store'])->middleware('throttle:login')->name('setup.store');

Route::middleware('auth')->group(function (): void {
    Route::post('/deconnexion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/clients', [CustomerController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [CustomerController::class, 'create'])->name('clients.create');
    Route::post('/clients', [CustomerController::class, 'store'])->name('clients.store');
    Route::get('/clients/{customer}', [CustomerController::class, 'show'])->name('clients.show');
    Route::get('/produits', [ProductController::class, 'index'])->name('produits.index');
    Route::get('/stock', StockController::class)->name('stock.index');
    Route::resource('ventes', SaleController::class)->parameters(['ventes' => 'sale'])->only(['index', 'create', 'store', 'show']);
    Route::get('/factures', [InvoiceController::class, 'index'])->name('factures.index');
    Route::get('/factures/{invoice}', [InvoiceController::class, 'show'])->name('factures.show');
    Route::post('/ventes/{sale}/facture', [InvoiceController::class, 'store'])->name('factures.store');
    Route::post('/ventes/{sale}/paiements', [SalePaymentController::class, 'store'])->name('ventes.paiements.store');

    Route::middleware('admin')->group(function (): void {
        Route::get('/statistiques', [DashboardController::class, 'statistiques'])->name('statistiques');
        Route::get('/rapports/export', ReportExportController::class)->name('rapports.export');
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('fournisseurs', SupplierController::class)->parameters(['fournisseurs' => 'supplier']);
        Route::resource('produits', ProductController::class)->parameters(['produits' => 'product'])->except(['index', 'show']);
        Route::resource('unites', UnitController::class)->parameters(['unites' => 'unit'])->except(['show']);
        Route::get('/mouvements-stock', [StockMovementController::class, 'index'])->name('mouvements-stock.index');
        Route::get('/mouvements-stock/create', [StockMovementController::class, 'create'])->name('mouvements-stock.create');
        Route::post('/mouvements-stock', [StockMovementController::class, 'store'])->name('mouvements-stock.store');
        Route::post('/produits/{product}/inventaire', [StockMovementController::class, 'inventory'])->name('produits.inventaire');
        Route::resource('achats', PurchaseController::class)->parameters(['achats' => 'purchase'])->only(['index', 'create', 'store', 'show']);
        Route::post('/achats/{purchase}/paiements', [PurchasePaymentController::class, 'store'])->name('achats.paiements.store');
        Route::post('/ventes/{sale}/retours', [SaleReturnController::class, 'store'])->name('ventes.retours.store');
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/clients/{customer}/edit', [CustomerController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{customer}', [CustomerController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{customer}', [CustomerController::class, 'destroy'])->name('clients.destroy');
    });
});
