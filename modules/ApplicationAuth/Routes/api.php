<?php

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
Route::prefix('auth')
->as('api.')
->group(function(){
    Route::post('register')
    ->uses('\\' . config('application-auth.controllers.auth') . '@register')
    ->name('register');
    Route::post('change-password')
    ->uses('\\' . config('application-auth.controllers.auth') . '@changePassword')
    ->name('change-password');
    Route::post('forgot')
    ->uses('\\' . config('application-auth.controllers.auth') . '@forgot')
    ->name('forgot');
    Route::post('reset-password')
    ->uses('\\' . config('application-auth.controllers.auth') . '@resetPassword')
    ->name('reset-password');
    Route::post('login')
    ->uses('\\' . config('application-auth.controllers.auth') . '@login')
    ->name('login');
    Route::post('logout')
    ->uses('\\' . config('application-auth.controllers.auth') . '@logout')
    ->name('logout');
    Route::post('refresh')
    ->uses('\\' . config('application-auth.controllers.auth') . '@refresh')
    ->name('refresh');
});

Route::get('me')
    ->uses('\\' . config('application-auth.controllers.user') . '@show')
    ->name('me.show');
Route::put('me')
    ->uses('\\' . config('application-auth.controllers.user') . '@update')
    ->name('me.update');
