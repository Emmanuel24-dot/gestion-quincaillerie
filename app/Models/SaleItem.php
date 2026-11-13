<?php

namespace App\Models;

use Database\Factories\SaleItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    /** @use HasFactory<SaleItemFactory> */
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'reference_produit',
        'nom_produit',
        'unite_symbole',
        'quantite',
        'prix_unitaire',
        'prix_achat_unitaire',
        'total_ligne',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'decimal:3',
            'prix_unitaire' => 'decimal:2',
            'prix_achat_unitaire' => 'decimal:2',
            'total_ligne' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
