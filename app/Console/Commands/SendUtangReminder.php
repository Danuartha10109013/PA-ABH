<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Utang;
use App\Models\UtangInstallement;
use App\Models\MoneyOut;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendUtangReminder extends Command
{
    protected $signature = 'utang:reminder';

    protected $description = 'Kirim reminder WA sebelum dan sesudah due date utang';

    public function handle()
    {
        $besok = Carbon::tomorrow()->format('Y-m-d');
        $hari_ini = Carbon::today()->format('Y-m-d');

        $user = User::where('role', 'cfo')->first();
        if (!$user) {
            Log::error("User dengan role 'cfo' tidak ditemukan.");
            return;
        }

        // === UTANG PENUH JATUH TEMPO BESOK ===
        $utang_biasa_besok = Utang::where('type', '!=', 'installment')
            ->whereDate('due_date', $besok)
            ->where('is_paid', false)
            ->where('reminded_besok', 0)
            ->get();
        // UTANG PENUH JATUH TEMPO BESOK
        if ($utang_biasa_besok->count() > 0) {
            $this->sendMessageUtangPenuhBesok($utang_biasa_besok, $user);
            foreach ($utang_biasa_besok as $utang) {
                $utang->reminded_besok = 1;
                $utang->save();
            }
        }

        // === UTANG PENUH OVERDUE ===
        $utang_biasa_terlambat = Utang::where('type', '!=', 'installment')
            ->whereDate('due_date', '<', $hari_ini)
            ->where('is_paid', false)
            ->get();
        // UTANG PENUH OVERDUE
        if ($utang_biasa_terlambat->count() > 0) {
            $this->sendMessageUtangPenuhOverdue($utang_biasa_terlambat, $user);
            // sleep(60);
        }

        // === CICILAN OVERDUE ===
        $cicilan_terlambat = UtangInstallement::whereDate('due_date', '<', $hari_ini)
            ->where('is_paid', false)
            ->with('utang.moneyout')
            ->get();
        // CICILAN OVERDUE
        if ($cicilan_terlambat->count() > 0) {
            $this->sendMessageUtangCicilanOverdue($cicilan_terlambat, $user);
            // sleep(60);
        }

        // === CICILAN JATUH TEMPO BESOK ===
        $cicilan_besok = UtangInstallement::whereDate('due_date', $besok)
            ->where('is_paid', false)
            ->where('reminded_besok', 0)
            ->with('utang.moneyout')
            ->get();
        // CICILAN JATUH TEMPO BESOK
        if ($cicilan_besok->count() > 0) {
            $this->sendMessageUtangCicilanBesok($cicilan_besok, $user);
            foreach ($cicilan_besok as $cicil) {
                $cicil->reminded_besok = 1;
                $cicil->save();
            }
        }
    }

    private function sendMessageUtangPenuhOverdue($utangList, $user)
    {
        $total = 0;
        $pesan = "*Summary Reminder Utang Penuh Overdue*\n";
        $pesan .= "Total utang overdue: {$utangList->count()}\n";
        foreach ($utangList as $item) {
            $pesan .= "ID: {$item->trx_id} | Jumlah: Rp. " . number_format($item->amount,0,',','.') . " | Sisa utang: Rp. " . number_format($item->amount,0,',','.') . " | Kepada: " . ($item->payment_from ?? '-') . "\n";
            $total += $item->amount;
        }
        $pesan .= "\nTotal nominal utang overdue: Rp. " . number_format($total,0,',','.') . "\n";
        $pesan .= "\nUtang-utang ini SUDAH LEWAT JATUH TEMPO. Mohon segera ditindaklanjuti!";
        $this->sendMessage($pesan, $user);
    }

    private function sendMessageUtangCicilanOverdue($cicilanList, $user)
    {
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        // Ambil dari MoneyOut, lalu ke utang (installment), lalu ke utang_instalment yang overdue
        $moneyOuts = MoneyOut::with(['utangs' => function($q) {
            $q->where('type', 'installment');
        }, 'utangs.installments' => function($q) {
            $q->where('is_paid', false)->whereDate('due_date', '<', now('Asia/Jakarta')->toDateString());
        }])->get();
        $pesan = "*Summary Reminder Cicilan Utang Overdue*\n";
        $pesan .= "Tanggal: " . $now->format('d/m/Y H:i') . " WIB\n";
        $totalUtang = 0;
        $totalCicilanOverdue = 0;
        $totalSisa = 0;
        $countUtang = 0;
        foreach ($moneyOuts as $mo) {
            foreach ($mo->utangs as $utang) {
                $overdueCicilan = $utang->installments()
                    ->where('is_paid', false)
                    ->whereDate('due_date', '<=', $now->toDateString())
                    ->get();
                if ($overdueCicilan->count() > 0) {
                    $countUtang++;
                    $sisa = $utang->installments()->where('is_paid', false)->sum('amount');
                    $cicil = $utang->installments()->where('is_paid', false)->count();
                    $jumlahCicilanOverdue = $overdueCicilan->count();
                    $nominalCicilanOverdue = $overdueCicilan->sum('amount');
                    $pesan .= "ID: {$utang->trx_id} | Jumlah cicilan overdue: $jumlahCicilanOverdue | Nominal overdue: Rp. " . number_format($nominalCicilanOverdue,0,',','.') . " | Sisa cicilan: $cicil | Sisa utang: Rp. " . number_format($sisa,0,',','.') . " | Kepada: " . ($utang->payment_from ?? '-') . "\n";
                    $totalCicilanOverdue += $nominalCicilanOverdue;
                    $totalSisa += $sisa;
                }
            }
        }
        $pesan = str_replace('{$grouped->count()}', $countUtang, $pesan); // update count utang
        $pesan .= "\nTotal nominal cicilan overdue: Rp. " . number_format($totalCicilanOverdue,0,',','.') . "\n";
        $pesan .= "Total sisa utang dari cicilan overdue: Rp. " . number_format($totalSisa,0,',','.') . "\n";
        $pesan .= "\nCicilan-cicilan ini SUDAH LEWAT JATUH TEMPO. Mohon segera ditindaklanjuti!";
        if ($countUtang > 0) {
            $this->sendMessage($pesan, $user);
        }
    }

    private function sendMessageUtangCicilanBesok($cicilanList, $user)
    {
        // Kelompokkan cicilan jatuh tempo besok berdasarkan utang
        $grouped = $cicilanList->groupBy(function($cicilan) {
            return $cicilan->utang ? $cicilan->utang->trx_id : null;
        });
        $pesan = "*Summary Reminder Cicilan Utang Jatuh Tempo Besok*\n";
        $pesan .= "Total utang dengan cicilan jatuh tempo besok: {$grouped->count()}\n";
        $totalCicilan = 0;
        $totalSisa = 0;
        foreach ($grouped as $trx_id => $cicilans) {
            $utang = $cicilans->first()->utang;
            $sisa = $utang ? $utang->installments()->where('is_paid', false)->sum('amount') : $cicilans->sum('amount');
            $cicil = $utang ? $utang->installments()->where('is_paid', false)->count() : $cicilans->count();
            $jumlahCicilanBesok = $cicilans->count();
            $pesan .= "ID: {$trx_id} | Jumlah cicilan jatuh tempo besok: $jumlahCicilanBesok | Sisa cicilan: $cicil | Sisa utang: Rp. " . number_format($sisa,0,',','.') . " | Kepada: " . ($utang->payment_from ?? '-') . "\n";
            $totalCicilan += $cicilans->sum('amount');
            $totalSisa += $sisa;
        }
        $pesan .= "\nTotal nominal cicilan jatuh tempo besok: Rp. " . number_format($totalCicilan,0,',','.') . "\n";
        $pesan .= "Total sisa utang dari cicilan jatuh tempo besok: Rp. " . number_format($totalSisa,0,',','.') . "\n";
        $pesan .= "\nCicilan-cicilan ini akan jatuh tempo besok. Mohon segera ditindaklanjuti!";
        $this->sendMessage($pesan, $user);
    }

    private function sendMessageUtangPenuhBesok($utangList, $user)
    {
        $total = 0;
        $pesan = "*Summary Reminder Utang Penuh Jatuh Tempo Besok*\n";
        // $pesan .= "Total utang jatuh tempo besok: {$utangList->count()}\n";
        foreach ($utangList as $item) {
            $pesan .= "ID: {$item->trx_id} | Jumlah: Rp. " . number_format($item->amount,0,',','.') . " | Sisa utang: Rp. " . number_format($item->amount,0,',','.') . " | Kepada: " . ($item->payment_from ?? '-') . "\n";
            $total += $item->amount;
        }
        $pesan .= "\nTotal nominal utang jatuh tempo besok: Rp. " . number_format($total,0,',','.') . "\n";
        $pesan .= "\nUtang-utang ini akan jatuh tempo besok. Mohon segera ditindaklanjuti!";
        $this->sendMessage($pesan, $user);
    }

    private function sendMessageUtangBesok($item, $user, $isCicilan = false)
    {
        if ($isCicilan) {
            $utang = $item->utang;
            $sisa = $utang ? $utang->installments()->where('is_paid', false)->sum('amount') : $item->amount;
            $cicil = $utang ? $utang->installments()->where('is_paid', false)->count() : 1;
            $pesan = "*Reminder Cicilan Utang Jatuh Tempo Besok*\n" .
                "ID: {$utang->trx_id}\n" .
                "Jumlah cicilan: Rp. " . number_format($item->amount,0,',','.') . "\n" .
                "Jenis: Cicilan\n" .
                "Sisa cicilan: $cicil\n" .
                "Sisa utang: Rp. " . number_format($sisa,0,',','.') . "\n" .
                "Kepada: " . ($utang->payment_from ?? '-') . "\n" .
                "\nMohon segera ditindaklanjuti agar pembayaran tepat waktu. Terima kasih.";
            $this->sendMessage($pesan, $user);
        } else {
            $pesan = "*Reminder Utang Jatuh Tempo Besok*\n" .
                "ID: {$item->trx_id}\n" .
                "Jumlah: Rp. " . number_format($item->amount,0,',','.') . "\n" .
                "Jenis: Pembayaran penuh\n" .
                "Sisa utang: Rp. " . number_format($item->amount,0,',','.') . "\n" .
                "Kepada: " . ($item->payment_from ?? '-') . "\n" .
                "\nMohon segera ditindaklanjuti agar pembayaran tepat waktu. Terima kasih.";
            $this->sendMessage($pesan, $user);
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
                Log::info("Summary reminder utang terkirim ke {$user->no_phone}. Response: {$response}");
            }
            curl_close($curl);
        } catch (\Exception $e) {
            Log::error("Error kirim summary WA utang: " . $e->getMessage());
        }
    }

}
