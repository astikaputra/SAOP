<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'driver_id',
        'vehicle_type',
        'vehicle_number',
        'vehicle_brand',
        'vehicle_model',
        'vehicle_year',
        'vehicle_color',
        'status',
        'current_latitude',
        'current_longitude',
        'location_updated_at',
        'total_trips',
        'total_earnings',
        'rating',
        'rating_count',
        'driver_license_image',
        'vehicle_registration_image',
        'insurance_image',
        'shift_start',
        'shift_end',
        'working_days'
    ];

    protected $casts = [
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'location_updated_at' => 'datetime',
        'total_earnings' => 'decimal:2',
        'rating' => 'decimal:2',
        'working_days' => 'array',
        'shift_start' => 'datetime:H:i',
        'shift_end' => 'datetime:H:i'
    ];

    // Vehicle Types
    public const VEHICLE_TYPES = [
        'PICKUP_TRANSPORTASI' => 'Pickup Transportasi',
        'TRANSUP' => 'Transup',
        'MOTOR_110CC' => 'Motor 110cc',
        'MOTOR_125CC' => 'Motor 125cc',
        'MOTOR_150CC' => 'Motor 150cc',
        'MOTOR_200CC' => 'Motor 200cc',
        'MOTOR_250CC' => 'Motor 250cc'
    ];

    // Driver Statuses
    public const STATUSES = [
        'AVAILABLE' => 'Tersedia',
        'ON_TRIP' => 'Dalam Perjalanan',
        'BREAK' => 'Istirahat',
        'OFFLINE' => 'Offline',
        'MAINTENANCE' => 'Perbaikan'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trips()
    {
        return $this->hasMany(QueueTicket::class, 'driver_id', 'user_id');
    }

    // Helper Methods
    public function getVehicleTypeNameAttribute()
    {
        return self::VEHICLE_TYPES[$this->vehicle_type] ?? $this->vehicle_type;
    }

    public function getStatusNameAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getFullNameAttribute()
    {
         return $this->user ? $this->user->name : 'Unknown Driver';
    }

    public function isAvailable()
    {
        return $this->status === 'AVAILABLE';
    }

    public function updateLocation($latitude, $longitude)
    {
        $this->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
            'location_updated_at' => now()
        ]);
    }

    public function updateRating($newRating)
    {
        $this->rating_count++;
        $this->rating = (($this->rating * ($this->rating_count - 1)) + $newRating) / $this->rating_count;
        $this->save();
    }

}