<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'counter_id',
        'counter_status',
        'telegram_user_id',
        'telegram_username',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_active_at' => 'datetime',
        'preferences' => 'array',
        'is_active' => 'boolean'
    ];

    // User Roles
    public const ROLES = [
        'SUPER_ADMIN' => 'Super Administrator',
        'ADMIN' => 'Administrator',
        'LOKET_STAFF' => 'Staff Loket',
        'DRIVER' => 'Driver',
        'MANAGER' => 'Manager',
        'CUSTOMER' => 'Customer'
    ];

    // Relationships
    public function counter()
    {
        return $this->hasOne(Counter::class);
    }
    public function operator()
    {
        // Jika ingin tahu user yang mengoperasikan counter ini
        return $this->belongsTo(User::class, 'user_id');
    }
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function calledTickets()
    {
        return $this->hasMany(QueueTicket::class, 'called_by');
    }

    public function servedTickets()
    {
        return $this->hasMany(QueueTicket::class, 'served_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    // Helper Methods
    public function isAdmin()
    {
        return in_array($this->role, ['SUPER_ADMIN', 'ADMIN']);
    }

    public function isLoketStaff()
    {
        return $this->role === 'LOKET_STAFF';
    }

    public function isDriver()
    {
        return $this->role === 'DRIVER';
    }

    public function getRoleNameAttribute()
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLoketStaff($query)
    {
        return $query->where('role', 'LOKET_STAFF');
    }

    public function scopeDrivers($query)
    {
        return $query->where('role', 'DRIVER');
    }
}