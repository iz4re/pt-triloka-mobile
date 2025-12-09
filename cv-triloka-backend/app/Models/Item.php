<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'description',
        'unit',
        'stock_quantity',
        'min_stock_threshold',
        'unit_price',
        'is_active',
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:2',
        'min_stock_threshold' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Invoice items using this item
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Check if stock is low
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_threshold;
    }

    /**
     * Scope: Get only low stock items
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= min_stock_threshold')
                     ->where('is_active', true);
    }

    /**
     * Scope: Get only active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
