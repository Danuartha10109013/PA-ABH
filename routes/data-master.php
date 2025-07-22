<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MoneyInController;
use App\Http\Controllers\MoneyOutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\UtangController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::controller(GeneralController::class)->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/profil', 'profil')->name('profil.index');
        Route::post('/profil', 'store')->name('profil.store');
    });
    Route::controller(LaporanController::class)->group(function () {
        Route::get('/laporan-neraca', 'index')->name('laporan.index');
        Route::get('/laporan-money-in', 'laporanMoneyin')->name('laporan.money-in');
        Route::get('/laporan-money-out', 'laporanMoneyout')->name('laporan.money-out');
        Route::get('/laporan-utang', 'laporanUtang')->name('laporan.utang');
        Route::get('/laporan-piutang', 'laporanPiutang')->name('laporan.piutang');
        Route::get('/laporan-cashflow', 'laporanCashflow')->name('laporan.cashflow');
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::get('/payment-category', 'index')->name('payment.index');
        Route::post('/payment-category/store', 'store')->name('payment.store');
        Route::post('/payment-category/update/{payment}', 'update')->name('payment.update');
        Route::post('/payment-category/delete/{payment}', 'destroy')->name('payment.destroy');
    });

    Route::controller(MoneyInController::class)->group(function () {
        Route::get('/money-in', 'index')->name('money-in.index');
        Route::get('/money-in/download/{filename}', 'download')->name('money-in.download');
        Route::post('/money-in/store', 'store')->name('moneyin.store');
        Route::post('/money-in/update/{moneyIn}', 'update')->name('money-in.update');
        // Route::post('/money-in/delete/{moneyIn}', 'destroy')->name('money-in.destroy');
    });

    Route::controller(MoneyOutController::class)->group(function () {
        Route::get('/money-out', 'index')->name('money-out.index');
        Route::get('/money-out/download/{filename}', 'download')->name('money-out.download');
        Route::post('/money-out/store', 'store')->name('moneyout.store');
        Route::post('/money-out/update/{moneyOut}', 'update')->name('money-out.update');
        // Route::post('/money-out/delete/{moneyOut}', 'destroy')->name('money-out.destroy');
    });

    Route::controller(PiutangController::class)->group(function () {
        Route::get('/piutang', 'index')->name('piutang.index');
        Route::post('/piutang/store', 'store')->name('piutang.store');
        Route::post('/piutang/update/{piutang}', 'update')->name('piutang.update');
        Route::post('/piutang/update-status/{piutang}', 'updateStatus')->name('piutang.update-status');
        Route::post('/piutang/installment/update-status/{piutangInstallement}', 'updateStatusInstallment')->name('piutang.installment.update-status');
        // Route::post('/money-out/delete/{moneyOut}', 'destroy')->name('money-out.destroy');
    });

    Route::controller(UtangController::class)->group(function () {
        Route::get('/utang', 'index')->name('utang.index');
        Route::post('/utang/store', 'store')->name('utang.store');
        Route::post('/utang/update/{utang}', 'update')->name('utang.update');
        Route::post('/utang/update-status/{utang}', 'changeStatus')->name('utang.update-status');
        Route::post('/utang/installment/update-status/{utang}', 'changeStatusInstallment')->name('utang.installment.update-status');
        // Route::post('/money-out/delete/{moneyOut}', 'destroy')->name('money-out.destroy');
    });

});