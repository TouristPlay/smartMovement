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

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test', function () {

    $job = new \App\Jobs\SyncTransport();

    $job->handle();



//   $coords = [
//       "latitude" => 54.18746,
//       "longitude" => 45.179399
//   ];
//
//
//   $stopsService = new \App\Services\Bot\Transport\StopService();
//
//   $aroundStops = $stopsService->getStopsAroundUser($coords);
//
//   $scheduleService = new \App\Services\Bot\Transport\StopScheduleService();
//
//   //dd($aroundStops);
//    $stop = Stop::whereId(5)->first();
//
//    $stopSchedule = $scheduleService->getStopSchedule($stop);
//
//print_r(json_encode($stopSchedule, JSON_UNESCAPED_UNICODE));
//    $telegram = new \App\Services\Api\Telegram\TelegramMethods();
//
//    $message = "*Остановка {$stop->name}* \n\n";
//
//    foreach ($stopSchedule as $schedule) {
//
//        foreach ($schedule as $key => $item) {
//
//            $message .= "*{$key}* \n\n";
//
//            foreach ($item as $route) {
//                $message .= "Маршрут {$route['name']}  {$route['lastStation']} \- " . Helper::escapingCharacter($route['arriveTime']) . " \n\n";
//            }
//
//        }
//
//    }
//
//    $telegram->sendMessage('422253717', $message, [], false);


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