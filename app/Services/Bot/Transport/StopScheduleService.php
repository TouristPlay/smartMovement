<?php

namespace App\Services\Bot\Transport;

use App\Models\City;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class StopScheduleService {


    /**
     * @param $stop
     * @return array
     */
    public function getStopSchedule($stop): array
    {

        $city = City::query()->whereId($stop->city_id)->first();

        $driver = RemoteWebDriver::create(config('app.selenium.url'), DesiredCapabilities::chrome());

        $driver->get('https://yandex.ru/maps/' . $city->city_id . '/' . $city->slug .  '/stops/' . $stop->stop_id);

        sleep(1);

        $schedule = $driver->findElement(WebDriverBy::className("masstransit-brief-schedule-view__vehicles"));

        $sortedSchedule = $this->scheduleHandler($schedule);

        $driver->close();

        return $sortedSchedule;
    }


    /**
     * @param $schedules
     * @return array
     */
    private function scheduleHandler($schedules): array
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
                $key => $this->getTransportSchedule($schedulesByType),
            ];
        }


        return $scheduleCategory;
    }

    /**
     * @param $items
     * @return array
     */
    private function getTransportSchedule($items): array
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



}