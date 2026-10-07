<?php

use App\Livewire\Admin\About\Index as AboutIndex;
use App\Livewire\Admin\Areas\Index as AreasIndex;
use App\Livewire\Admin\Auth\ForgotPassword;
use App\Livewire\Admin\Auth\Login;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Features\Index as FeaturesIndex;
use App\Livewire\Admin\Footer\Index as FooterIndex;
use App\Livewire\Admin\Hero\Index as HeroIndex;
use App\Livewire\Admin\ImportData\Index as ImportDataIndex;
use App\Livewire\Admin\Media\Index as MediaIndex;
use App\Livewire\Admin\Methodology\Index as MethodologyIndex;
use App\Livewire\Admin\SettlementAreas\Index as SettlementAreasIndex;
use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Livewire\Admin\Statistik\Index as StatistikIndex;
use App\Livewire\Admin\Stats\Index as StatsIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Authentication (guest only)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {   
        Route::get('login', Login::class)->name('login');
        Route::get('password/reset', ForgotPassword::class)->name('password.request');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Area (auth + admin middleware)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    // ➕ BARIS BARU: Redirect /admin langsung ke /admin/dashboard
    Route::redirect('', 'admin/dashboard');

    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::get('hero', HeroIndex::class)->name('hero.index');
    Route::get('about', AboutIndex::class)->name('about.index');
    Route::get('stats', StatsIndex::class)->name('stats.index');
    Route::get('features', FeaturesIndex::class)->name('features.index');
    Route::get('settlement-areas', SettlementAreasIndex::class)->name('settlement-areas.index');
    Route::get('areas', AreasIndex::class)->name('areas.index');
    Route::get('statistik', StatistikIndex::class)->name('statistik.index');
    Route::get('import', ImportDataIndex::class)->name('import.index');
    Route::get('methodology', MethodologyIndex::class)->name('methodology.index');
    Route::get('footer', FooterIndex::class)->name('footer.index');
    Route::get('users', UsersIndex::class)->name('users.index');
    Route::get('media', MediaIndex::class)->name('media.index');
    Route::get('settings', SettingsIndex::class)->name('settings.index');
});