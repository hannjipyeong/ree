<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderServiceChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_container_id',
        'old_tkbm_option',
        'new_tkbm_option',
        'added_services',
        'document_name',
        'document_path',
        'notes',
        'changed_by',
    ];

    protected $casts = [
        'added_services' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(OrderContainer::class, 'order_container_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
