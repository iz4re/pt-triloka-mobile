<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'company_name',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is klien
     */
    public function isKlien(): bool
    {
        return $this->role === 'klien';
    }

    /**
     * Invoices created by this user (as klien)
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'klien_id');
    }

    /**
     * Invoices created by this admin
     */
    public function createdInvoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    /**
     * Payments made by this user
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    /**
     * Notifications for this user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Activity logs for this user
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Project requests made by this klien
     */
    public function projectRequests()
    {
        return $this->hasMany(ProjectRequest::class, 'klien_id');
    }

    /**
     * Quotations created by this admin
     */
    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'created_by');
    }
}
