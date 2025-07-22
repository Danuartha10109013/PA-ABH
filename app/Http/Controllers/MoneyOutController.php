<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMoneyOut;
use App\Http\Requests\UpdateMoneyout;
use App\Models\Beginning;
use App\Models\MoneyOut;
use App\Models\Payment;
use App\Models\Utang;
use App\Models\UtangInstallement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoneyOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $category = Payment::where("type", 1)->get();
        $moneyout = MoneyOut::orderBy("created_at", 'desc');

        if ($request->has('periode')) {
            $periode = $request->periode;
            $moneyout->whereYear('created_at', date('Y', strtotime($periode)))
                ->whereMonth('created_at', date('m', strtotime($periode)));
        }

        $data = [
            "category" => $category,
            "moneyout" => $moneyout->get(),
            "title" => "Money Out",
            "subtitle" => "Pengeluaran",
        ];
        return view("page.data-master.money-out", $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMoneyOut $request)
    {
        try {
            DB::beginTransaction();

            $fileName = "";
            if ($request->hasFile('proof')) {
                $file = $request->file('proof');
                $fileName = $file->getClientOriginalName();
                $file->move(public_path('moneyout/proof'), $fileName);
            }
            $beginning = Beginning::first();

            $amount = $request->amount;
            $taxPercentage = $request->tax ?? 0;
            $taxAmount = ($amount * $taxPercentage) / 100;
            $total = $amount + $taxAmount;

            $beginning->update([
                'amount' => $beginning->amount - $total,
            ]);

            $moneyout = MoneyOut::create([
                "category_id" => $request->category_id,
                "amount" => $total,
                "payment_method" => $request->payment_method,
                "proof" => $fileName,
                "ext_doc_ref" => $request->ext_doc_ref,
                "payment_to" => $request->payment_to,
                "payment_date" => $request->payment_date,
                "note" => $request->note,
                "tax" => $taxPercentage,
            ]);

            if ($request->utang == 1) {
                if ($request->type == 'installment') {
                    $utang = Utang::create([
                        "moneyout_id" => $moneyout->trx_id,
                        "amount" => $request->amount_utang,
                        "ext_doc_ref" => $request->ext_doc_ref_utang,
                        "payment_from" => $request->payment_from_utang,
                        "due_date" => $request->due_date_utang,
                        "note" => $request->note_utang,
                        "type" => 'installment',
                        "installment_count" => $request->installement_count
                    ]);

                    $cicilanCount = (int) $request->installement_count;
                    $cicilanAmount = $request->amount_utang / $cicilanCount;

                    for ($i = 0; $i < $cicilanCount; $i++) {
                        $due = Carbon::parse($request->due_date_utang)->addMonths($i);
                        UtangInstallement::create([
                            'utang_trx_id' => $utang->trx_id,
                            'amount' => $cicilanAmount,
                            'due_date' => $due->toDateString()
                        ]);
                    }
                } else {
                    Utang::create([
                        "moneyout_id" => $moneyout->trx_id,
                        "amount" => $request->amount_utang,
                        "ext_doc_ref" => $request->ext_doc_ref_utang,
                        "payment_from" => $request->payment_from_utang,
                        "due_date" => $request->due_date_utang,
                        "note" => $request->note_utang,
                    ]);
                }
            }

            DB::commit();

            notyf()->success("Data berhasil ditambahkan!");

            return redirect()->back();

        } catch (\Throwable $e) {
            DB::rollBack();

            notyf()->error('Gagal Menyimpan: ' . $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(MoneyOut $moneyOut)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MoneyOut $moneyOut)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMoneyout $request, MoneyOut $moneyOut)
    {
        try {
            DB::beginTransaction();

            if ($request->hasFile('proof')) {
                $oldFile = public_path('moneyout/proof/' . $moneyOut->proof);
                if (file_exists($oldFile) && is_file($oldFile)) {
                    unlink($oldFile);
                }

                $file = $request->file('proof');
                $fileName = $file->getClientOriginalName();
                $file->move(public_path('moneyout/proof'), $fileName);
                $moneyOut->proof = $fileName;
            }

            // Hitung total lama dan total baru
            $oldAmount = $moneyOut->amount; // Sudah termasuk pajak
            $amount = $request->amount;
            $taxPercentage = $request->tax ?? 0;
            $taxAmount = ($amount * $taxPercentage) / 100;
            $total = $amount + $taxAmount;

            // Hitung selisih
            $diff = $total - $oldAmount;

            // Update saldo awal (beginning)
            $beginning = Beginning::first();
            $beginning->update([
                'amount' => $beginning->amount - $diff,
            ]);

            // Update data money out
            $moneyOut->category_id = $request->category_id;
            $moneyOut->amount = $total;
            $moneyOut->payment_method = $request->payment_method;
            $moneyOut->ext_doc_ref = $request->ext_doc_ref;
            $moneyOut->payment_to = $request->payment_to;
            $moneyOut->payment_date = $request->payment_date;
            $moneyOut->note = $request->note;
            $moneyOut->tax = $taxPercentage;
            $moneyOut->save();

            DB::commit();

            notyf()->success("Data berhasil diupdate!");

            return redirect()->back();

        } catch (\Throwable $e) {
            DB::rollBack();

            notyf()->error('Gagal mengupdate: ' . $e->getMessage());

            return redirect()->back();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MoneyOut $moneyOut)
    {
        //
    }

    public function download($filename)
    {
        $filePath = public_path('moneyout/proof/' . $filename);

        if (!file_exists($filePath)) {
            abort(404);
        }
        return response()->download($filePath);
    }

    public function calculateTax()
    {

    }
}
