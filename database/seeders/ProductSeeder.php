<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(UnitSeeder::class);

        $products = [
            [
                'reference' => 'BAR-001',
                'nom' => 'Barre de fer 10 mm',
                'description' => 'Barre d’armature pour béton armé.',
                'category' => 'Barre',
                'unit' => 'pce',
                'prix_achat' => 3200,
                'prix_vente' => 4000,
                'quantite_stock' => 60,
                'seuil_alerte' => 12,
            ],
            [
                'reference' => 'CMT-001',
                'nom' => 'Ciment CPJ 35 - 50 kg',
                'description' => 'Sac de ciment pour travaux courants.',
                'category' => 'Ciment',
                'unit' => 'sac',
                'prix_achat' => 4700,
                'prix_vente' => 5500,
                'quantite_stock' => 45,
                'seuil_alerte' => 10,
            ],
            [
                'reference' => 'GRV-001',
                'nom' => 'Gravier concassé 5/15',
                'description' => 'Gravier concassé conditionné en sac.',
                'category' => 'Gravier',
                'unit' => 'sac',
                'prix_achat' => 1800,
                'prix_vente' => 2500,
                'quantite_stock' => 30,
                'seuil_alerte' => 8,
            ],
            [
                'reference' => 'SAB-001',
                'nom' => 'Sable lavé fin',
                'description' => 'Sable lavé pour maçonnerie et finition.',
                'category' => 'Sable',
                'unit' => 'sac',
                'prix_achat' => 1000,
                'prix_vente' => 1500,
                'quantite_stock' => 35,
                'seuil_alerte' => 8,
            ],
            [
                'reference' => 'VIS-001',
                'nom' => 'Vis à bois 4 x 40 mm',
                'description' => 'Boîte de vis à bois multi-usage.',
                'category' => 'Vis',
                'unit' => 'bte',
                'prix_achat' => 900,
                'prix_vente' => 1400,
                'quantite_stock' => 24,
                'seuil_alerte' => 5,
            ],
            [
                'reference' => 'PE-005L',
                'nom' => 'Peinture acrylique blanche 5 L',
                'description' => 'Peinture acrylique intérieure, finition mate.',
                'category' => 'Peinture',
                'unit' => 'L',
                'prix_achat' => 15500,
                'prix_vente' => 19000,
                'quantite_stock' => 10,
                'seuil_alerte' => 3,
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::query()->where('nom', $productData['category'])->firstOrFail();
            $unit = Unit::query()->where('symbole', $productData['unit'])->firstOrFail();

            $product = Product::firstOrCreate(
                ['reference' => $productData['reference']],
                [
                    'nom' => $productData['nom'],
                    'description' => $productData['description'],
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'prix_achat' => $productData['prix_achat'],
                    'prix_vente' => $productData['prix_vente'],
                    'quantite_stock' => $productData['quantite_stock'],
                    'seuil_alerte' => $productData['seuil_alerte'],
                    'statut' => true,
                ],
            );

            if ($product->wasRecentlyCreated && (float) $product->quantite_stock > 0) {
                $product->stockMovements()->create([
                    'type' => 'stock_initial',
                    'quantite' => $product->quantite_stock,
                    'stock_avant' => 0,
                    'stock_apres' => $product->quantite_stock,
                    'motif' => 'Stock initial',
                ]);
            }
        }
    }
}
