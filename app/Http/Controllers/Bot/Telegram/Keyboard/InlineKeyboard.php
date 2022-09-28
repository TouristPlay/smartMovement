<?php

namespace App\Http\Controllers\Bot\Telegram\Keyboard;

class InlineKeyboard extends Keyboard
{

    /**
     * @return void
     */
    public function getMainMenu() {

    }


    /**
     * @param $stops
     * @return array[]
     */
    public function getAroundStopsMenu($stops): array
    {
        return [
            'inline_keyboard' => $this->generateStopsKeyboard($stops),
        ];
    }


    /**
     * @param $stopsSchedule
     * @return array
     */
    public function getStopScheduleMenu($stopsSchedule): array
    {
        return [
            'inline_keyboard' => $this->generateStopsScheduleKeyboard($stopsSchedule),
        ];
    }

}