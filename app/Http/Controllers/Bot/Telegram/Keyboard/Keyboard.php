<?php

namespace App\Http\Controllers\Bot\Telegram\Keyboard;

use Illuminate\Support\Facades\Log;

class Keyboard
{

    /**
     * Метод создает клавиатуру для отсановок
     * @param $elements
     * @return void
     */
    protected function generateStopsKeyboard($elements) : array {

        $keyboard = [];

        $keyboardRow = [];

        $rowCounter = 1;

        foreach ($elements as $element) {

            $button =  [
                'text' => "🚏 " . $element->name,
                'callback_data' =>  json_encode([
                    'callbackKey' => 'stops',
                    'data' => $element->id
                ]),
            ];

            $keyboardRow[] = $button;

            if ($rowCounter % 2 == 0 || count($elements) == $rowCounter) {
                $keyboard[] = $keyboardRow;
                $keyboardRow = [];
            }

            $rowCounter++;
        }

        return $keyboard;
    }


    /**
     * Метод создает клавиатуру расписания транспорта
     * @param $elements
     * @return array
     */
    protected function generateStopsScheduleKeyboard($elements) : array {

        $transportType = [
            'bus' => '🚌',
            'minibus' => '🚐',
            'trolleybus' => '🚎',
        ];

        $keyboard = [];

        $keyboardRow = [];

        $rowCounter = 1;

        $allRoutes = 0;

        foreach ($elements as $key => $element) {

            if ($key == "stop" || empty($element)) continue;

            $allRoutes += count($element);

            foreach ($element as $i) {
                if (array_key_exists($key, $transportType)) {
                    $button =  [
                        'text' => $transportType[$key] . " " . $i['name'] . " - " . $i['arriveTime'] . " [" . $i['lastStation'] . "]",
                        'callback_data' =>  json_encode([
                            'callbackKey' => 'transport',
                            'data' => 1
                        ]),
                    ];

                    $keyboardRow[] = $button;

                    if ($rowCounter % 1 == 0 || count($elements) == $allRoutes) {
                        $keyboard[] = $keyboardRow;
                        $keyboardRow = [];
                    }

                    $rowCounter++;
                }
            }

        }

        return $keyboard;
    }


    /** Возвращает кнопку для добавление в избранное
     * @param $stop
     * @return array
     */
    protected function createFavoriteStopButton($stop): array
    {
        return  [
            [
                'text' => "🖤 Добавить в избранные",
                'callback_data' =>  json_encode([
                    'callbackKey' => 'createFavorite',
                    'data' => $stop
                ]),
            ]
        ];
    }

    /**Возвращает кнопку для удаление из избранных
     * @param $stop
     * @return array[]
     */
    protected function deleteFavoriteStopButton($stop): array
    {
        return  [
            [
                'text' => "❤ Удалить из избранных",
                'callback_data' =>  json_encode([
                    'callbackKey' => 'deleteFavorite',
                    'data' => $stop
                ]),
            ]
        ];
    }

}