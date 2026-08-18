<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'source',
        'tanggal_order',
        'nama_pt',
        'nama_pbm',
        'no_telp',
        'wilayah',
        'lokasi_fasilitas',
        'jenis_kegiatan',
        'payload_type',
        'cargo_file_path',
        'haulage_file_path',
        'tkbm_option',
        'has_asuransi',
        'asuransi_value',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_order' => 'date',
            'has_asuransi' => 'boolean',
            'asuransi_value' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function containers()
    {
        return $this->hasMany(OrderContainer::class, 'order_id');
    }

    public function subTasks()
    {
        return $this->hasMany(SubTask::class, 'order_id');
    }
}
