<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VulnerableUserController;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');


/*
|--------------------------------------------------------------------------
| Vulnerable Examples
|--------------------------------------------------------------------------
*/

Route::get('/unsafe-raw', [
    VulnerableUserController::class,
    'unsafeSearch'
])->name('unsafe.raw');

Route::get('/unsafe-whereraw', [
    VulnerableUserController::class,
    'unsafeWhereRaw'
])->name('unsafe.whereRaw');


/*
|--------------------------------------------------------------------------
| Safe Examples
|--------------------------------------------------------------------------
*/

Route::get('/safe-parameterized', [
    VulnerableUserController::class,
    'safeParameterized'
])->name('safe.parameterized');

Route::get('/safe-eloquent', [
    VulnerableUserController::class,
    'safeEloquent'
])->name('safe.eloquent');

Route::get('/safe-querybuilder', [
    VulnerableUserController::class,
    'safeQueryBuilder'
])->name('safe.queryBuilder');


/*
|--------------------------------------------------------------------------
| Export CSV Routes
|--------------------------------------------------------------------------
*/

Route::get('/unsafe-raw/export', function () {
    request()->merge(['export' => 1]);
    return app(VulnerableUserController::class)->unsafeSearch(request());
})->name('unsafe.raw.export');


Route::get('/unsafe-whereraw/export', function () {
    request()->merge(['export' => 1]);
    return app(VulnerableUserController::class)->unsafeWhereRaw(request());
})->name('unsafe.whereRaw.export');


Route::get('/safe-parameterized/export', function () {
    request()->merge(['export' => 1]);
    return app(VulnerableUserController::class)->safeParameterized(request());
})->name('safe.parameterized.export');


Route::get('/safe-eloquent/export', function () {
    request()->merge(['export' => 1]);
    return app(VulnerableUserController::class)->safeEloquent(request());
})->name('safe.eloquent.export');


Route::get('/safe-querybuilder/export', function () {
    request()->merge(['export' => 1]);
    return app(VulnerableUserController::class)->safeQueryBuilder(request());
})->name('safe.queryBuilder.export');
