<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfilRequest;
use App\Models\Beginning;
use App\Models\MoneyIn;
use App\Models\MoneyOut;
use App\Models\Piutang;
use App\Models\Utang;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GeneralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $now = Carbon::now();

        // Ambil dari request kalau ada, kalau nggak pakai default kuartal sekarang
        $startDate = $request->startMonth
            ? Carbon::parse($request->startMonth)->startOfMonth()
            : Carbon::create($now->year, floor(($now->month - 1) / 3) * 3 + 1, 1);

        $endDate = $request->endMonth
            ? Carbon::parse($request->endMonth)->endOfMonth()
            : (clone $startDate)->addMonths(3)->subDay();

        // Query data sesuai range
        $moneyin = MoneyIn::with('category', 'piutangs')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $moneyout = MoneyOut::with('category', 'utangs')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalAmountMoneyIn = 0;
        foreach ($moneyin as $moneyins) {
            $totalAmountMoneyIn += $moneyins->amount;
            foreach ($moneyins->piutangs as $piutang) {
                $totalAmountMoneyIn += $piutang->amount;
            }
        }

        $totalAmountMoneyOut = 0;
        foreach ($moneyout as $moneyouts) {
            $totalAmountMoneyOut += $moneyouts->amount;
            foreach ($moneyouts->utangs as $utang) {
                $totalAmountMoneyOut += $utang->amount;
            }
        }

        $overalltotal = $totalAmountMoneyIn - $totalAmountMoneyOut;
        $monthDiff = intval($startDate->diffInMonths($endDate)) + 1;

        $data = [
            'moneyin' => $moneyin,
            'moneyout' => $moneyout,
            'overalltotal' => $overalltotal,
            'monthDiff' => $monthDiff,
            'totalAmountMoneyIn' => $totalAmountMoneyIn,
            'totalAmountMoneyOut' => $totalAmountMoneyOut,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'saldo' => Beginning::first(),
            'countMoneyIn' => MoneyIn::all(),
            'countMoneyOut' => MoneyOut::all(),
            'countPiutang' => Piutang::all(),
            'countUtang' => Utang::all(),
        ];

        return view("page.index", $data);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function profil()
    {
        $data = [
            "user" => auth()->user(),
        ];
        return view("page.general.profil", $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProfilRequest $request)
    {
        $fileName = "";
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = $file->getClientOriginalName();
            $file->move(public_path('user/photo'), $fileName);
        }

        $user = auth()->user();
        if ($fileName != "") {
            $user->photo = $fileName;
        }
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->no_phone = $request->no_phone;
        $user->save();

        notyf()->success("User berhasil diubah!");
        return redirect()->back();
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
