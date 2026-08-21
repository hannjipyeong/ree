<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderContainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'container_type',
        'container_size',
        'container_number',
        'tkbm_option',
        'additional_services',
        'is_cancelled',
        'sp3kk_file_path',
        'is_pnbp',
        'pnbp_number',
        'pnbp_note',
        'pnbp_completed_at',
    ];

    protected $casts = [
        'additional_services' => 'array',
        'is_cancelled' => 'boolean',
        'is_pnbp' => 'boolean',
        'pnbp_completed_at' => 'datetime',
    ];

    protected $appends = ['sp3kk_file_url'];

    public function getSp3kkFileUrlAttribute()
    {
        return $this->sp3kk_file_path ? asset('storage/' . $this->sp3kk_file_path) : null;
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function progresses()
    {
        return $this->hasMany(SubTaskContainerProgress::class, 'order_container_id');
    }
}
