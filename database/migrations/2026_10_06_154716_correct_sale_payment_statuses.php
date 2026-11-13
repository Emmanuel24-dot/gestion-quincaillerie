<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('sales')
            ->where('statut', '!=', 'annulee')
            ->update([
                'statut' => DB::raw("CASE WHEN montant_paye >= total THEN 'payee' WHEN montant_paye > 0 THEN 'partiellement_payee' ELSE 'impayee' END"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sales')
            ->whereIn('statut', ['payee', 'partiellement_payee', 'impayee'])
            ->update(['statut' => 'payee']);
    }
};
