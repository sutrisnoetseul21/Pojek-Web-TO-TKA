<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Portal Routes
|--------------------------------------------------------------------------
| Semua routes untuk peserta tryout
| Prefix URL: /tryout
*/

Route::prefix('tryout')->name('tryout.')->group(function () {
    // Guest routes (belum login)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [StudentController::class, 'showLogin'])->name('login');
        Route::post('/login', [StudentController::class, 'login']);
    });

    // Authenticated routes (sudah login sebagai peserta)
    Route::middleware('auth')->group(function () {
        Route::get('/biodata', [StudentController::class, 'showBiodata'])->name('biodata');
        Route::post('/biodata', [StudentController::class, 'storeBiodata']);

        Route::get('/konfirmasi/{jadwal}', [StudentController::class, 'konfirmasi'])->name('konfirmasi');
        Route::post('/mulai/{jadwal}', [StudentController::class, 'mulai'])->name('mulai');

        Route::get('/soal/{pesertaJadwal}', [StudentController::class, 'soal'])->name('soal');
        Route::post('/jawab', [StudentController::class, 'simpanJawaban'])->name('jawab');
        Route::post('/ragu/{jawaban}', [StudentController::class, 'toggleRagu'])->name('ragu');

        Route::get('/selesai/{pesertaJadwal}', [StudentController::class, 'showSelesai'])->name('selesai');
        Route::post('/submit/{pesertaJadwal}', [StudentController::class, 'submit'])->name('submit');
        Route::get('/hasil/{pesertaJadwal}', [StudentController::class, 'hasil'])->name('hasil');

        Route::match(['get', 'post'], '/logout', [StudentController::class, 'logout'])->name('logout');
    });
});
