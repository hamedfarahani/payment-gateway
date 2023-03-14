<?php

use App\Http\Controllers\BankCheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// ip internal gateway
Route::prefix('bank')->group(function () {
    Route::get('checkout/{payment_id}',[BankCheckoutController::class,'showForm'])->name('bank.form.show');
    Route::get('checkout/{payment_id}/redirect',[BankCheckoutController::class,'redirect'])->name('bank.redirect');
});

Route::get('/', function () {
    return view('welcome');
});
