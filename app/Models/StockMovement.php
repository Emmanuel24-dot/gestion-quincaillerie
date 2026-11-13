<?php

namespace App\Models;

use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    /** @use HasFactory<StockMovementFactory> */
    use HasFactory;

    public const TYPE_LABELS = [
        'stock_initial' => 'Stock initial',
        'entree' => 'Entrée',
        'sortie' => 'Sortie',
        'ajustement_positif' => 'Ajustement à la hausse',
        'ajustement_negatif' => 'Ajustement à la baisse',
    ];

    protected $fillable = [
        'product_id',
        'type',
        'quantite',
        'stock_avant',
        'stock_apres',
        'motif',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'decimal:3',
            'stock_avant' => 'decimal:3',
            'stock_apres' => 'decimal:3',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
