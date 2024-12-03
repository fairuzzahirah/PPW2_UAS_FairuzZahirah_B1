<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Rentang tanggal yang diinginkan
        $startDate = Carbon::create(2024, 11, 1); // 1 November 2024
        $endDate = Carbon::create(2024, 11, 10); // 10 November 2024

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Jumlah transaksi per hari antara 15 dan 20
            $numberOfTransactions = $faker->numberBetween(15, 20);

            for ($i = 0; $i < $numberOfTransactions; $i++) {
                Transaksi::create([
                    'tanggal_pembelian' => $date->format('Y-m-d'), // Format tanggal
                    'total_harga' => 0, // Nilai awal
                    'bayar' => 0, // Nilai awal
                    'kembalian' => 0, // Nilai awal
                ]);
            }
        }
    }
}
