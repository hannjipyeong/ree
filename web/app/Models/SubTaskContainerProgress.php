<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTaskContainerProgress extends Model
{
    use HasFactory;

    protected $table = 'sub_task_container_progress';

    protected $fillable = [
        'sub_task_id',
        'order_container_id',
        'status',
        'in_note',
        'in_photo_path',
        'in_time',
        'out_note',
        'out_photo_path',
        'out_time',
    ];

    protected function casts(): array
    {
        return [
            'in_time' => 'datetime',
            'out_time' => 'datetime',
        ];
    }

    public function subTask()
    {
        return $this->belongsTo(SubTask::class, 'sub_task_id');
    }

    public function container()
    {
        return $this->belongsTo(OrderContainer::class, 'order_container_id');
    }
}
