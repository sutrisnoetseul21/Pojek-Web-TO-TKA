<?php

use App\Http\Controllers\KartuPesertaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Cetak Kartu Peserta - Protected by middleware auth
Route::middleware(['auth'])->group(function () {
    Route::get('/print/kartu-peserta', [KartuPesertaController::class, 'print'])
        ->name('print.kartu-peserta');
});
