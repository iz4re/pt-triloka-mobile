<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negotiation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'sender_id',
        'sender_type',
        'message',
        'counter_amount',
        'status',
    ];

    protected $casts = [
        'counter_amount' => 'decimal:2',
    ];

    // Relationships
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Helper methods
    public function isFromClient()
    {
        return $this->sender_type === 'client';
    }

    public function isFromAdmin()
    {
        return $this->sender_type === 'admin';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }
}
