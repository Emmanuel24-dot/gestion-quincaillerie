<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 60)->unique();
            $table->string('nom', 150);
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('unite', 30)->default('pièce');
            $table->decimal('prix_achat', 12, 2)->default(0);
            $table->decimal('prix_vente', 12, 2)->default(0);
            $table->decimal('quantite_stock', 12, 3)->default(0);
            $table->decimal('seuil_alerte', 12, 3)->default(0);
            $table->boolean('statut')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
