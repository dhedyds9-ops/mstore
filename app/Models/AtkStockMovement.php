<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkStockMovement extends Model
{
    protected $table = 'atk_stock_movements';

    protected $fillable = [
        'atk_product_id',
        'type',
        'quantity',
        'unit_price',
        'total_cost',
        'supplier_id',
        'invoice_number',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(AtkProduct::class, 'atk_product_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(AtkSupplier::class, 'supplier_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
