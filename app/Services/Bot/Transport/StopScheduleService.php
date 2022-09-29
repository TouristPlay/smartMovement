<?php

namespace App\Services\Bot\Transport;

use App\Models\City;
use Facebook\WebDriver\Chrome\ChromeOptions;
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

        $desiredCapabilities = DesiredCapabilities::chrome();

        // Disable accepting SSL certificates
        $desiredCapabilities->setCapability('acceptSslCerts', false);

        // Add arguments via FirefoxOptions to start headless firefox
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(['-headless']);
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $driver = RemoteWebDriver::create(config('app.selenium.url'), $desiredCapabilities);

        $driver->get($stop->url);
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