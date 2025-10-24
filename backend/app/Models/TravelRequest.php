<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'requester_name',
        'destination',
        'departure_date',
        'return_date',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected $appends = ['can_be_cancelled'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getCanBeCancelledAttribute()
    {
        return $this->status !== 'approved';
    }

    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeByDestination($query, $destination)
    {
        if ($destination) {
            return $query->where('destination', 'like', "%{$destination}%");
        }
        return $query;
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('departure_date', [$startDate, $endDate])
                ->orWhereBetween('return_date', [$startDate, $endDate])
                ->orWhereBetween('created_at', [$startDate, $endDate]);
        }
        return $query;
    }
}
