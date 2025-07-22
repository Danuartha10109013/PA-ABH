<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentCategory;
use App\Http\Requests\UpdatePaymentCategory;
use App\Models\Payment;
use DB;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payment = Payment::orderBy("id","desc")->get();
        $data = [
            "payment" => $payment,
            "title" => "Payment Category"
        ];
        return view("page.data-master.payment-category", $data);
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
    public function store(StorePaymentCategory $request)
    {
        try {
            DB::beginTransaction();

            Payment::create([
                "name" => $request->name,
                "type" => $request->type,
                "user_id" => auth()->user()->id
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
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentCategory $request, Payment $payment)
    {
        try {
            DB::beginTransaction();

            $payment->update([
                "name" => $request->name,
                "type" => $request->type,
                "user_id" => auth()->user()->id
            ]);

            DB::commit();

            notyf()->success("Data berhasil diubah!");

            return redirect()->back();

        } catch (\Throwable $e) {
            DB::rollBack();

            notyf()->error('Gagal Merubah:' . $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        try {
            DB::beginTransaction();

            $payment->delete();

            DB::commit();

            notyf()->success("Data berhasil dihapus!");

            return redirect()->back();

        } catch (\Throwable $e) {
            DB::rollBack();

            notyf()->error('Gagal Menghapus:' . $e->getMessage());

            return redirect()->back();
        }
    }
}
