<?php

use App\Http\Controllers\CallController;
use App\Http\Controllers\IndexCallersController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\TwimlController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::any('twiml', TwimlController::class)->name('twiml');

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'me'], function () {
    Route::get('/', function (Request $request) {
        return $request->user();
    });

    Route::get('organization', [OrganizationController::class, 'show']);
    Route::put('organization', [OrganizationController::class, 'update']);
});

Route::get('/callers', IndexCallersController::class);

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'call'], function () {
    Route::post('/', [CallController::class, 'store']);
    Route::get('/{call}', []);
});
