<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Utang;
use App\Models\UtangInstallement;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendUtangReminder extends Command
{
    protected $signature = 'utang:reminder';

    protected $description = 'Kirim reminder WA sebelum dan sesudah due date utang';

    public function handle()
    {
        // $besok = Carbon::tomorrow()->format('Y-m-d'); // pakai untuk yang benernyta
        $besok = Carbon::create(2025, 5, 19)->format('Y-m-d'); // cicilan terlambat sesuai dengan tanggal cicilan
        // $besok = Carbon::create(2025, 5, 20); // cicilan besok sesuaikan dengan tanggal cicilan
        $hari_ini = Carbon::today()->format('Y-m-d');

        $user = User::where('role', 'cfo')->first();
        if (!$user) {
            Log::error("User dengan role 'cfo' tidak ditemukan.");
            return;
        }

        // === HANDLE UTANG BIASA (BUKAN CICILAN) ===
        $utang_biasa_besok = Utang::where('type', '!=', 'installment')
            ->whereDate('due_date', $besok)
            ->where('is_paid', false)
            ->get();

        $utang_biasa_terlambat = Utang::where('type', '!=', 'installment')
            ->whereDate('due_date', '<', $hari_ini)
            ->where('is_paid', false)
            ->get();

        Log::info("Mengirim reminder untuk utang biasa besok: " . $utang_biasa_besok->count());
        Log::info("Mengirim reminder untuk utang biasa terlambat: " . $utang_biasa_terlambat->count());

        foreach ($utang_biasa_besok as $utang) {
            $this->sendMessage($utang, $user, 'H-1');
        }

        foreach ($utang_biasa_terlambat as $utang) {
            $this->sendMessage($utang, $user, 'terlambat');
        }

        // === HANDLE CICILAN (UTANG INSTALLMENT) ===
        $cicilan_besok = UtangInstallement::whereDate('due_date', $besok)
            ->where('is_paid', false)
            ->with('utang.moneyout')
            ->get();
        Log::info("Mengirim reminder untuk cicilan besok: " . $cicilan_besok->count());

        $cicilan_terlambat = UtangInstallement::whereDate('due_date', '<', $hari_ini)
            ->where('is_paid', false)
            ->with('utang.moneyout')
            ->get();
        Log::info("Mengirim reminder untuk cicilan terlambat: " . $cicilan_terlambat->count());

        foreach ($cicilan_besok as $cicilan) {
            $this->sendMessageInstallment($cicilan, $user, 'H-1');
        }

        foreach ($cicilan_terlambat as $cicilan) {
            $this->sendMessageInstallment($cicilan, $user, 'terlambat');
        }
        
    }

    private function sendMessage($utang, $user, $tipe)
    {
        try {
            $pesan = "*Pemberitahuan Tagihan Utang*\n" .
                "Halo, Chief Financial Officer TEKMT\n" .
                "Anda memiliki tagihan senilai Rp. " . number_format($utang->amount, 0, ',', '.') .
                ($tipe === 'H-1' ? " yang akan jatuh tempo BESOK" : " yang SUDAH lewat jatuh tempo") .
                ($utang->moneyout && $utang->moneyout->payment_to ? ", dengan nama {$utang->moneyout->payment_to}" : "") .
                "\n\nHarap segera ditindaklanjuti.\nTerimakasih";

            $token = 'sRe8VoXMYiM8fhcxHAFq';

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => array(
                    'target' => $user->no_phone,
                    'message' => $pesan,
                    'countryCode' => '62',
                ),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: $token"
                ),
            ));

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
                Log::error("Gagal kirim ke {$user->no_phone}: {$error_msg}");
            } else {
                Log::info("Reminder {$tipe} terkirim ke {$user->no_phone}. Response: {$response}");
            }
            Log::info("Response dari API: {$response}");

            curl_close($curl);
        } catch (\Exception $e) {
            Log::error("Error kirim WA untuk utang ID {$utang->trx_id}: " . $e->getMessage());
        }
    }

    private function sendMessageInstallment($cicilan, $user, $tipe)
    {
        try {
            $pesan = "*Pemberitahuan Cicilan Utang*\n" .
                "Halo, Chief Financial Officer TEKMT\n" .
                "Anda memiliki cicilan senilai Rp. " . number_format($cicilan->amount, 0, ',', '.') .
                ($tipe === 'H-1' ? " yang akan jatuh tempo BESOK" : " yang SUDAH lewat jatuh tempo") .
                ($cicilan->utang && $cicilan->utang->moneyout && $cicilan->utang->moneyout->payment_to
                    ? ", dengan nama {$cicilan->utang->moneyout->payment_to}" : "") .
                "\n\nHarap segera ditindaklanjuti.\nTerimakasih";

            $token = 'sRe8VoXMYiM8fhcxHAFq';

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => array(
                    'target' => $user->no_phone,
                    'message' => $pesan,
                    'countryCode' => '62',
                ),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: $token"
                ),
            ));

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
                Log::error("Gagal kirim ke {$user->no_phone}: {$error_msg}");
            } else {
                Log::info("Reminder cicilan {$tipe} terkirim ke {$user->no_phone}. Response: {$response}");
            }

            curl_close($curl);
        } catch (\Exception $e) {
            Log::error("Error kirim WA untuk cicilan ID {$cicilan->id}: " . $e->getMessage());
        }
    }

}
