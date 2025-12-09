<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_number',
        'amount',
        'payment_date',
        'payment_method',
        'notes',
        'proof_image',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Related invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * User who created this payment
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Auto-generate payment number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_number) {
                $payment->payment_number = self::generatePaymentNumber();
            }
        });

        static::created(function ($payment) {
            // Update invoice status after payment
            $invoice = $payment->invoice;
            $totalPaid = $invoice->payments()->sum('amount');
            
            if ($totalPaid >= $invoice->total) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
        });
    }

    /**
     * Generate unique payment number
     */
    public static function generatePaymentNumber(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'PAY-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
