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
        'is_invoiced',
        'invoice_number',
        'invoiced_at',
        'in_note',
        'in_photo_path',
        'in_photos',
        'in_time',
        'out_note',
        'out_photo_path',
        'out_photos',
        'out_time',
        'done_note',
        'done_photo_path',
        'done_photos',
        'done_time',
    ];

    protected function casts(): array
    {
        return [
            'is_invoiced' => 'boolean',
            'invoiced_at' => 'datetime',
            'in_photos' => 'array',
            'out_photos' => 'array',
            'done_photos' => 'array',
            'in_time' => 'datetime',
            'out_time' => 'datetime',
            'done_time' => 'datetime',
        ];
    }

    public function getAllInPhotosAttribute(): array
    {
        $photos = is_array($this->in_photos) ? $this->in_photos : [];
        if ($this->in_photo_path && !in_array($this->in_photo_path, $photos)) {
            array_unshift($photos, $this->in_photo_path);
        }
        return array_values(array_filter($photos));
    }

    public function getAllOutPhotosAttribute(): array
    {
        $photos = is_array($this->out_photos) ? $this->out_photos : [];
        if ($this->out_photo_path && !in_array($this->out_photo_path, $photos)) {
            array_unshift($photos, $this->out_photo_path);
        }
        return array_values(array_filter($photos));
    }

    public function getAllDonePhotosAttribute(): array
    {
        $photos = is_array($this->done_photos) ? $this->done_photos : [];
        if ($this->done_photo_path && !in_array($this->done_photo_path, $photos)) {
            array_unshift($photos, $this->done_photo_path);
        }
        return array_values(array_filter($photos));
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
