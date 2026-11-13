<?php

namespace App\Models;

use Database\Factories\PurchaseItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    /** @use HasFactory<PurchaseItemFactory> */
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'reference_produit',
        'nom_produit',
        'unite_symbole',
        'quantite',
        'prix_unitaire',
        'total_ligne',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'decimal:3',
            'prix_unitaire' => 'decimal:2',
            'total_ligne' => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
