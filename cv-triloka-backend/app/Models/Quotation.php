<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_request_id',  // FIXED: was request_id
        'quotation_number',
        'quotation_date',
        'version',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'valid_until',
        'status',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_until' => 'date',
    ];

    // Auto-generate quotation number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $date = now()->format('Ymd');
                $count = self::whereDate('created_at', today())->count() + 1;
                $quotation->quotation_number = 'QUO-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
            
            // Set default valid_until to 14 days from now
            if (empty($quotation->valid_until)) {
                $quotation->valid_until = now()->addDays(14);
            }
        });
    }

    // Relationships
    public function projectRequest()
    {
        return $this->belongsTo(ProjectRequest::class, 'project_request_id');  // FIXED
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
    }

    public function negotiations()
    {
        return $this->hasMany(Negotiation::class, 'quotation_id');
    }

    // Helper methods
    public function calculateTotal()
    {
        $this->subtotal = $this->items()->sum('subtotal');
        $this->total = $this->subtotal + $this->tax - $this->discount;
        $this->save();
    }

    public function isExpired()
    {
        return $this->valid_until < now();
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function canNegotiate()
    {
        return in_array($this->status, ['sent', 'revised']) && !$this->isExpired();
    }
}
