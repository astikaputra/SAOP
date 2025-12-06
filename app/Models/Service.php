<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'sub_service',
        'description',
        'base_price',
        'price_per_km',
        'duration_minutes',
        'capacity',
        'is_active',
        'display_order',
        'icon'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'price_per_km' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Service Types
    public const TYPES = [
        'PICKUP_TRANSPORTASI' => 'Pickup Transportasi',
        'TRANSUP' => 'Transup',
        'SEWA_MOTOR' => 'Sewa Motor'
    ];

    // Relationships
    public function queueTickets()
    {
        return $this->hasMany(QueueTicket::class);
    }

    // Helper Methods
    public function getTypeNameAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getPriceRangeAttribute()
    {
        if ($this->price_per_km) {
            return "Rp " . number_format($this->base_price, 0, ',', '.') . " + Rp " . 
                   number_format($this->price_per_km, 0, ',', '.') . "/km";
        }
        return "Rp " . number_format($this->base_price, 0, ',', '.');
    }
}