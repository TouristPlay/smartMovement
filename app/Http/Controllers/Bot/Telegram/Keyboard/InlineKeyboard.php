<?php

namespace App\Http\Controllers\Bot\Telegram\Keyboard;

use App\Models\FavoriteStop;

class InlineKeyboard extends Keyboard
{

    protected $user;

    public function __construct($user) {
        $this->user = $user;
    }

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


    /** Получаем избранный остановки пользователя
     * @param $stops
     * @return array
     */
    public function getFavoriteStopMenu($stops): array
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

        $scheduleMenu = [
            'inline_keyboard' => $this->generateStopsScheduleKeyboard($stopsSchedule),
        ];

        $scheduleMenu['inline_keyboard'][] = $this->isFavoriteStop($stopsSchedule['stop'])
            ? $this->deleteFavoriteStopButton($stopsSchedule['stop'])
            : $this->createFavoriteStopButton($stopsSchedule['stop']);


        return $scheduleMenu;
    }


    /** Метод проверяет, есть ли остановка в избранных
     * @param $stop
     * @return bool
     */
    private function isFavoriteStop($stop): bool
    {
        $favorite = FavoriteStop::query()
            ->whereUserId($this->user->id)->whereStopId($stop)->first();

        return !($favorite == null);
    }
}