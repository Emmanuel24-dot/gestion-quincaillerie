<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('type', 30);
            $table->decimal('quantite', 12, 3);
            $table->decimal('stock_avant', 12, 3);
            $table->decimal('stock_apres', 12, 3);
            $table->string('motif', 150);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['type', 'created_at']);
        });

        DB::table('products')
            ->where('quantite_stock', '>', 0)
            ->orderBy('id')
            ->chunkById(500, function ($products): void {
                foreach ($products as $product) {
                    DB::table('stock_movements')->insert([
                        'product_id' => $product->id,
                        'type' => 'stock_initial',
                        'quantite' => $product->quantite_stock,
                        'stock_avant' => 0,
                        'stock_apres' => $product->quantite_stock,
                        'motif' => 'Stock initial (reprise)',
                        'notes' => null,
                        'created_at' => $product->created_at ?? now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
