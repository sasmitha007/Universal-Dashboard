<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/data/{dataset}', [DashboardController::class, 'data'])
    ->where('dataset', '[A-Za-z0-9_-]+')
    ->name('dashboard.data');
