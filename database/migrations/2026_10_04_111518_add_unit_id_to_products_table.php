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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('category_id')->constrained('units')->restrictOnDelete();
        });

        $legacyProducts = DB::table('products')->select('id', 'unite')->get();

        foreach ($legacyProducts as $legacyProduct) {
            $label = trim((string) ($legacyProduct->unite ?: 'pièce'));
            $unitId = DB::table('units')->where('symbole', $label)->value('id');

            if (! $unitId) {
                $unitId = DB::table('units')->insertGetId([
                    'nom' => $label,
                    'symbole' => $label,
                    'statut' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('products')->where('id', $legacyProduct->id)->update(['unit_id' => $unitId]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable(false)->change();
            $table->dropColumn('unite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unite', 30)->default('pièce')->after('category_id');
        });

        $products = DB::table('products')->select('id', 'unit_id')->get();

        foreach ($products as $product) {
            $symbol = DB::table('units')->where('id', $product->unit_id)->value('symbole') ?: 'pièce';

            DB::table('products')->where('id', $product->id)->update(['unite' => $symbol]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
