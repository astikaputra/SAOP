<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Counter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'location',
        'ip_address',
        'current_ticket_id',
        'status',
        'type',
        'opening_time',
        'closing_time',
        'service_types',
        'is_active',
        'display_order',
        'notes'
    ];

    protected $casts = [
        'service_types' => 'array',
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'is_active' => 'boolean'
    ];

    // Counter Statuses
    public const STATUSES = [
        'ACTIVE' => 'Aktif',
        'INACTIVE' => 'Tidak Aktif',
        'MAINTENANCE' => 'Perbaikan'
    ];

    // Counter Types
    public const TYPES = [
        'REGULAR' => 'Regular',
        'VIP' => 'VIP',
        'EXPRESS' => 'Express'
    ];

    // Relationships
    public function currentTicket()
    {
        return $this->belongsTo(QueueTicket::class, 'current_ticket_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function queueTickets()
    {
        return $this->hasMany(QueueTicket::class);
    }

    // Helper Methods
    public function getStatusNameAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getTypeNameAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isOpen()
    {
        $now = now();
        $opening = now()->setTimeFromTimeString($this->opening_time);
        $closing = now()->setTimeFromTimeString($this->closing_time);
        
        return $this->is_active && 
               $this->status === 'ACTIVE' &&
               $now->between($opening, $closing);
    }
}