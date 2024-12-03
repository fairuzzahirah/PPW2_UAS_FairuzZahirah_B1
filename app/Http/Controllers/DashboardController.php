<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi; // Sesuaikan dengan nama model Anda

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung jumlah transaksi
        $transaksi_count = Transaksi::count();

        // Kirim data ke view
        return view('dashboard', compact('transaksi_count'));
    }
}
