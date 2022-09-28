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
   Route::post('/stop', \App\Jobs\SyncStop::class);
   Route::post('/transport', \App\Jobs\SyncTransport::class);
});

// TODO подумать как хранить роуты маршрутов или хранить в каждой остановке его роуты
// TODO nested tree
// TODO посмотреть метод отправки карты пользователю в тг
// TODO хранить именование траспорта с его ID bus_66
// TODO маршрут от и до
// TODO хранить у каждой остановке транспорт или хранить маршрут транспорта отдельно
//
//
//\App\Model\User::whereNotIn('id', $ids)
//    ->where('status', 1)
//    ->whereHas('user_location', function($q) use ($radius, $coordinates) {
//        $q->whereRaw("111.045*haversine(latitude, longitude, '{$coordinates['latitude']}', '{$coordinates['longitude']}') <= " . $radius]);
//     })->select('id', 'firstname')
//    ->get();