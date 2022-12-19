<?php

use App\Models\Stop;
use App\Services\Bot\Helper;
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

Route::group(['prefix' => 'autocomplete'], function () {
   Route::get('/stop', [\App\Jobs\SyncStop::class, 'handle']);
   Route::get('/transport', [\App\Jobs\SyncTransport::class, 'handle']);
});


Route::get('/tester', function () {

    $stopService = new \App\Services\Bot\Transport\StopService();

    $stopService->getStopsAroundUser([
        'longitude' => 45.224284,
        'latitude' => 54.185382,
    ]);
});