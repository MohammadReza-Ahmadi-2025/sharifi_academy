<?php

namespace App\Modules\Auth\Models;

use App\Modules\Auth\Enums\OtpStatus;
use Illuminate\Database\Eloquent\Model;

class MobileOtp extends Model
{
    protected $fillable = [
        'mobile',
        'type',
        'code_hash',
        'ip_address',
        'user_agent',
        'attempts',
        'max_attempts',
        'status',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OtpStatus::class,
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    public function isPending(): bool
    {
        return $this->status === OtpStatus::Pending && $this->expires_at->isFuture();
    }

    public function hasAttemptsRemaining(): bool
    {
        return $this->attempts < $this->max_attempts;
    }
}
