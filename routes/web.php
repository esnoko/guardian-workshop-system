<?php

use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopController::class, 'index'])->name('home');
Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
Route::get('/workshops/{session}/register', [WorkshopController::class, 'register'])->name('workshops.register');
Route::post('/workshops/{session}/register', [WorkshopController::class, 'store'])->name('registrations.store');
Route::get('/registrations/{registration}/confirmation', [WorkshopController::class, 'confirmation'])->name('registration.confirmation');
