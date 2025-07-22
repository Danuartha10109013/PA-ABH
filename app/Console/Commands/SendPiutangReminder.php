<?php

namespace App\Console\Commands;

use App\Models\Piutang;
use App\Models\User;
use App\Models\PiutangInstallement;
use App\Models\MoneyIn;
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
        $now = Carbon::now('Asia/Jakarta');
        $besok = $now->copy()->addDay()->toDateString();
        $hari_ini = $now->toDateString();

        $user = User::where('role', 'cfo')->first();
        if (!$user) {
            Log::error("User dengan role 'cfo' tidak ditemukan.");
            return;
        }

        // PIUTANG PENUH JATUH TEMPO BESOK
        $piutang_biasa_besok = Piutang::where('type', '!=', 'installment')
            ->whereDate('due_date', $besok)
            ->where('is_paid', false)
            ->where('reminded_besok', 0)
            ->get();
        if ($piutang_biasa_besok->count() > 0) {
            $this->sendMessagePiutangPenuhBesok($piutang_biasa_besok, $user, $now);
            foreach ($piutang_biasa_besok as $piutang) {
                $piutang->reminded_besok = 1;
                $piutang->save();
            }
        }

        // PIUTANG PENUH OVERDUE
        $piutang_biasa_terlambat = Piutang::where('type', '!=', 'installment')
            ->whereDate('due_date', '<', $hari_ini)
            ->where('is_paid', false)
            ->get();
        if ($piutang_biasa_terlambat->count() > 0) {
            $this->sendMessagePiutangPenuhOverdue($piutang_biasa_terlambat, $user, $now);
            // sleep(60);
        }

        // CICILAN OVERDUE
        $cicilan_terlambat = PiutangInstallement::where('is_paid', 0)
            ->whereDate('due_date', '<', $hari_ini)
            ->with(['piutang' => function($q) { $q->where('is_paid', 0); }])
            ->get();
        if ($cicilan_terlambat->count() > 0) {
            $this->sendMessagePiutangCicilanOverdue($cicilan_terlambat, $user, $now);
            // sleep(60);
        }

        // CICILAN JATUH TEMPO BESOK
        $cicilan_besok = \App\Models\PiutangInstallement::where('is_paid', 0)
            ->whereDate('due_date', $besok)
            ->where('reminded_besok', 0)
            ->with(['piutang' => function($q) { $q->where('is_paid', 0); }])
            ->get();
        if ($cicilan_besok->count() > 0) {
            $this->sendMessagePiutangCicilanBesok($cicilan_besok, $user, $now);
            foreach ($cicilan_besok as $cicil) {
                $cicil->reminded_besok = 1;
                $cicil->save();
            }
        }
    }

    private function sendMessage($pesan, $user)
    {
        try {
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
                Log::info("Summary reminder piutang terkirim ke {$user->no_phone}. Response: {$response}");
            }
            curl_close($curl);
        } catch (\Exception $e) {
            Log::error("Error kirim summary WA piutang: " . $e->getMessage());
        }
    }

    private function sendMessagePiutangPenuhBesok($piutangList, $user, $now)
    {
        $total = 0;
        $count = $piutangList->count();
        $pesan = "*Reminder Piutang Jatuh Tempo Besok*\n";
        $pesan .= "Tanggal: " . $now->format('d/m/Y H:i') . " WIB\n";
        foreach ($piutangList as $item) {
            $pesan .= "ID: {$item->collection_id} | Jumlah: Rp. " . number_format($item->amount,0,',','.') . " | Sisa piutang: Rp. " . number_format($item->amount,0,',','.') . " | Dari: " . ($item->payment_from ?? '-') . "\n";
            $total += $item->amount;
        }
        $pesan .= "\nTotal nominal piutang jatuh tempo besok: Rp. " . number_format($total,0,',','.') . "\n";
        if ($count === 1) {
            $pesan .= "\nHanya ada 1 piutang yang akan jatuh tempo besok. Mohon segera ditindaklanjuti!";
        } else {
            $pesan .= "\nAda $count piutang yang akan jatuh tempo besok. Mohon segera ditindaklanjuti!";
        }
        $this->sendMessage($pesan, $user);
    }

    private function sendMessagePiutangPenuhOverdue($piutangList, $user, $now)
    {
        $total = 0;
        $pesan = "*Summary Reminder Piutang Penuh Overdue*\n";
        $pesan .= "Tanggal: " . $now->format('d/m/Y H:i') . " WIB\n";
        foreach ($piutangList as $item) {
            $pesan .= "ID: {$item->collection_id} | Jumlah: Rp. " . number_format($item->amount,0,',','.') . " | Sisa piutang: Rp. " . number_format($item->amount,0,',','.') . " | Dari: " . ($item->payment_from ?? '-') . "\n";
            $total += $item->amount;
        }
        $pesan .= "\nTotal nominal piutang overdue: Rp. " . number_format($total,0,',','.') . "\n";
        $pesan .= "\nPiutang-piutang ini SUDAH LEWAT JATUH TEMPO. Mohon segera ditindaklanjuti!";
        $this->sendMessage($pesan, $user);
    }

    private function sendMessagePiutangCicilanOverdue($cicilanList, $user, $now)
    {
        // Kelompokkan cicilan overdue berdasarkan piutang
        $grouped = $cicilanList->groupBy(function($cicilan) {
            return $cicilan->piutang ? $cicilan->piutang->collection_id : null;
        });
        $pesan = "*Summary Reminder Cicilan Piutang Overdue*\n";
        $pesan .= "Tanggal: " . $now->format('d/m/Y H:i') . " WIB\n";
        $totalCicilan = 0;
        $totalSisa = 0;
        foreach ($grouped as $collection_id => $cicilans) {
            $piutang = $cicilans->first()->piutang;
            $sisa = $piutang ? $piutang->installments()->where('is_paid', false)->sum('amount') : $cicilans->sum('amount');
            $cicil = $piutang ? $piutang->installments()->where('is_paid', false)->count() : $cicilans->count();
            $jumlahCicilanOverdue = $cicilans->count();
            $nominalCicilanOverdue = $cicilans->sum('amount');
            $pesan .= "ID: {$collection_id} | Jumlah cicilan overdue: $jumlahCicilanOverdue | Nominal overdue: Rp. " . number_format($nominalCicilanOverdue,0,',','.') . " | Sisa cicilan: $cicil | Sisa piutang: Rp. " . number_format($sisa,0,',','.') . " | Dari: " . ($piutang->payment_from ?? '-') . "\n";
            $totalCicilan += $nominalCicilanOverdue;
            $totalSisa += $sisa;
        }
        $pesan .= "\nTotal nominal cicilan overdue: Rp. " . number_format($totalCicilan,0,',','.') . "\n";
        $pesan .= "Total sisa piutang dari cicilan overdue: Rp. " . number_format($totalSisa,0,',','.') . "\n";
        $pesan .= "\nCicilan-cicilan ini SUDAH LEWAT JATUH TEMPO. Mohon segera ditindaklanjuti!";
        $this->sendMessage($pesan, $user);
    }

    private function sendMessagePiutangCicilanBesok($cicilanList, $user, $now)
    {
        // Kelompokkan cicilan jatuh tempo besok berdasarkan piutang
        $grouped = $cicilanList->groupBy(function($cicilan) {
            return $cicilan->piutang ? $cicilan->piutang->collection_id : null;
        });
        $pesan = "*Summary Reminder Cicilan Piutang Jatuh Tempo Besok*\n";
        $pesan .= "Tanggal: " . $now->format('d/m/Y H:i') . " WIB\n";
        $totalCicilan = 0;
        $totalSisa = 0;
        foreach ($grouped as $collection_id => $cicilans) {
            $piutang = $cicilans->first()->piutang;
            $sisa = $piutang ? $piutang->installments()->where('is_paid', false)->sum('amount') : $cicilans->sum('amount');
            $cicil = $piutang ? $piutang->installments()->where('is_paid', false)->count() : $cicilans->count();
            $jumlahCicilanBesok = $cicilans->count();
            $nominalCicilanBesok = $cicilans->sum('amount');
            $pesan .= "ID: {$collection_id} | Jumlah cicilan jatuh tempo besok: $jumlahCicilanBesok | Nominal jatuh tempo besok: Rp. " . number_format($nominalCicilanBesok,0,',','.') . " | Sisa cicilan: $cicil | Sisa piutang: Rp. " . number_format($sisa,0,',','.') . " | Dari: " . ($piutang->payment_from ?? '-') . "\n";
            $totalCicilan += $nominalCicilanBesok;
            $totalSisa += $sisa;
        }
        $pesan .= "\nTotal nominal cicilan jatuh tempo besok: Rp. " . number_format($totalCicilan,0,',','.') . "\n";
        $pesan .= "Total sisa piutang dari cicilan jatuh tempo besok: Rp. " . number_format($totalSisa,0,',','.') . "\n";
        $pesan .= "\nCicilan-cicilan ini akan jatuh tempo besok. Mohon segera ditindaklanjuti!";
        $this->sendMessage($pesan, $user);
    }
}
