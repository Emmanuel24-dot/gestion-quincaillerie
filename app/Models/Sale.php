<?php

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    public const STATUS_PAID = 'payee';

    public const STATUS_PARTIALLY_PAID = 'partiellement_payee';

    public const STATUS_UNPAID = 'impayee';

    public const STATUS_CANCELLED = 'annulee';

    public const REPORTABLE_STATUSES = [
        self::STATUS_PAID,
        self::STATUS_PARTIALLY_PAID,
        self::STATUS_UNPAID,
    ];

    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    protected $fillable = [
        'numero',
        'date_vente',
        'client_nom',
        'customer_id',
        'moyen_paiement',
        'statut',
        'sous_total',
        'total',
        'montant_recu',
        'montant_paye',
        'monnaie_rendue',
    ];

    protected function casts(): array
    {
        return [
            'date_vente' => 'datetime',
            'sous_total' => 'decimal:2',
            'total' => 'decimal:2',
            'montant_recu' => 'decimal:2',
            'montant_paye' => 'decimal:2',
            'monnaie_rendue' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class)->latest('date_paiement');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            self::STATUS_PAID => 'Payée',
            self::STATUS_PARTIALLY_PAID => 'Partiellement payée',
            self::STATUS_UNPAID => 'Impayée',
            self::STATUS_CANCELLED => 'Annulée',
            default => 'Inconnu',
        };
    }

    public function getStatutBadgeClassAttribute(): string
    {
        return match ($this->statut) {
            self::STATUS_PAID => 'text-bg-success',
            self::STATUS_PARTIALLY_PAID => 'text-bg-warning',
            self::STATUS_UNPAID, self::STATUS_CANCELLED => 'text-bg-secondary',
            default => 'text-bg-secondary',
        };
    }
}
