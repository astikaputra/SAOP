<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DisplayBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'type',
        'services_to_display',
        'refresh_rate',
        'show_current_calls',
        'show_waiting_queues',
        'show_statistics',
        'api_key',
        'is_active',
        'last_connected_at'
    ];

    protected $casts = [
        'services_to_display' => 'array',
        'show_current_calls' => 'boolean',
        'show_waiting_queues' => 'boolean',
        'show_statistics' => 'boolean',
        'is_active' => 'boolean',
        'last_connected_at' => 'datetime'
    ];

    // Board Types
    public const TYPES = [
        'MAIN' => 'Utama',
        'WAITING_AREA' => 'Area Tunggu',
        'VIP' => 'VIP',
        'SERVICE_SPECIFIC' => 'Spesifik Layanan'
    ];

    // Helper Methods
    public function getTypeNameAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function generateApiKey()
    {
        $this->api_key = bin2hex(random_bytes(32));
        $this->save();
        return $this->api_key;
    }

    public function updateConnection()
    {
        $this->update(['last_connected_at' => now()]);
    }
}