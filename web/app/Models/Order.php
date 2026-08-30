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
        'jumlah_barang',
        'jumlah_tonase',
        'nomor_bl',
        'vessel',
        'voyage',
        'no_surat_jalan',
        'no_bp',
        'nomor_container_cargo',
        'cargo_file_path',
        'railing_file_path',
        'tkbm_option',
        'has_asuransi',
        'asuransi_value',
        'status',
        'is_invoiced',
        'invoice_number',
        'invoiced_at',
        'is_pnbp',
        'pnbp_number',
        'pnbp_note',
        'pnbp_completed_at',
    ];

    protected $casts = [
        'tanggal_order' => 'date',
        'has_asuransi' => 'boolean',
        'asuransi_value' => 'decimal:2',
        'jumlah_tonase' => 'decimal:2',
        'is_invoiced' => 'boolean',
        'invoiced_at' => 'datetime',
        'is_pnbp' => 'boolean',
        'pnbp_completed_at' => 'datetime',
        'cargo_file_path' => 'array',
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

    /**
     * Generate sequential incrementing order number: ORD-001, ORD-002, ORD-003, ...
     */
    public static function generateNextOrderNumber(): string
    {
        $orders = self::where('order_number', 'like', 'ORD-%')->get();
        $maxNum = 0;
        foreach ($orders as $o) {
            if (preg_match('/^ORD-(\d+)$/', (string)$o->order_number, $matches)) {
                $num = intval($matches[1]);
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        if ($maxNum === 0) {
            $maxNum = self::count();
        }

        $nextNum = $maxNum + 1;
        return 'ORD-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);
    }
}
