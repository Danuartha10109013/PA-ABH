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
        Log::info(Piutang::where('owner_phone', '!=', null)->get());
        // PIUTANG PENUH JATUH TEMPO BESOK
        $piutang_biasa_besok = Piutang::where('type', '!=', 'installment')
            ->whereDate('due_date', $besok)
            ->where('is_paid', false)
            ->where('reminded_besok', 0)
            ->get();
        if ($piutang_biasa_besok->count() > 0) {
            $this->sendMessagePiutangPenuhBesok($piutang_biasa_besok, $user, $now);
            Log::info('Jumlah piutang jatuh tempo besok: ' . $piutang_biasa_besok->count());
            foreach ($piutang_biasa_besok as $piutang) {
                Log::info('Loop piutang_biasa_besok: ' . $piutang->collection_id);
                Log::info('Cek owner_phone: ' . ($piutang->owner_phone ?? 'NULL') . ' | ID: ' . $piutang->collection_id);
                if (!empty($piutang->owner_phone)) {
                    Log::info('Kirim reminder jatuh tempo besok ke owner: ' . $piutang->owner_phone . ' | ID: ' . $piutang->collection_id);
                    $this->sendMessagePiutangPenuhBesokOwner($piutang, $piutang->owner_phone, $now);
                }
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
            foreach ($piutang_biasa_terlambat as $piutang) {
                Log::info('Loop piutang_biasa_terlambat: ' . $piutang->collection_id);
                if (!empty($piutang->owner_phone)) {
                    Log::info('Kirim reminder overdue ke owner: ' . $piutang->owner_phone . ' | ID: ' . $piutang->collection_id);
                    $this->sendMessagePiutangPenuhOverdueOwner($piutang, $piutang->owner_phone, $now);
                }
            }
            // sleep(60);
        }

        // CICILAN OVERDUE
        $cicilan_terlambat = PiutangInstallement::where('is_paid', 0)
            ->whereDate('due_date', '<', $hari_ini)
            ->with(['piutang' => function($q) { $q->where('is_paid', 0); }])
            ->get();
        if ($cicilan_terlambat->count() > 0) {
            $this->sendMessagePiutangCicilanOverdue($cicilan_terlambat, $user, $now);
            foreach ($cicilan_terlambat as $cicilan) {
                Log::info('Loop cicilan_terlambat: ' . $cicilan->id);
                $ownerPhone = $cicilan->piutang->owner_phone ?? null;
                if (!empty($ownerPhone)) {
                    Log::info('Kirim reminder cicilan overdue ke owner: ' . $ownerPhone . ' | ID Cicilan: ' . $cicilan->id);
                    $this->sendMessagePiutangCicilanOverdueOwner($cicilan, $ownerPhone, $now);
                }
            }
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
                Log::info('Loop cicilan_besok: ' . $cicil->id);
                $ownerPhone = $cicil->piutang->owner_phone ?? null;
                if (!empty($ownerPhone)) {
                    Log::info('Kirim reminder cicilan jatuh tempo besok ke owner: ' . $ownerPhone . ' | ID Cicilan: ' . $cicil->id);
                    $this->sendMessagePiutangCicilanBesokOwner($cicil, $ownerPhone, $now);
                }
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
        $pesan = "*Reminder Pembayaran Piutang Jatuh Tempo Besok*\n";
        $pesan .= "Tanggal: " . $now->format('d/m/Y H:i') . " WIB\n";
        foreach ($piutangList as $item) {
            $pesan .= "Yth. Pemilik hutang,\n";
            $pesan .= "ID Piutang: {$item->collection_id}\n";
            $pesan .= "Jumlah Tagihan: Rp. " . number_format($item->amount,0,',','.') . "\n";
            $pesan .= "Sisa Piutang: Rp. " . number_format($item->amount,0,',','.') . "\n";
            $pesan .= "Dari: " . ($item->payment_from ?? '-') . "\n";
            $pesan .= "Jatuh tempo besok. Mohon segera melakukan pembayaran agar tidak terkena denda atau sanksi. Terima kasih.\n\n";
            $total += $item->amount;
        }
        $pesan .= "Total nominal piutang jatuh tempo besok: Rp. " . number_format($total,0,',','.') . "\n";
        $pesan .= "Pesan ini dikirim otomatis sebagai pengingat pembayaran.\n";
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

    /**
     * Reminder jatuh tempo besok ke pemilik hutang (pembayaran penuh)
     */
    private function sendMessagePiutangPenuhBesokOwner($piutang, $ownerPhone, $now)
    {
        $ownerPhone = $this->formatPhone($ownerPhone);
        if (empty($ownerPhone)) {
            Log::warning('Nomor owner kosong setelah format, ID: ' . $piutang->collection_id);
            return;
        }
        Log::info('Akan kirim ke owner: ' . $ownerPhone . ' | ID: ' . $piutang->collection_id);
        $pesan = "*Reminder Pembayaran Piutang Jatuh Tempo Besok (Personal)*\n";
        $pesan .= "Tanggal: " . $now->format('d/m/Y H:i') . " WIB\n";
        $pesan .= "Yth. Pemilik hutang,\n";
        $pesan .= "ID Piutang: {$piutang->collection_id}\n";
        $pesan .= "Jumlah Tagihan: Rp. " . number_format($piutang->amount,0,',','.') . "\n";
        $pesan .= "Sisa Piutang: Rp. " . number_format($piutang->amount,0,',','.') . "\n";
        $pesan .= "Dari: " . ($piutang->payment_from ?? '-') . "\n";
        $pesan .= "Jatuh tempo besok. Mohon segera melakukan pembayaran agar tidak terkena denda atau sanksi. Terima kasih.\n";
        $pesan .= "Pesan ini dikirim otomatis sebagai pengingat pembayaran.\n";
        $this->sendMessages($pesan, $ownerPhone, 'owner');
    }

    /**
     * Reminder overdue ke pemilik hutang (pembayaran penuh)
     */
    private function sendMessagePiutangPenuhOverdueOwner($piutang, $ownerPhone, $now)
    {
        $ownerPhone = $this->formatPhone($ownerPhone);
        if (empty($ownerPhone)) {
            Log::warning('Nomor owner kosong setelah format, ID: ' . $piutang->collection_id);
            return;
        }
        Log::info('Akan kirim ke owner: ' . $ownerPhone . ' | ID: ' . $piutang->collection_id);
        $pesan = "*Reminder Pembayaran Piutang Overdue (Personal)*\n";
        $pesan .= "Tanggal: " . $now->format('d/m/Y H:i') . " WIB\n";
        $pesan .= "Yth. Pemilik hutang,\n";
        $pesan .= "ID Piutang: {$piutang->collection_id}\n";
        $pesan .= "Jumlah Tagihan: Rp. " . number_format($piutang->amount,0,',','.') . "\n";
        $pesan .= "Sisa Piutang: Rp. " . number_format($piutang->amount,0,',','.') . "\n";
        $pesan .= "Dari: " . ($piutang->payment_from ?? '-') . "\n";
        $pesan .= "Tagihan ini SUDAH LEWAT JATUH TEMPO. Mohon segera melakukan pembayaran agar tidak terkena denda atau sanksi.\n";
        $pesan .= "Pesan ini dikirim otomatis sebagai pengingat pembayaran.\n";
        $this->sendMessages($pesan, $ownerPhone, 'owner');
    }

    /**
     * Reminder overdue ke pemilik hutang (cicilan)
     */
    private function sendMessagePiutangCicilanOverdueOwner($cicilan, $ownerPhone, $now)
    {
        $ownerPhone = $this->formatPhone($ownerPhone);
        if (empty($ownerPhone)) {
            Log::warning('Nomor owner kosong setelah format, ID Cicilan: ' . $cicilan->id);
            return;
        }
        Log::info('Akan kirim ke owner: ' . $ownerPhone . ' | ID Cicilan: ' . $cicilan->id);
        $piutang = $cicilan->piutang;
        $pesan = "*Reminder Pembayaran Cicilan Piutang Overdue (Personal)*\n";
        $pesan .= "Tanggal: " . $now->format('d/m/Y H:i') . " WIB\n";
        $pesan .= "Yth. Pemilik hutang,\n";
        $pesan .= "ID Piutang: {$piutang->collection_id}\n";
        $pesan .= "Cicilan ke: [ID Cicilan: {$cicilan->id}]\n";
        $pesan .= "Jumlah Cicilan: Rp. " . number_format($cicilan->amount,0,',','.') . "\n";
        $pesan .= "Sisa Piutang: Rp. " . number_format($piutang->installments()->where('is_paid', false)->sum('amount'),0,',','.') . "\n";
        $pesan .= "Dari: " . ($piutang->payment_from ?? '-') . "\n";
        $pesan .= "Cicilan ini SUDAH LEWAT JATUH TEMPO. Mohon segera melakukan pembayaran agar tidak terkena denda atau sanksi.\n";
        $pesan .= "Pesan ini dikirim otomatis sebagai pengingat pembayaran.\n";
        $this->sendMessages($pesan, $ownerPhone, 'owner');
    }

    /**
     * Reminder jatuh tempo besok ke pemilik hutang (cicilan)
     */
    private function sendMessagePiutangCicilanBesokOwner($cicilan, $ownerPhone, $now)
    {
        $ownerPhone = $this->formatPhone($ownerPhone);
        if (empty($ownerPhone)) {
            Log::warning('Nomor owner kosong setelah format, ID Cicilan: ' . $cicilan->id);
            return;
        }
        Log::info('Akan kirim ke owner: ' . $ownerPhone . ' | ID Cicilan: ' . $cicilan->id);
        $piutang = $cicilan->piutang;
        $pesan = "*Reminder Pembayaran Cicilan Piutang Jatuh Tempo Besok (Personal)*\n";
        $pesan .= "Tanggal: " . $now->format('d/m/Y H:i') . " WIB\n";
        $pesan .= "Yth. Pemilik hutang,\n";
        $pesan .= "ID Piutang: {$piutang->collection_id}\n";
        $pesan .= "Cicilan ke: [ID Cicilan: {$cicilan->id}]\n";
        $pesan .= "Jumlah Cicilan: Rp. " . number_format($cicilan->amount,0,',','.') . "\n";
        $pesan .= "Sisa Piutang: Rp. " . number_format($piutang->installments()->where('is_paid', false)->sum('amount'),0,',','.') . "\n";
        $pesan .= "Dari: " . ($piutang->payment_from ?? '-') . "\n";
        $pesan .= "Cicilan ini akan jatuh tempo besok. Mohon segera melakukan pembayaran agar tidak terkena denda atau sanksi.\n";
        $pesan .= "Pesan ini dikirim otomatis sebagai pengingat pembayaran.\n";
        $this->sendMessages($pesan, $ownerPhone, 'owner');
    }

    /**
     * Format nomor telepon ke format internasional (62...)
     */
    private function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone); // hanya angka
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    private function sendMessages($pesan, $target, $targetType = 'cfo')
    {
        try {
            $token = 'sRe8VoXMYiM8fhcxHAFq';
            $curl = curl_init();
            $targetPhone = is_string($target) ? $target : ($target->no_phone ?? null);
            if (empty($targetPhone)) {
                Log::warning('Nomor target kosong, tidak mengirim pesan. TargetType: ' . $targetType);
                return;
            }
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => array(
                    'target' => $targetPhone,
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
                Log::error("Gagal kirim ke {$targetPhone} (target: {$targetType}): {$error_msg}");
            } else {
                Log::info("Reminder piutang terkirim ke {$targetPhone} (target: {$targetType}). Response: {$response}");
            }
            curl_close($curl);
        } catch (\Exception $e) {
            Log::error("Error kirim summary WA piutang (target: {$targetType}): " . $e->getMessage());
        }
    }
}
