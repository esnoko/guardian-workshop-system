<?php

use App\Http\Controllers\Registration\WorkshopRegistrationController;
use App\Http\Controllers\Workshop\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopController::class, 'index'])->name('home');
Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
Route::get('/workshops/{session}/register', [WorkshopRegistrationController::class, 'create'])->name('workshops.register');
Route::post('/workshops/{session}/register', [WorkshopRegistrationController::class, 'store'])->name('registrations.store');
Route::get('/registrations/{registration}/confirmation', [WorkshopRegistrationController::class, 'confirmation'])
	->middleware('signed')
	->name('registration.confirmation');
