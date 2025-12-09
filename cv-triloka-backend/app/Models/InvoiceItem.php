<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_id',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Parent invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Related item from inventory
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Auto-calculate subtotal
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($invoiceItem) {
            $invoiceItem->subtotal = $invoiceItem->quantity * $invoiceItem->unit_price;
        });
    }
}
