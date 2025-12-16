<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'klien_id',
        'quotation_id',
        'created_by',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax',
        'discount',
        'total',
        'status',
        'notes',
        'paid_at',
        'va_number',
        'va_bank',
        'va_expires_at',
        'invoice_type',
        'parent_invoice_id',
        'is_survey_fee_applied',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'va_expires_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Client who owns this invoice
     */
    public function klien()
    {
        return $this->belongsTo(User::class, 'klien_id');
    }

    /**
     * Admin who created this invoice
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Invoice items
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Payments for this invoice
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Calculate remaining balance
     */
    public function remainingBalance(): float
    {
        $totalPaid = $this->payments()->sum('amount');
        return $this->total - $totalPaid;
    }

    /**
     * Auto-generate invoice number and VA number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            // Generate invoice number
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            
            // Generate Virtual Account number
            if (!$invoice->va_number) {
                $invoice->va_number = self::generateVANumber();
                $invoice->va_bank = 'BCA';
                $invoice->va_expires_at = now()->addDays(7);
            }
        });
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'INV-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Virtual Account number (Mock)
     * Format: 8808 + invoice_id + random digits
     * Example: 8808 0001 234567
     */
    public static function generateVANumber(): string
    {
        // Get next ID (approximate)
        $nextId = self::max('id') + 1;
        
        // Generate random 6 digits
        $random = rand(100000, 999999);
        
        // Format: 8808 + 4-digit ID + 6-digit random
        // Example: 8808000123456 7 (displayed as 8808 0001 234567)
        return '8808' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . $random;
    }

    /**
     * Check if this is a survey invoice
     */
    public function isSurveyInvoice(): bool
    {
        return $this->invoice_type === 'survey';
    }

    /**
     * Get the parent invoice (for project invoices that have survey fee)
     */
    public function parentInvoice()
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    /**
     * Get the child invoice (for survey invoices)
     */
    public function childInvoice()
    {
        return $this->hasOne(Invoice::class, 'parent_invoice_id');
    }

    /**
     * Get standard survey fee amount
     */
    public static function getSurveyFeeAmount(): float
    {
        return 500000; // Rp 500k
    }

    /**
     * Apply survey fee discount to this invoice
     */
    public function applySurveyFeeDiscount(Invoice $surveyInvoice): void
    {
        if ($this->isSurveyInvoice()) {
            return; // Cannot apply to survey invoice itself
        }

        if ($surveyInvoice->status !== 'paid') {
            return; // Survey must be paid first
        }

        $this->parent_invoice_id = $surveyInvoice->id;
        $this->is_survey_fee_applied = true;
        $this->discount = ($this->discount ?? 0) + self::getSurveyFeeAmount();
        $this->total = $this->subtotal + $this->tax - $this->discount;
        $this->save();
    }
}
