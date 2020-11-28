<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('device', 'DeviceCrudController');
    Route::crud('devicelog', 'DeviceLogCrudController');
    Route::crud('devicerequest', 'DeviceRequestCrudController');
    Route::crud('user', 'UserCrudController');

    Route::get('devicerequest/receive/{deviceRequest}','DeviceRequestCrudController@receiveDevice');
    Route::get('devicerequest/collect/{deviceRequest}','DeviceRequestCrudController@pleaseCollect');
    Route::get('devicerequest/deliver/{deviceRequest}','DeviceRequestCrudController@deliverDevice');
}); // this should be the absolute last line of this file
