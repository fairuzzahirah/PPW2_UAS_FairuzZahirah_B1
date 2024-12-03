<?php

namespace App\Http\Controllers;

use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        // Get transactions ordered by the purchase date (newest to oldest)
        $transaksi = Transaksi::orderBy('tanggal_pembelian', 'DESC')->get(); 
        return view('transaksi.index', compact('transaksi'));
    }

    public function create()
    {
        return view('transaksi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_pembelian' => 'required|date',
            'bayar' => 'required|numeric',
            'nama_produk1' => 'required|string',
            'harga_satuan1' => 'required|numeric',
            'jumlah1' => 'required|numeric',
            'nama_produk2' => 'required|string',
            'harga_satuan2' => 'required|numeric',
            'jumlah2' => 'required|numeric',
            'nama_produk3' => 'required|string',
            'harga_satuan3' => 'required|numeric',
            'jumlah3' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // Create Transaksi record
            $transaksi = new Transaksi();
            $transaksi->tanggal_pembelian = $request->input('tanggal_pembelian');
            $transaksi->total_harga = 0;
            $transaksi->bayar = $request->input('bayar');
            $transaksi->kembalian = 0;
            $transaksi->save();

            $total_harga = 0;

            // Loop through the products
            for ($i = 1; $i <= 3; $i++) {
                $transaksidetail = new TransaksiDetail();
                $transaksidetail->id_transaksi = $transaksi->id;
                $transaksidetail->nama_produk = $request->input('nama_produk' . $i);
                $transaksidetail->harga_satuan = $request->input('harga_satuan' . $i);
                $transaksidetail->jumlah = $request->input('jumlah' . $i);
                $transaksidetail->subtotal = $transaksidetail->harga_satuan * $transaksidetail->jumlah;

                // Add to total price
                $total_harga += $transaksidetail->subtotal;

                $transaksidetail->save();
            }

            // Update the total_harga and kembalian of the Transaksi
            $transaksi->total_harga = $total_harga;
            $transaksi->kembalian = $transaksi->bayar - $total_harga;
            $transaksi->save();

            DB::commit();

            // Redirect with success message
            return redirect('transaksidetail/' . $transaksi->id)->with('pesan', 'Berhasil menambahkan data');
        } catch (\Exception $e) {
            DB::rollback();
            // Log the error for debugging (optional)
            // Log::error($e->getMessage());
            return redirect()->back()->withErrors(['Transaction' => 'Gagal menambahkan data'])->withInput();
        }
    }

    public function edit($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        return view('transaksi.edit', compact('transaksi'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bayar' => 'required|numeric'
        ]);

        // Find the Transaksi record
        $transaksi = Transaksi::findOrFail($id);

        // Update bayar and calculate kembalian
        $transaksi->bayar = $request->input('bayar');
        $transaksi->kembalian = $transaksi->bayar - $transaksi->total_harga;
        $transaksi->save();

        // Redirect with success message
        return redirect('/transaksi')->with('pesan', 'Berhasil mengubah data');
    }

    public function destroy($id)
    {
        $transaksi = Transaksi::findOrFail($id);

        // Soft delete the transaksi and its associated transaksi details
        $transaksi->delete();

        // Redirect with success message
        return redirect('/transaksi')->with('pesan', 'Berhasil menghapus data');
    }
}
