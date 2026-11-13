<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sale::query()
            ->where('statut', 'payee')
            ->whereDoesntHave('invoice')
            ->orderBy('id')
            ->each(function (Sale $sale): void {
                Invoice::firstOrCreate(
                    ['sale_id' => $sale->id],
                    [
                        'numero' => sprintf('FAC-%s-%06d', now()->format('Ymd'), $sale->id),
                        'date_emission' => now(),
                        'statut' => 'emise',
                    ],
                );
            });
    }
}
