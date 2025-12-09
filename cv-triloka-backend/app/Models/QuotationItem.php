<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'item_name',
        'category',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Auto-calculate subtotal
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($item) {
            $item->subtotal = $item->quantity * $item->unit_price;
        });

        static::saved(function ($item) {
            // Recalculate quotation total
            $item->quotation->calculateTotal();
        });

        static::deleted(function ($item) {
            // Recalculate quotation total
            $item->quotation->calculateTotal();
        });
    }

    // Relationships
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }
}
