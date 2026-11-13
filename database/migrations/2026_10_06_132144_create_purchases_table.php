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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('numero', 60)->unique();
            $table->dateTime('date_achat');
            $table->string('statut', 20)->default('receptionnee');
            $table->decimal('sous_total', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();

            $table->index('date_achat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
