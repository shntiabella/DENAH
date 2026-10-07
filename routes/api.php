<?php

use App\Http\Controllers\Api\AdministrativeAreaController;
use App\Http\Controllers\Api\SettlementAreaController;
use App\Http\Controllers\Api\SettlementStatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API untuk Peta Publik (tanpa auth, hanya data published)
|--------------------------------------------------------------------------
*/

Route::get('/settlement-areas', [SettlementAreaController::class, 'index']);
Route::get('/settlement-areas/{settlementArea}', [SettlementAreaController::class, 'show']);
Route::get('/settlement-stats', [SettlementStatsController::class, 'index']);
Route::get('/settlement-geojson-chunk', [SettlementStatsController::class, 'geojsonChunk']);

Route::get('/administrative-areas/{level}', [AdministrativeAreaController::class, 'index'])
    ->whereIn('level', ['provinsi', 'kabupaten', 'kecamatan', 'desa']);
Route::get('/administrative-areas/{level}/{area}', [AdministrativeAreaController::class, 'show'])
    ->whereIn('level', ['provinsi', 'kabupaten', 'kecamatan', 'desa']);
