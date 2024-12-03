<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiDetail extends Model
{
    use HasFactory, SoftDeletes; // Menggunakan trait SoftDeletes

    protected $table = 'transaksi_detail';

    protected $fillable = [
        'id_transaksi',
        'nama_produk',
        'harga_satuan',
        'jumlah',
        'subtotal',
    ];

    // Relasi ke Transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id');
    }
}
