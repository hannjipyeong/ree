<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'jenis_barang',
        'jumlah_tonase',
        'nomor_container_cargo',
        'cargo_file_path',
        'haulage_file_path',
        'tkbm_option',
        'has_asuransi',
        'asuransi_value',
        'status',
    ];

    protected $casts = [
        'tanggal_order' => 'date',
        'has_asuransi' => 'boolean',
        'asuransi_value' => 'decimal:2',
        'jumlah_tonase' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function containers(): HasMany
    {
        return $this->hasMany(OrderContainer::class);
    }

    public function subTasks(): HasMany
    {
        return $this->hasMany(SubTask::class);
    }

    public function serviceChanges(): HasMany
    {
        return $this->hasMany(OrderServiceChange::class)->latest();
    }
}
