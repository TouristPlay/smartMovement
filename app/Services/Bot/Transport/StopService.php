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
        $aroundStop = [];

        // TODO искать город по близости
        $stops = Stop::query()->whereCityId(1)->get();

        foreach ($stops as $stop) {

            $distance = $this->getCordBetweenDistance(
                $coordinate['latitude'],
                $coordinate['longitude'],
                $stop->latitude,
                $stop->longitude,
            );

            if ($distance <= 500) {
                $aroundStop[] = $stop;
            }
        }

        return $aroundStop;
    }


    /**
     * Получаем расстояние между двумя координатам
     * @param $latitudeOne
     * @param $longitudeOne
     * @param $latitudeTwo
     * @param $longitudeTwo
     * @return int
     */
    private function getCordBetweenDistance($latitudeOne, $longitudeOne, $latitudeTwo, $longitudeTwo): int
    {

        $earthRadius = 3958.75;

        $dLat = deg2rad($latitudeTwo - $latitudeOne);
        $dLng = deg2rad($longitudeTwo - $longitudeOne);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($latitudeOne)) * cos(deg2rad($latitudeTwo)) *
            sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $dist = $earthRadius * $c;

        // from miles
        $meterConversion = 1609;
        $pointDistance = $dist * $meterConversion;

        return (int) round($pointDistance);
    }
}