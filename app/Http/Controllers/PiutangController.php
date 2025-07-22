<?php

namespace App\Http\Controllers;

use App\Models\Piutang;
use App\Http\Requests\StorePiutangRequest;
use App\Http\Requests\UpdatePiutangRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PiutangInstallement;

class PiutangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $piutang = Piutang::orderBy("created_at", 'desc');

        if ($request->has('periode')) {
            $periode = $request->periode; // Format: YYYY-MM
            $piutang->whereYear('created_at', date('Y', strtotime($periode)))
                    ->whereMonth('created_at', date('m', strtotime($periode)));
        }

        $data = [
            "piutang" => $piutang->get(),
            "title" => "Piutang",
            "subtitle" => "Piutang",
        ];
        return view("page.data-master.piutang", $data);
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
    public function store(StorePiutangRequest $request)
    {
        try{
            DB::beginTransaction();

            Piutang::create([
                "amount" => $request->amount,
                "ext_doc_ref" => $request->ext_doc_ref,
                "payment_from" => $request->payment_from,
                "due_date" => $request->due_date,
                "note" => $request->note,
                "status"=> 1,
            ]);

            DB::commit();

            notyf()->success("Data berhasil ditambahkan!");

            return redirect()->back();

        } catch (\Throwable $e) {
            DB::rollBack();

            notyf()->error('Gagal Menyimpan:' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Piutang $piutang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Piutang $piutang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePiutangRequest $request, Piutang $piutang)
    {
        try{
            DB::beginTransaction();

            $updateData = [
                "amount" => $request->amount,
                "ext_doc_ref" => $request->ext_doc_ref,
                "payment_from" => $request->payment_from,
                "due_date" => $request->due_date,
                "note" => $request->note,
                "type" => $request->type ?? $piutang->type,
                "installment_count" => $request->installement_count_piutang ?? null,
            ];

            $piutang->update($updateData);

            // Handle cicilan jika type installment
            if (($request->type ?? $piutang->type) == 'installment') {
                // Hapus semua cicilan lama
                $piutang->installments()->delete();
                $cicilanCount = (int) ($request->installement_count_piutang ?? $piutang->installment_count);
                $cicilanAmount = $request->amount / $cicilanCount;
                for ($i = 0; $i < $cicilanCount; $i++) {
                    $due = \Carbon\Carbon::parse($request->due_date)->subMonths($cicilanCount - ($i + 1));
                    \App\Models\PiutangInstallement::create([
                        'piutang_collection_id' => $piutang->collection_id,
                        'amount' => $cicilanAmount,
                        'due_date' => $due->toDateString()
                    ]);
                }
            } else {
                // Jika type bukan installment, hapus semua cicilan
                $piutang->installments()->delete();
            }

            DB::commit();

            notyf()->success("Data berhasil diubah!");

            return redirect()->back();

        } catch (\Throwable $e) {
            DB::rollBack();

            notyf()->error('Gagal Menyimpan:' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Piutang $piutang)
    {
        //
    }

    /**
     * Change the status of the piutang.
     */
    public function updateStatus(Request $request, Piutang $piutang)
    {
        try {
            DB::beginTransaction();

            $piutang->update([
                "is_paid" => $request->is_paid ? 1 : 0,
            ]);

            DB::commit();

            notyf()->success("Status berhasil diubah!");

            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();

            notyf()->error('Gagal Mengubah Status:' . $e->getMessage());
        }
    }

    public function updateStatusInstallment(Request $request, PiutangInstallement $piutangInstallement)
    {
        try {
            DB::beginTransaction();

            $piutangInstallement->update([
                "is_paid" => $request->is_paid ? 1 : 0,
            ]);

            $piutangParent = $piutangInstallement->piutang;
            $allPaid = $piutangParent->installments()->where('is_paid', false)->doesntExist();
            $piutangParent->update([
                'is_paid' => $allPaid ? 1 : 0,
            ]);

            DB::commit();

            notyf()->success("Status berhasil diubah!");

            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            notyf()->error('Gagal Mengubah Status: ' . $e->getMessage());
        }
    }
}
