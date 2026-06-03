<?php

use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopController::class, 'index'])->name('home');
Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
Route::get('/workshops/{session}/register', [WorkshopController::class, 'register'])->name('workshops.register');
