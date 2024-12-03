<?php

namespace App\Http\Controllers;

use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class TransaksiDetailController extends Controller
{
    public function index()
    {
        // Fetching the details with the transaksi relationship
        $transaksidetail = TransaksiDetail::with('transaksi')->orderBy('id', 'DESC')->get();

        return view('transaksidetail.index', compact('transaksidetail'));
    }

    public function detail(Request $request)
    {

        $transaksi = Transaksi::with('transaksidetail')->findOrFail($request->id_transaksi);

        return view('transaksidetail.detail', compact('transaksi'));
    }

    public function edit($id)
    {
        // Fetch the transaksi detail to edit
        $transaksidetail = TransaksiDetail::findOrFail($id);

        return view('transaksidetail.edit', compact('transaksidetail'));
    }

    public function update(Request $request, $id)
    {
        // Validating the input
        $request->validate([
            'nama_produk' => 'required|string',
            'harga_satuan' => 'required|numeric',
            'jumlah' => 'required|numeric',
        ]);

        // Find the TransaksiDetail record to update
        $transaksidetail = TransaksiDetail::findOrFail($id);
        $transaksi = $transaksidetail->transaksi; // Get related Transaksi

        // Start transaction
        DB::beginTransaction();
        try {
            // Update the TransaksiDetail
            $transaksidetail->nama_produk = $request->input('nama_produk');
            $transaksidetail->harga_satuan = $request->input('harga_satuan');
            $transaksidetail->jumlah = $request->input('jumlah');
            $transaksidetail->subtotal = $transaksidetail->harga_satuan * $transaksidetail->jumlah;
            $transaksidetail->save();

            // Recalculate total_harga and kembalian for Transaksi
            $total_harga = $transaksi->transaksidetail->sum('subtotal');
            $transaksi->total_harga = $total_harga;
            $transaksi->kembalian = $transaksi->bayar - $total_harga;
            $transaksi->save();

            // Commit the transaction
            DB::commit();

            // Redirect with success message
            return redirect('transaksidetail/' . $transaksidetail->id_transaksi)->with('pesan', 'Berhasil mengubah data');
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollback();
            return redirect()->back()->withErrors(['Transaction' => 'Gagal mengubah data'])->withInput();
        }
    }

    public function destroy($id)
    {
        // Find the TransaksiDetail to delete
        $transaksidetail = TransaksiDetail::findOrFail($id);
        $transaksi = $transaksidetail->transaksi; // Get related Transaksi

        // Start transaction
        DB::beginTransaction();
        try {
            // Delete the TransaksiDetail
            $transaksidetail->delete();

            // Recalculate total_harga and kembalian for Transaksi after deletion
            $total_harga = $transaksi->transaksidetail->sum('subtotal');
            $transaksi->total_harga = $total_harga;
            $transaksi->kembalian = $transaksi->bayar - $total_harga;
            $transaksi->save();

            // Commit the transaction
            DB::commit();

            // Redirect with success message
            return redirect('transaksidetail/' . $transaksi->id)->with('pesan', 'Berhasil menghapus data');
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollback();
            return redirect()->back()->withErrors(['Transaction' => 'Gagal menghapus data'])->withInput();
        }
    }
}
