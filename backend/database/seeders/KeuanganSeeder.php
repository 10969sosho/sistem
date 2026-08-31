<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Debt;
use Illuminate\Database\Seeder;

class KeuanganSeeder extends Seeder
{
    /**
     * Master data dropdowns dari spreadsheet KEUANGAN.
     */
    public const KATEGORI_PEMASUKAN = [
        'Pendapatan Penjualan',
        'Pendapatan Project',
        'Pendapatan Maintenance',
        'Pendapatan Lainnya',
    ];

    public const KATEGORI_PENGELUARAN = [
        'Beban Gaji',
        'Beban Marketing',
        'Beban AI',
        'Beban Software',
        'Beban Transportasi',
        'Beban Operasional Kantor',
        'Beban Pajak',
        'Beban Peralatan',
        'Beban Maintanance',
        'Beban Lainnya',
    ];

    public const METODE_PEMBAYARAN = [
        'Transfer',
        'Cash',
        'QRIS',
        'Debit',
        'Credit Card',
    ];

    public function run(): void
    {
        // Seed sample transactions dari spreadsheet dump (JULI - AGUSTUS 2026)
        $transactions = [
            // JULI 2026 - Pemasukan
            ['date' => '2026-07-02', 'type' => 'pemasukan', 'category' => 'Pendapatan Project', 'description' => 'Project Formula Juli', 'vendor' => 'Bu Vania', 'amount' => 300000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-06', 'type' => 'pemasukan', 'category' => 'Pendapatan Project', 'description' => 'Project (Termin 3)', 'vendor' => 'Pak Joni', 'amount' => 1950000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-24', 'type' => 'pemasukan', 'category' => 'Pendapatan Maintenance', 'description' => 'Perpanjang DomHos Maintenance', 'vendor' => 'Hakusa Edu', 'amount' => 500000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-30', 'type' => 'pemasukan', 'category' => 'Pendapatan Project', 'description' => 'Project Payroll (Termin 1)', 'vendor' => 'Bu Vania', 'amount' => 2500000, 'status' => 'paid', 'payment_method' => 'Transfer'],

            // JULI 2026 - Pengeluaran
            ['date' => '2026-07-01', 'type' => 'pengeluaran', 'category' => 'Beban Gaji', 'description' => 'Titus', 'vendor' => null, 'amount' => 3150000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-01', 'type' => 'pengeluaran', 'category' => 'Beban Gaji', 'description' => 'Cecil', 'vendor' => null, 'amount' => 1575000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-01', 'type' => 'pengeluaran', 'category' => 'Beban Maintanance', 'description' => 'Pindah Hosting ke NATA', 'vendor' => null, 'amount' => 800000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-01', 'type' => 'pengeluaran', 'category' => 'Beban Operasional Kantor', 'description' => 'Content Creator IG', 'vendor' => null, 'amount' => 300000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-01', 'type' => 'pengeluaran', 'category' => 'Beban Operasional Kantor', 'description' => 'Content Creator IG', 'vendor' => null, 'amount' => 50000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-01', 'type' => 'pengeluaran', 'category' => 'Beban Software', 'description' => 'CHATGPT Juli', 'vendor' => null, 'amount' => 75000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-01', 'type' => 'pengeluaran', 'category' => 'Beban Operasional Kantor', 'description' => 'Domain jayapetir.com', 'vendor' => null, 'amount' => 181200, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-03', 'type' => 'pengeluaran', 'category' => 'Beban Transportasi', 'description' => 'Ke Surabaya 3 Juli', 'vendor' => null, 'amount' => 250000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-03', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Deepseek', 'vendor' => null, 'amount' => 39103, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-06', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Deepseek', 'vendor' => null, 'amount' => 97573, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-11', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Open ai', 'vendor' => null, 'amount' => 102881, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-14', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Deepseek', 'vendor' => null, 'amount' => 39420, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-14', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Deepseek', 'vendor' => null, 'amount' => 59130, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-15', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Open ai', 'vendor' => null, 'amount' => 32500, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-18', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Deepseek', 'vendor' => null, 'amount' => 97953, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-23', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Opencode Anomaly', 'vendor' => null, 'amount' => 93066, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-27', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Ai Jelek', 'vendor' => null, 'amount' => 8000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-07-27', 'type' => 'pengeluaran', 'category' => 'Beban Transportasi', 'description' => 'Ke Surabaya 27 Juli', 'vendor' => null, 'amount' => 250000, 'status' => 'pending', 'payment_method' => 'Transfer'],

            // AGUSTUS 2026 - Pemasukan
            ['date' => '2026-08-05', 'type' => 'pemasukan', 'category' => 'Pendapatan Project', 'description' => 'Pembayaran Termin 4', 'vendor' => 'Pak Joni / Jomoto Center', 'amount' => 1950000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-07', 'type' => 'pemasukan', 'category' => 'Pendapatan Project', 'description' => 'Pembayaran Maintenance Formula', 'vendor' => 'Bu Vania / Tiga Putra Perkasa', 'amount' => 300000, 'status' => 'paid', 'payment_method' => 'Transfer'],

            // AGUSTUS 2026 - Pengeluaran
            ['date' => '2026-08-05', 'type' => 'pengeluaran', 'category' => 'Beban Gaji', 'description' => 'Tian', 'vendor' => null, 'amount' => 1170000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-05', 'type' => 'pengeluaran', 'category' => 'Beban Gaji', 'description' => 'Cecil', 'vendor' => null, 'amount' => 585000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-07', 'type' => 'pengeluaran', 'category' => 'Beban Gaji', 'description' => 'Tian', 'vendor' => null, 'amount' => 180000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-07', 'type' => 'pengeluaran', 'category' => 'Beban Gaji', 'description' => 'Cecil', 'vendor' => null, 'amount' => 90000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-01', 'type' => 'pengeluaran', 'category' => 'Beban Marketing', 'description' => 'Meta Ads ke-1', 'vendor' => 'META', 'amount' => 178000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-01', 'type' => 'pengeluaran', 'category' => 'Beban Marketing', 'description' => 'Meta Ads ke-2', 'vendor' => 'META', 'amount' => 390000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-01', 'type' => 'pengeluaran', 'category' => 'Beban Marketing', 'description' => 'Meta Ads ke-3', 'vendor' => 'META', 'amount' => 389000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-01', 'type' => 'pengeluaran', 'category' => 'Beban Marketing', 'description' => 'Meta Ads ke-4', 'vendor' => 'META', 'amount' => 390000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-01', 'type' => 'pengeluaran', 'category' => 'Beban Maintanance', 'description' => 'Hosting Rumah Web', 'vendor' => 'Rumah Web', 'amount' => 124000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-01', 'type' => 'pengeluaran', 'category' => 'Beban Maintanance', 'description' => 'Perpanjang Domain Solusi', 'vendor' => 'Niagahoster', 'amount' => 237000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-01', 'type' => 'pengeluaran', 'category' => 'Beban Maintanance', 'description' => 'Domain jayapetir.online', 'vendor' => 'Niagahoster', 'amount' => 23000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-03', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'API ai murah', 'vendor' => null, 'amount' => 26000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-04', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'API ai murah', 'vendor' => null, 'amount' => 26000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-07', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'API ai murah', 'vendor' => null, 'amount' => 15000, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-01', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Open Router', 'vendor' => null, 'amount' => 110696, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-01', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Anomaly', 'vendor' => null, 'amount' => 199689, 'status' => 'paid', 'payment_method' => 'Transfer'],
            ['date' => '2026-08-01', 'type' => 'pengeluaran', 'category' => 'Beban AI', 'description' => 'Open ai', 'vendor' => null, 'amount' => 122474, 'status' => 'paid', 'payment_method' => 'Transfer'],
        ];

        foreach ($transactions as $txn) {
            Transaction::create($txn);
        }

        // Seed sample debts dari spreadsheet dump (HUTANG ke OWNER)
        $debts = [
            ['date' => null, 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Pindah Hosting ke NATA', 'amount' => 800000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => null, 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Content Creator IG', 'amount' => 300000, 'status' => 'lunas', 'paid_date' => null],
            ['date' => null, 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Content Creator IG', 'amount' => 50000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => null, 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'CHATGPT Juli', 'amount' => 75000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => null, 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Domain jayapetir.com', 'amount' => 181200, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => null, 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Ke Surabaya 3 Juli', 'amount' => 250000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-07-03', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Deepseek', 'amount' => 39103, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-07-06', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Deepseek', 'amount' => 97573, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-07-11', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Open ai', 'amount' => 102881, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-07-14', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Deepseek', 'amount' => 39420, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-07-14', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Deepseek', 'amount' => 59130, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-07-15', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Open ai', 'amount' => 32500, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-07-18', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Deepseek', 'amount' => 97953, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-07-23', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Opencode Anomaly', 'amount' => 93066, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-07-27', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Ai Jelek', 'amount' => 8000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-07-27', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Ke Surabaya 27 Juli', 'amount' => 250000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-08-01', 'type' => 'talangan', 'person' => 'CECIL', 'description' => 'Meta Ads ke-1', 'amount' => 178000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-08-01', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Meta Ads ke-2', 'amount' => 390000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-08-01', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Meta Ads ke-3', 'amount' => 389000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-08-01', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Meta Ads ke-4', 'amount' => 390000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-08-01', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Hosting Rumah Web', 'amount' => 124000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-08-01', 'type' => 'talangan', 'person' => 'TIAN', 'description' => 'Perpanjang Domain Solusi', 'amount' => 237000, 'status' => 'belum_dibayar', 'paid_date' => null],
            ['date' => '2026-08-01', 'type' => 'talangan', 'person' => 'CECIL', 'description' => 'Domain jayapetir.online', 'amount' => 23000, 'status' => 'belum_dibayar', 'paid_date' => null],
        ];

        foreach ($debts as $debt) {
            Debt::create($debt);
        }
    }
}
