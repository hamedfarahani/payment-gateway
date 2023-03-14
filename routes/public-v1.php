<?php
// redirect route for checkout
use Illuminate\Support\Facades\Route;

Route::get('payment/{payment_id}/callback', [\App\Http\Controllers\BankCheckoutController::class,'callback'])
    ->name('payment.callback')->whereNumber('payment_id');