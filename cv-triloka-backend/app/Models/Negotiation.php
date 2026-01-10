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
        'admin_notes',
    ];

    protected $casts = [
        'counter_amount' => 'decimal:2',
    ];
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isFromClient()
    {
        return strtolower($this->sender_type) === 'klien';
    }

    public function isFromAdmin()
    {
        return strtolower($this->sender_type) === 'admin';
    }

    public function isPending()
    {
        return strtolower($this->status) === 'pending';
    }

    public function isAccepted()
    {
        return strtolower($this->status) === 'accepted';
    }

    public function isRejected()
    {
        return strtolower($this->status) === 'rejected';
    }
}
