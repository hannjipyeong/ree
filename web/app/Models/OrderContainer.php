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
    ];

    protected $casts = [
        'additional_services' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function progresses()
    {
        return $this->hasMany(SubTaskContainerProgress::class, 'order_container_id');
    }
}
