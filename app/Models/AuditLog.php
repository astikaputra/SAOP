<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
        'user_id',
        'url',
        'metadata'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->morphTo();
    }

    // Helper Methods
    public function getActionNameAttribute()
    {
        return match ($this->action) {
            'CREATE' => 'Buat Data',
            'UPDATE' => 'Ubah Data',
            'DELETE' => 'Hapus Data',
            'CALL' => 'Panggil Antrian',
            'COMPLETE' => 'Selesaikan Antrian',
            'CANCEL' => 'Batalkan Antrian',
            default => $this->action,
        };
    }
}