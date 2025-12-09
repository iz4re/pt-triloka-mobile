<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false; // Only created_at

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * User who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Static helper to log activity
     */
    public static function log(string $action, string $description, ?Model $model = null, ?User $user = null)
    {
        return self::create([
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'model' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
