<?php

use App\Livewire\Public\Home;
use App\Livewire\Public\Metodologi;
use App\Livewire\Public\Peta;
use App\Livewire\Public\Placeholder;
use App\Livewire\Public\Statistik;
use App\Livewire\Public\Tentang;
use App\Http\Controllers\Api\WebgisFilterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website
|--------------------------------------------------------------------------
| Landing page publik beserta rute halaman yang akan dikembangkan:
| peta, statistik, tentang, metodologi, tim, aktivitas, faq.
| Untuk saat ini halaman selain beranda menampilkan komponen placeholder.
*/

Route::get('/', Home::class)->name('home');

Route::get('/peta', Peta::class)->name('peta');
Route::get('/statistik', Statistik::class)->name('statistik');
Route::get('/tentang', Tentang::class)->name('tentang');
Route::get('/metodologi', Metodologi::class)->name('metodologi');
Route::get('/tim', Placeholder::class)->name('tim');
Route::get('/aktivitas', Placeholder::class)->name('aktivitas');
Route::get('/faq', Placeholder::class)->name('faq');

/*
|--------------------------------------------------------------------------
| Pergantian Bahasa (ID | EN)
|--------------------------------------------------------------------------
| Menyimpan bahasa aktif ke Session + Cookie, lalu mengembalikan pengguna
| ke halaman sebelumnya. Middleware SetLocale membaca nilai ini.
*/

Route::get('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['id', 'en'], true)) {
        $locale = config('app.locale', 'id');
    }

    session(['locale' => $locale]);
    app()->setLocale($locale);

    return redirect()->back()
        ->withCookie(cookie()->forever('locale', $locale));
})->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Area Aplikasi (auth / dashboard) — akan dipisah ke CMS nanti
|--------------------------------------------------------------------------
*/

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

/*
|--------------------------------------------------------------------------
| WebGIS Dependent Wilayah API (Option A)
|--------------------------------------------------------------------------
| Public JSON endpoints for the cascading Kabupaten -> Kecamatan -> Desa
| filter dropdowns, plus a single real-time statistics endpoint that
| aggregates total_lokasi & total_luas for the deepest active level.
|--------------------------------------------------------------------------
*/

Route::get('/api/get-kecamatan', [WebgisFilterController::class, 'kecamatan'])
    ->name('api.get.kecamatan');
Route::get('/api/get-desa', [WebgisFilterController::class, 'desa'])
    ->name('api.get.desa');
Route::get('/api/get-statistics', [WebgisFilterController::class, 'statistics'])
    ->name('api.get.statistics');

require __DIR__.'/auth.php';

require __DIR__.'/admin.php';
