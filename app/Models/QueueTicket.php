<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QueueTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'service_id',
        'counter_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_id_card',
        'pickup_location',
        'destination',
        'distance_km',
        'passenger_count',
        'luggage_count',
        'estimated_price',
        'final_price',
        'queue_number',
        'status',
        'called_at',
        'serving_at',
        'completed_at',
        'cancelled_at',
        'called_by',
        'served_by',
        'driver_id',
        'telegram_chat_id',
        'telegram_message_id',
        'notes',
        'metadata',
        'priority_level',
        'is_vip'
    ];

    protected $casts = [
        'estimated_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'called_at' => 'datetime',
        'serving_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
        'is_vip' => 'boolean'
    ];

    // Ticket Statuses
    public const STATUSES = [
        'WAITING' => 'Menunggu',
        'CALLED' => 'Dipanggil',
        'SERVING' => 'Sedang Dilayani',
        'COMPLETED' => 'Selesai',
        'CANCELLED' => 'Dibatalkan',
        'NO_SHOW' => 'Tidak Datang',
        'TRANSFERRED' => 'Ditransfer'
    ];

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    public function calledBy()
    {
        return $this->belongsTo(User::class, 'called_by');
    }

    public function servedBy()
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // Helper Methods
    public function getStatusNameAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getWaitingTimeAttribute()
    {
        if ($this->called_at && $this->created_at) {
            return $this->created_at->diffInMinutes($this->called_at);
        }
        return null;
    }

    public function getServiceTimeAttribute()
    {
        if ($this->completed_at && $this->called_at) {
            return $this->called_at->diffInMinutes($this->completed_at);
        }
        return null;
    }

    public function getTotalTimeAttribute()
    {
        if ($this->completed_at && $this->created_at) {
            return $this->created_at->diffInMinutes($this->completed_at);
        }
        return null;
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'WAITING');
    }

    public function scopeCalled($query)
    {
        return $query->where('status', 'CALLED');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeByServiceType($query, $type)
    {
        return $query->whereHas('service', function($q) use ($type) {
            $q->where('type', $type);
        });
    }
}