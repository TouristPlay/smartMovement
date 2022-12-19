<?php

namespace App\Http\Controllers\Bot\Telegram;

use App\Http\Controllers\Bot\Telegram\Menu\TelegramMenu;
use GuzzleHttp\Exception\GuzzleException;

class Telegram extends TelegramOptions
{

    public function __construct($options)
    {
        parent::__construct($options);
    }

    /**
     * Обрабатывает сообщения пользователя
     *
     * @return void
     * @throws GuzzleException
     */
    public function messageHandler()
    {
        $callbackKey = $this->callbackData['callbackKey'] ?? null;

        // Регистрируем пользователя
        if ($this->message == '/start' && !isset($this->user)) {
            $registration = new Registration($this->options);
            $registration->register();
            return;
        }

        $menu = new TelegramMenu($this->options);

        // Обрабатываем геолокацию
        if (isset($this->user) && isset($this->options['data']['message']['location'])
            || $this->messageIsMenuKey($callbackKey)
            || $this->messageIsMenuKey($this->message)
        ) {

            $location = $this->options['data']['message']['location'] ?? null;

            $menu->handler($location);

            return;
        }

    }

    private function messageIsMenuKey($key) : bool {

        $keys = [
            'stops',
            'deleteFavorite',
            'createFavorite',
            'transports',
            '/favorite',
            'buildRoute',
            'schedule'
        ];

        return in_array($key, $keys);
    }

}