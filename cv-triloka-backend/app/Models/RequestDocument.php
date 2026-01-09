<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RequestDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_request_id',  // Fixed: was request_id
        'document_type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'description',
        'verification_status',
        'verification_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function projectRequest()
    {
        return $this->belongsTo(ProjectRequest::class, 'project_request_id');  // Fixed: was request_id
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Helper methods
    public function isPending()
    {
        return $this->verification_status === 'pending';
    }

    public function isVerified()
    {
        return $this->verification_status === 'verified';
    }

    public function isRejected()
    {
        return $this->verification_status === 'rejected';
    }

    public function verify(User $verifier)
    {
        $this->update([
            'verification_status' => 'verified',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);
    }

    public function reject(User $verifier)
    {
        $this->update([
            'verification_status' => 'rejected',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);
    }

    public function getFileUrl()
    {
        return Storage::url($this->file_path);
    }

   // Delete file when document is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }
}
