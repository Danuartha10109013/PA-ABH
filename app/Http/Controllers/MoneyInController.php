<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMoneyIn;
use App\Http\Requests\UpdateMoneyin;
use App\Models\Beginning;
use App\Models\MoneyIn;
use App\Models\Payment;
use App\Models\Piutang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoneyInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $category = Payment::where("type", 0)->get();
        $moneyin = MoneyIn::orderBy("created_at", 'desc');

        if ($request->has('periode')) {
            $periode = $request->periode; // Format: YYYY-MM
            $moneyin->whereYear('created_at', date('Y', strtotime($periode)))
                ->whereMonth('created_at', date('m', strtotime($periode)));
        }
        
        if ($request->has('category_id') && $request->category_id != '') {
            $moneyin->where('category_id', $request->category_id);
        }
        
        if ($request->has('source') && $request->source != '') {
            $moneyin->where('source', $request->source);
        }

        $data = [
            "category" => $category,
            "moneyin" => $moneyin->get(),
            "title" => "Money In",
            "subtitle" => "Pemasukan",
        ];
        return view("page.data-master.money-in", $data);
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
    public function store(StoreMoneyIn $request)
    {
        try {
            DB::beginTransaction();

            $fileName = "";
            if ($request->hasFile('proof')) {
                $file = $request->file('proof');
                $fileName = $file->getClientOriginalName();
                $file->move(public_path('moneyin/proof'), $fileName);
            }
            $moneyin = MoneyIn::create([
                "category_id" => $request->category_id,
                "amount" => $request->amount,
                "payment_method" => $request->payment_method,
                "source" => $request->source,
                "proof" => $fileName,
                "ext_doc_ref" => $request->ext_doc_ref,
                "payment_from" => $request->payment_from,
                "payment_date" => $request->payment_date,
                "note" => $request->note,
            ]);

            if ($request->piutang == 1) {
                if ($request->type_piutang == 'installment') {
                    $piutang = Piutang::create([
                        "moneyin_id" => $moneyin->trx_id,
                        "amount" => $request->amount_piutang,
                        "ext_doc_ref" => $request->ext_doc_ref_piutang,
                        "payment_from" => $request->payment_from_piutang,
                        "due_date" => $request->due_date_piutang,
                        "note" => $request->note_piutang,
                        "type" => 'installment',
                        "installment_count" => $request->installement_count_piutang,
                    ]);

                    $cicilanCount = (int) $request->installement_count_piutang;
                    $cicilanAmount = $request->amount_piutang / $cicilanCount;

                    for ($i = 0; $i < $cicilanCount; $i++) {
                        $due = \Carbon\Carbon::parse($request->due_date_piutang)->addMonths($i);
                        \App\Models\PiutangInstallement::create([
                            'piutang_collection_id' => $piutang->collection_id,
                            'amount' => $cicilanAmount,
                            'due_date' => $due->toDateString()
                        ]);
                    }
                } else {
                    Piutang::create([
                        "moneyin_id" => $moneyin->trx_id,
                        "amount" => $request->amount_piutang,
                        "ext_doc_ref" => $request->ext_doc_ref_piutang,
                        "payment_from" => $request->payment_from_piutang,
                        "due_date" => $request->due_date_piutang,
                        "note" => $request->note_piutang,
                        "type" => 'full',
                    ]);
                }
            }

            $beginning = Beginning::first();
            $beginning->update([
                'amount' => $beginning->amount + $request->amount,
            ]);

            DB::commit();

            notyf()->success("Data berhasil ditambahkan!");

            return redirect()->back();

        } catch (\Throwable $e) {
            DB::rollBack();

            notyf()->error('Gagal Menyimpan:' . $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MoneyIn $moneyIn)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MoneyIn $moneyIn)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMoneyin $request, MoneyIn $moneyIn)
    {
        try {
            DB::beginTransaction();

            if ($request->hasFile('proof')) {
                $oldFile = public_path('moneyin/proof/' . $moneyIn->proof);
                if (file_exists($oldFile) && is_file($oldFile)) {
                    unlink($oldFile);
                }

                $file = $request->file('proof');
                $fileName = $file->getClientOriginalName();
                $file->move(public_path('moneyin/proof'), $fileName);
                $moneyIn->proof = $fileName;
            }

            $oldAmount = $moneyIn->amount;
            $newAmount = $request->amount;
            $diff = $newAmount - $oldAmount;

            $moneyIn->category_id = $request->category_id;
            $moneyIn->amount = $request->amount;
            $moneyIn->payment_method = $request->payment_method;
            $moneyIn->source = $request->source;
            $moneyIn->ext_doc_ref = $request->ext_doc_ref;
            $moneyIn->payment_from = $request->payment_from;
            $moneyIn->payment_date = $request->payment_date;
            $moneyIn->note = $request->note;
            $moneyIn->save();


            $beginning = Beginning::first();
            $beginning->update([
                'amount' => $beginning->amount + $diff,
            ]);

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
    public function destroy(MoneyIn $moneyIn)
    {
        //
    }

    public function download($filename)
    {
        $filePath = public_path('moneyin/proof/' . $filename);

        if (!file_exists($filePath)) {
            abort(404);
        }
        return response()->download($filePath);
    }
}
