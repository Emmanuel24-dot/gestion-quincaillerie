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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 60)->unique();
            $table->dateTime('date_vente');
            $table->string('client_nom', 150)->nullable();
            $table->string('moyen_paiement', 30);
            $table->string('statut', 20)->default('payee');
            $table->decimal('sous_total', 12, 2);
            $table->decimal('total', 12, 2);
            $table->decimal('montant_recu', 12, 2);
            $table->decimal('monnaie_rendue', 12, 2);
            $table->timestamps();

            $table->index('date_vente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
