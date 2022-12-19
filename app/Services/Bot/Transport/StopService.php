<?php

namespace App\Services\Bot\Transport;

use App\Models\Stop;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

class StopService
{

    /**
     * Метод получает остановки
     * @param array $coordinate
     * @return array
     */
    public function getStopsAroundUser(array $coordinate): array
    {

        $radius = 0.5;

        return Stop::getCordBetweenDistance($coordinate['latitude'], $coordinate['longitude'], $radius);
    }
}