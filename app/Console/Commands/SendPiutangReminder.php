<?php

namespace App\Console\Commands;

use App\Models\Piutang;
use App\Models\User;
use App\Models\PiutangInstallement;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPiutangReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piutang:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim Reminder Piutang';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $besok = Carbon::tomorrow()->format('Y-m-d');
        $hari_ini = Carbon::today()->format('Y-m-d');

        // Piutang biasa (bukan cicilan)
        $piutang_besok = Piutang::where('type', '!=', 'installment')
            ->whereDate('due_date', $besok)
            ->get();
        Log::info("Mengirim reminder untuk piutang biasa besok: " . $piutang_besok->count());

        $user = User::where('role', 'cfo')->first();
        if (!$user) {
            Log::error("User dengan role 'cfo' tidak ditemukan.");
            return;
        }

        // Reminder H-1 piutang biasa
        foreach ($piutang_besok as $piutang) {
            $this->sendMessage($piutang, $user, 'H-1');
            Log::info("Reminder H-1 terkirim ke {$user->no_phone} untuk piutang ID {$piutang->collection_id}");
        }

        // Piutang cicilan (installment)
        $cicilan_besok = PiutangInstallement::whereDate('due_date', $besok)
            ->where('is_paid', false)
            ->with('piutang')
            ->get();
        Log::info("Mengirim reminder untuk cicilan piutang besok: " . $cicilan_besok->count());

        foreach ($cicilan_besok as $cicilan) {
            $this->sendMessageInstallment($cicilan, $user, 'H-1');
        }
    }

    private function sendMessage($piutang, $user, $tipe)
    {
        try {
            $pesan = "*Pemberitahuan Piutang*\n" .
                "Halo, Chief Financial Officer TEKMT,\n" .
                "Terdapat piutang senilai Rp. " . number_format($piutang->amount, 0, ',', '.') .
                " yang akan jatuh tempo BESOK" .
                ($piutang->moneyin && $piutang->moneyin->payment_from ? ", dari pihak bernama {$piutang->moneyin->payment_from}" : "") .
                ".\n\nMohon segera ditindaklanjuti agar pembayaran tepat waktu.\nTerima kasih.";

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
            Log::error("Error kirim WA untuk piutang ID {$piutang->collection_id}: " . $e->getMessage());
        }
    }

    private function sendMessageInstallment($cicilan, $user, $tipe)
    {
        try {
            $piutang = $cicilan->piutang;
            $sisa = $piutang->installments()->where('is_paid', false)->sum('amount');
            $pesan = "*Pemberitahuan Cicilan Piutang*\n" .
                "Halo, Chief Financial Officer TEKMT\n" .
                "Anda memiliki cicilan piutang senilai Rp. " . number_format($cicilan->amount, 0, ',', '.') .
                ($tipe === 'H-1' ? " yang akan jatuh tempo BESOK" : " yang SUDAH lewat jatuh tempo") .
                ($piutang && $piutang->payment_from ? ", dari pihak bernama {$piutang->payment_from}" : "") .
                "\nSisa total angsuran piutang: Rp. " . number_format($sisa, 0, ',', '.') .
                "\n\nMohon segera ditindaklanjuti agar pembayaran tepat waktu.\nTerima kasih.";

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
            Log::info("Response dari API: {$response}");

            curl_close($curl);
        } catch (\Exception $e) {
            Log::error("Error kirim WA untuk cicilan piutang ID {$cicilan->id}: " . $e->getMessage());
        }
    }
}
