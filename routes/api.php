<?php

use App\Http\Controllers\ItemsController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::group(['prefix' => '/order'], function () {
    Route::post('/add', [OrderController::class,'store']);
    Route::post('/preorder', [OrderController::class,'savePreOrder']);
    Route::get('/getpreorder/{userId}/{eventId}', [OrderController::class,'getPreOrder']);
    Route::post('/checkpreorder/{orderID}', [OrderController::class,'checkPreOrder']);
    Route::post('/rollbackpreorder/{orderID}', [OrderController::class,'rollbackPreOrder']);
    Route::post('/deletepreorder/{orderID}', [OrderController::class,'delPreOrder']);
});
Route::get('/items/get/{userId}/{eventId}',[ItemsController::class,'getItems']);
