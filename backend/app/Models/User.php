<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\PasswordReset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
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
        return UserRole::from($this->role) === UserRole::Admin;
    }

    public function isManager(): bool
    {
        return UserRole::from($this->role) === UserRole::Manager;
    }

    public function isRequester(): bool
    {
        return UserRole::from($this->role) === UserRole::Requester;
    }

    public function isApprover(): bool
    {
        return UserRole::from($this->role)->isApprover();
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
