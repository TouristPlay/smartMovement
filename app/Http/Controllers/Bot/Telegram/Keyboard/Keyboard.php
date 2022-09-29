<?php

namespace App\Http\Controllers\Bot\Telegram\Keyboard;

class Keyboard
{

    /**
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
     * @param $elements
     * @return array
     */
    public function generateStopsScheduleKeyboard($elements) : array {

        $transportType = [
            'bus' => '🚌',
            'minibus' => '🚐',
            'trolleybus' => '🚎',
        ];

        $keyboard = [];

        $keyboardRow = [];

        $rowCounter = 1;

        $allRoutes = 0;

        foreach ($elements as  $element) {
            foreach ($element as $key => $item) {

                $allRoutes += count($element);

                foreach ($item as $i) {
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

        }
        return $keyboard;
    }

}