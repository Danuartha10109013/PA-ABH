<?php

namespace App\Http\Controllers;

use App\Models\Beginning;
use App\Models\MoneyIn;
use App\Models\MoneyOut;
use App\Models\Payment;
use App\Models\Piutang;
use App\Models\Utang;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $periode = $request->get('periode', date('Y-m'));

        $categories =  Payment::all();

        // Ambil transaksi moneyin & moneyout sesuai periode
        $moneyin =  MoneyIn::with('piutang')
            ->whereYear('created_at', substr($periode, 0, 4))
            ->whereMonth('created_at', substr($periode, 5, 2))
            ->get();
        $moneyout = MoneyOut::with('utang')
            ->whereYear('created_at', substr($periode, 0, 4))
            ->whereMonth('created_at', substr($periode, 5, 2))
            ->get();

        $rows = [];
        $totalDebet = 0;
        $totalKredit = 0;
        $no = 1;

        foreach ($categories as $cat) {
            // Debet: jika ada piutang, ambil dari piutang, jika tidak dari moneyin
            $debet = $moneyin->where('category_id', $cat->id)->sum(function($item) {
                return $item->piutang ? $item->piutang->amount : $item->amount;
            });

            // Kredit: jika ada utang, ambil dari utang, jika tidak dari moneyout
            $kredit = $moneyout->where('category_id', $cat->id)->sum(function($item) {
                return $item->utang ? $item->utang->amount : $item->amount;
            });

            $totalDebet += $debet;
            $totalKredit += $kredit;

            if ($debet > 0 || $kredit > 0) {
                $rows[] = [
                    'no' => $no++,
                    'name' => $cat->name,
                    'debet' => $debet,
                    'kredit' => $kredit,
                ];
            }
        }

        return view('page.laporan.index', [
            'rows' => $rows,
            'totalDebet' => $totalDebet,
            'totalKredit' => $totalKredit,
            'periode' => $periode,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function laporanMoneyin(Request $request)
    {
        $periode = $request->get("periode", date("Y-m"));
        $moneyin = MoneyIn::whereYear("created_at", "=", substr($periode, 0, 4))
            ->whereMonth("created_at", "=", substr($periode, 5, 2))
            ->get();

        $data = [
            "moneyin" => $moneyin,
        ];
        return view("page.laporan.money-in", $data);
    }
    public function laporanMoneyout(Request $request)
    {
        $periode = $request->get("periode", date("Y-m"));
        $moneyout = MoneyOut::whereYear("created_at", "=", substr($periode, 0, 4))
            ->whereMonth("created_at", "=", substr($periode, 5, 2))
            ->get();

        $data = [
            "moneyout" => $moneyout,
        ];
        return view("page.laporan.money-out", $data);
    }
    public function laporanPiutang(Request $request)
    {
        $periode = $request->get("periode", date("Y-m"));
        $piutang = Piutang::whereYear("created_at", "=", substr($periode, 0, 4))
            ->whereMonth("created_at", "=", substr($periode, 5, 2))
            ->get();

        $data = [
            "piutang" => $piutang,
        ];
        return view("page.laporan.piutang", $data);
    }
    public function laporanUtang(Request $request)
    {
        $periode = $request->get("periode", date("Y-m"));
        $utang = Utang::whereYear("created_at", "=", substr($periode, 0, 4))
            ->whereMonth("created_at", "=", substr($periode, 5, 2))
            ->get();

        $data = [
            "utang" => $utang,
        ];
        return view("page.laporan.utang", $data);
    }

    public function laporanCashflow(Request $request)
    {
        $saldo_awal = Beginning::first()->amount ?? 0;  
        $periode = request('periode') ?? date('Y-m');
        $moneyin = MoneyIn::whereYear('payment_date', substr($periode, 0, 4))
            ->whereMonth('payment_date', substr($periode, 5, 2))
            ->get()
            ->map(function ($item) {
                return (object) [
                    'date' => $item->payment_date,
                    'description' => $item->category->name ?? '-',
                    'type' => 'in',
                    'amount' => $item->amount,
                ];
            });

        $moneyout = MoneyOut::whereYear('payment_date', substr($periode, 0, 4))
            ->whereMonth('payment_date', substr($periode, 5, 2))
            ->get()
            ->map(function ($item) {
                return (object) [
                    'date' => $item->payment_date,
                    'description' => $item->category->name ?? '-',
                    'type' => 'out',
                    'amount' => $item->amount,
                ];
            });

        $cashflows = $moneyin->concat($moneyout)->sortBy('date')->values();

        return view('page.laporan.cashflow', [
            'saldo_awal' => $saldo_awal, 
            'cashflows' => $cashflows,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
