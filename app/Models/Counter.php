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
        'service_types' => 'array', // Ubah casting ke array
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

    // Mutator untuk service_types
    public function setServiceTypesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['service_types'] = json_encode($value);
        } elseif (is_string($value) && json_decode($value) !== null) {
            // Jika sudah JSON string, langsung simpan
            $this->attributes['service_types'] = $value;
        } else {
            // Jika null atau invalid, simpan sebagai empty array
            $this->attributes['service_types'] = json_encode([]);
        }
    }

    // Accessor untuk service_types
    public function getServiceTypesAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }
        
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

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
        return match($this->status) {
            'ACTIVE' => 'Aktif',
            'INACTIVE' => 'Tidak Aktif',
            'MAINTENANCE' => 'Perbaikan',
            default => $this->status
        };
    }

    public function getTypeNameAttribute()
    {
        return match($this->type) {
            'REGULAR' => 'Regular',
            'VIP' => 'VIP',
            'EXPRESS' => 'Express',
            default => $this->type
        };
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

    // Helper untuk mendapatkan service types dengan aman
    public function getServiceTypesSafe()
    {
        $types = $this->service_types;
        
        // Jika sudah array, return langsung
        if (is_array($types)) {
            return $types;
        }
        
        // Jika string, coba decode
        if (is_string($types)) {
            $decoded = json_decode($types, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        // Default empty array
        return [];
    }
}