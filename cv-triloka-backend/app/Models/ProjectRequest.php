<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'user_id',
        'klien_id',
        'title',
        'type',
        'description',
        'location',
        'expected_budget',
        'expected_timeline',
        'status',
    ];

    protected $casts = [
        'expected_budget' => 'decimal:2',
    ];

    // Auto-generate request number on creation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($request) {
            if (empty($request->request_number)) {
                $date = now()->format('Ymd');
                $count = self::whereDate('created_at', today())->count() + 1;
                $request->request_number = 'REQ-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationships
    public function klien()
    {
        return $this->belongsTo(User::class, 'klien_id');  // FIXED: was user_id
    }

    public function documents()
    {
        return $this->hasMany(RequestDocument::class, 'project_request_id');  // FIXED: specify foreign key
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'project_request_id');  // FIXED
    }

    public function activeQuotation()
    {
        return $this->hasOne(Quotation::class, 'project_request_id')  // FIXED
            ->where('status', '!=', 'rejected')
            ->latest();
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isQuoted()
    {
        return $this->status === 'quoted';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isNegotiating()
    {
        return $this->status === 'negotiating';
    }
}
