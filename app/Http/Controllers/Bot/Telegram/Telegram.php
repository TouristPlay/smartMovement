<?php

namespace App\Http\Controllers\Bot\Telegram;

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
        $callbackKey = $this->callbackData['data'] ?? null;

        // Регистрируем пользователя
        if ($this->message == '/start' && !isset($this->user)) {
            $registration = new Registration($this->options);
            $registration->register();
            return;
        }

        // Обрабатываем геолокацию
        if (isset($this->user) && isset($this->options['data']['message']['location'])) {

            $location = $this->options['data']['message']['location'];

            dd($location);

            return;
        }

    }

    private function messageIsMenuKey($key) : bool {

        $keys = [

        ];

        return in_array($key, $keys);
    }

}