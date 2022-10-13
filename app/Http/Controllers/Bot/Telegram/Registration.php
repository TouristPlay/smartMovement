<?php namespace App\Http\Controllers\Bot\Telegram;


use App\Models\User;
use App\Services\Bot\Helper;
use GuzzleHttp\Exception\GuzzleException;


class Registration extends TelegramOptions{


    public function __construct($options)
    {
        parent::__construct($options);
    }


    /**
     * Регистрация пользователя
     * @return void
     * @throws GuzzleException
     */
    public function register() {
        $this->storeUser();
        $this->sendHelloMessage();
    }


    /**
     * @return void
     */
    private function storeUser() {

       User::query()->firstOrCreate([
           'chat_id' => $this->chatId,
           'username' => $this->options['username'],
           'first_name' => $this->options['first_name'],
           'last_name' => $this->options['last_name'],
       ]);

    }

    /**
     * @return void
     * @throws GuzzleException
     */
    private function sendHelloMessage() {
//        $this->telegram->sendMessage($this->chatId, "А я сейчас вам покажу, откуда на Беларусь готовилось нападение (с) Лукашенко", [
//            'reply_markup' => [
//                'keyboard' => [
//                   [
//                     [
//                         'text' => '🗺 Отправить геолокацию ',
//                         'request_location' => true
//                     ]
//                   ]
//                ],
//                'resize_keyboard' => true
//            ]
//        ]);


        $this->telegram->sendPhoto($this->chatId, "https://ibb.co/MMkmYxr", [
            'reply_markup' => [
                'keyboard' => [
                   [
                     [
                         'text' => '🗺 Отправить геолокацию ',
                         'request_location' => true
                     ]
                   ]
                ],
                'resize_keyboard' => true
            ],
            'caption' => Helper::escapingCharacter('А я сейчас вам покажу, откуда на Беларусь готовилось нападение')
        ]);
    }
}