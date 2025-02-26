<?php

use App\Http\Controllers\CashierController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::group(['prefix' => '/order'], function () {
    Route::post('/add', [OrderController::class,'store']);
    Route::post('/checkpreorder', [OrderController::class,'checkPreOrder']);
});
Route::get('/items/get/{userId}/{eventId}',[CashierController::class,'getItems']);
