<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['nom' => 'Pièce', 'symbole' => 'pce'],
            ['nom' => 'Kilogramme', 'symbole' => 'kg'],
            ['nom' => 'Litre', 'symbole' => 'L'],
            ['nom' => 'Mètre', 'symbole' => 'm'],
            ['nom' => 'Sac', 'symbole' => 'sac'],
            ['nom' => 'Boîte', 'symbole' => 'bte'],
        ];

        foreach ($units as $unitData) {
            $unit = Unit::query()
                ->where('symbole', $unitData['symbole'])
                ->orWhereRaw('LOWER(nom) = ?', [mb_strtolower($unitData['nom'])])
                ->first();

            if ($unit) {
                $unit->update($unitData + ['statut' => true]);

                continue;
            }

            Unit::create($unitData + ['statut' => true]);
        }
    }
}
