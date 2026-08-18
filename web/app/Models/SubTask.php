<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_number',
        'order_id',
        'service_type',
        'supir_id',
        'status',
        'in_note',
        'in_photo_path',
        'out_note',
        'out_photo_path',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function supir()
    {
        return $this->belongsTo(User::class, 'supir_id');
    }

    public function containerProgress()
    {
        return $this->hasMany(SubTaskContainerProgress::class, 'sub_task_id');
    }
}
