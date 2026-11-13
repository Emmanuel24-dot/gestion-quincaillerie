<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnItem extends Model
{
    protected $fillable = ['sale_return_id', 'sale_item_id', 'quantite', 'prix_unitaire', 'total_ligne'];

    protected function casts(): array
    {
        return ['quantite' => 'decimal:3', 'prix_unitaire' => 'decimal:2', 'total_ligne' => 'decimal:2'];
    }

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }
}
