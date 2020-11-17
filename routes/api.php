<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// un authenticated routes
Route::prefix('auth')
    ->namespace('API')
    ->as('auth.')
    ->group(function () {
        Route::post('login', 'Auth\AuthController@login');
        Route::post('forgot', 'Auth\AuthController@forgot');
        Route::post('reset-password', 'Auth\AuthController@resetPassword');
    });

// authenticated routes
Route::namespace('API')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('devices', 'DeviceController@index');
        Route::get('devices-logs', 'DeviceLogController@index');
        Route::post('logout', 'Auth\AuthController@logout');
    });
