<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePayment extends Model
{
    protected $fillable = ['purchase_id', 'montant', 'moyen_paiement', 'date_paiement', 'notes'];

    protected function casts(): array
    {
        return ['montant' => 'decimal:2', 'date_paiement' => 'datetime'];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
