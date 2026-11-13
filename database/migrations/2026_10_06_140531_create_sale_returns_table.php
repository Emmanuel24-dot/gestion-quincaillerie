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
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->string('numero', 60)->unique();
            $table->dateTime('date_retour');
            $table->decimal('total', 12, 2);
            $table->string('moyen_remboursement', 30);
            $table->string('motif', 255);
            $table->timestamps();

            $table->index(['sale_id', 'date_retour']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_returns');
    }
};
