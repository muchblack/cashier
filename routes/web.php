<?php

use App\Http\Controllers\CashierController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test',[TestController::class,'test']);
Route::group(['prefix'=>'cashier'],function(){
    Route::get('/',[CashierController::class,'index']);
    Route::get('/show',[CashierController::class,'show']);
    Route::get('/preorder',[CashierController::class,'preorder']);
});
