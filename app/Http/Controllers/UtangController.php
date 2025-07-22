<?php

namespace App\Http\Controllers;

use App\Models\Utang;
use App\Http\Requests\StoreUtangRequest;
use App\Http\Requests\UpdateUtangRequest;
use App\Models\UtangInstallement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class UtangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $utang = Utang::orderBy("created_at", 'desc');

        if ($request->has('periode')) {
            $periode = $request->periode; // Format: YYYY-MM
            $utang->whereYear('created_at', date('Y', strtotime($periode)))
                ->whereMonth('created_at', date('m', strtotime($periode)));
        }

        $data = [
            "utang" => $utang->get(),
            "title" => "Utang",
            "subtitle" => "Utang",
        ];
        return view("page.data-master.utang", $data);
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
    public function store(StoreUtangRequest $request)
    {
        try {
            DB::beginTransaction();

            Utang::create([
                "amount" => $request->amount,
                "ext_doc_ref" => $request->ext_doc_ref,
                "payment_from" => $request->payment_from,
                "due_date" => $request->due_date,
                "note" => $request->note,
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
    public function show(Utang $utang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Utang $utang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreUtangRequest $request, Utang $utang)
    {
        try {
            DB::beginTransaction();

            $utang->update([
                "amount" => $request->amount,
                "ext_doc_ref" => $request->ext_doc_ref,
                "payment_from" => $request->payment_from,
                "due_date" => $request->due_date,
                "note" => $request->note,
            ]);

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
    public function destroy(Utang $utang)
    {
        //
    }

    /**
     * Change the status of the utang.
     */
    public function changeStatus(Request $request, Utang $utang)
    {
        try {
            DB::beginTransaction();

            $utang->update([
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
    public function changeStatusInstallment(Request $request, UtangInstallement $utang)
    {
        try {
            DB::beginTransaction();

            $utang->update([
                "is_paid" => $request->is_paid ? 1 : 0,
            ]);

            $utangParent = $utang->utang;

            $allPaid = $utangParent->installments()->where('is_paid', false)->doesntExist();

            $utangParent->update([
                'is_paid' => $allPaid ? 1 : 0,
            ]);

            DB::commit();

            notyf()->success("Status berhasil diubah!");

            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            notyf()->error('Gagal Mengubah Status: ' . $e->getMessage());
        }
    }

}