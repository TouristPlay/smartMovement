<?php
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\Http;
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


Route::get('/testSelenium', function () {

    //https://yandex.ru/maps/42/saransk/search/Остановки общественного транспорта/?ll=45.227385%2C54.188223&sspn=0.011381%2C0.008889&z=15

    $serverUrl = 'http://localhost:9515';
    $driver = RemoteWebDriver::create($serverUrl, DesiredCapabilities::chrome());

    //$driver->get('https://yandex.ru/maps/42/saransk/stops/1543186781/?ll=45.180978%2C54.184941&tab=overview&z=15.92');
    $driver->get('https://yandex.ru/maps/42/saransk/?ll=45.189178%2C54.184017&z=18.54');
    sleep(1);

    $search = $driver->findElement(WebDriverBy::className("input__control"))->click();
    $search->sendKeys('Дом Союзов');
    $search->submit();

    dd($search->getLocation());

    $driver->findElement(WebDriverBy::className("search-snippet-view__link-overlay"))->click();


    $schedule = $driver->findElement(WebDriverBy::className("masstransit-brief-schedule-view__vehicles"));

    $sortedSchedule = scheduleHandler($schedule);

    $driver->close();

    dd($sortedSchedule);
});


/**
 * @param $schedules
 * @return array
 */
function scheduleHandler($schedules): array
{

    $types = [
        'bus' => '_type_bus',
        'minibus' => '_type_minibus',
        'trolleybus' => '_type_trolleybus',
    ];

    $scheduleCategory = [];

    foreach ($types as $key => $type) {
        $schedulesByType = $schedules->findElements(WebDriverBy::className($type));

        $scheduleCategory[] = [
            $key => getTransportSchedule($schedulesByType),
        ];
    }


    return $scheduleCategory;
}

/**
 * @param $items
 * @return array
 */
function getTransportSchedule($items): array
{
    $schedule = [];

    foreach ($items as $item) {
        $row = $item->findElement(WebDriverBy::className("masstransit-vehicle-snippet-view__row"));

        $info = $row->findElement(WebDriverBy::className("masstransit-vehicle-snippet-view__info"));
        $routeName = $info->findElement(WebDriverBy::className("masstransit-vehicle-snippet-view__name"))->getText();
        $lastRouteStation = $info->findElement(WebDriverBy::className("masstransit-vehicle-snippet-view__essential-stop"))->getText();

        $prognose = $row->findElement(WebDriverBy::className("masstransit-vehicle-snippet-view__prognoses"));
        $arriveTime = $prognose->findElement(WebDriverBy::className("masstransit-prognoses-view__title"))->getText();

        $schedule[] = [
            'name' => $routeName,
            'lastStation' => $lastRouteStation,
            'arriveTime' => $arriveTime,
        ];
    }

    return $schedule;
}

