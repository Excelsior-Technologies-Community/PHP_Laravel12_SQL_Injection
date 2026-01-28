<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VulnerableUserController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Vulnerable routes
Route::get('/unsafe-raw', [VulnerableUserController::class, 'unsafeSearch'])->name('unsafe.raw');
Route::get('/unsafe-whereraw', [VulnerableUserController::class, 'unsafeWhereRaw'])->name('unsafe.whereRaw');

// Safe routes
Route::get('/safe-parameterized', [VulnerableUserController::class, 'safeParameterized'])->name('safe.parameterized');
Route::get('/safe-eloquent', [VulnerableUserController::class, 'safeEloquent'])->name('safe.eloquent');
Route::get('/safe-querybuilder', [VulnerableUserController::class, 'safeQueryBuilder'])->name('safe.queryBuilder');