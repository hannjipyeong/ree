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
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
