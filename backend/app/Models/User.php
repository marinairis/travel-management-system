<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\PasswordReset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isRequester(): bool
    {
        return $this->role === 'requester';
    }

    public function isApprover(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function travelRequests()
    {
        return $this->hasMany(TravelRequest::class);
    }

    public function approvedTravelRequests()
    {
        return $this->hasMany(TravelRequest::class, 'approved_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new PasswordReset($token));
    }
}
