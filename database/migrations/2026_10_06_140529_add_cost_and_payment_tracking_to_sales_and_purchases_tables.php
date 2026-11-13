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
        Schema::table('sale_items', function (Blueprint $table): void {
            $table->decimal('prix_achat_unitaire', 12, 2)->default(0)->after('prix_unitaire');
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->decimal('montant_paye', 12, 2)->default(0)->after('montant_recu');
        });

        Schema::table('purchases', function (Blueprint $table): void {
            $table->decimal('montant_paye', 12, 2)->default(0)->after('total');
            $table->string('moyen_paiement', 30)->nullable()->after('montant_paye');
        });

        DB::table('sales')->where('statut', 'payee')->update(['montant_paye' => DB::raw('total')]);
        DB::table('purchases')->update(['montant_paye' => DB::raw('total')]);

        Schema::create('sale_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->decimal('montant', 12, 2);
            $table->string('moyen_paiement', 30);
            $table->dateTime('date_paiement');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->index(['sale_id', 'date_paiement']);
        });

        Schema::create('purchase_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->decimal('montant', 12, 2);
            $table->string('moyen_paiement', 30);
            $table->dateTime('date_paiement');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->index(['purchase_id', 'date_paiement']);
        });

        DB::table('sales')->where('statut', 'payee')->orderBy('id')->chunkById(500, function ($sales): void {
            foreach ($sales as $sale) {
                DB::table('sale_payments')->insert([
                    'sale_id' => $sale->id,
                    'montant' => $sale->total,
                    'moyen_paiement' => $sale->moyen_paiement,
                    'date_paiement' => $sale->date_vente,
                    'notes' => 'Paiement repris de la vente.',
                    'created_at' => $sale->created_at,
                    'updated_at' => now(),
                ]);
            }
        });

        DB::table('purchases')->orderBy('id')->chunkById(500, function ($purchases): void {
            foreach ($purchases as $purchase) {
                DB::table('purchase_payments')->insert([
                    'purchase_id' => $purchase->id,
                    'montant' => $purchase->total,
                    'moyen_paiement' => 'autre',
                    'date_paiement' => $purchase->date_achat,
                    'notes' => 'Paiement repris de l’achat.',
                    'created_at' => $purchase->created_at,
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
        Schema::dropIfExists('purchase_payments');
        Schema::dropIfExists('sale_payments');
        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropColumn(['montant_paye', 'moyen_paiement']);
        });
        Schema::table('sales', function (Blueprint $table): void {
            $table->dropColumn('montant_paye');
        });
        Schema::table('sale_items', function (Blueprint $table): void {
            $table->dropColumn('prix_achat_unitaire');
        });
    }
};
