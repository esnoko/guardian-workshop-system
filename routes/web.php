<?php

use App\Http\Controllers\Registration\WorkshopRegistrationController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Workshop\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopController::class, 'index'])->name('home');
Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
Route::get('/workshops/{session}/register', [WorkshopRegistrationController::class, 'create'])->name('workshops.register');
Route::post('/workshops/{session}/register', [WorkshopRegistrationController::class, 'store'])->name('registrations.store');
Route::get('/registrations/{registration}/confirmation', [WorkshopRegistrationController::class, 'confirmation'])
	->middleware('signed')
	->name('registration.confirmation');

Route::get('/registrations/{registration}/payment', [PaymentController::class, 'start'])
	->middleware('signed')
	->name('payment.start');

Route::post('/registrations/{registration}/payment', [PaymentController::class, 'initiate'])
	->middleware('signed')
	->name('payment.initiate');

Route::post('/payments/payfast/itn', [PaymentController::class, 'itn'])
	->name('payment.payfast.itn');

Route::get('/registrations/{registration}/payment/{payment}/complete', [PaymentController::class, 'complete'])
	->name('payment.complete');
