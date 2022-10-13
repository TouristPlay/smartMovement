<?php

namespace App\Http\Controllers\Bot\Webhooks;

use App\Jobs\TelegramHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class TelegramWebhook
{

    protected array $data;

    /**
     * Устанавливает json данные для бота
     *
     * @param $data
     * @return void
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * Получает json данные от бота
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Принимает json от бота и обрабатывает
     *
     * @param Request $request
     * @return void
     */
    public function webhook(Request $request)
    {

        $this->setData($request->all());

        $options = [
            'data' => $this->getData(),
            'chat_id' => $this->getChatId(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'username' => $this->getUsername(),
            'message' => [
                'text' => $this->getMessage(),
                'callbackData' => $this->getCallbackData(),
            ]
        ];

        TelegramHandler::dispatch($options);
    }

    /**
     *  Метод для получения ID чата
     * @return mixed
     */
    protected function getChatId()
    {
        if (isset($this->getData()['message']['chat']['id'])) {
            return $this->getData()['message']['chat']['id'];
        }

        return $this->getData()['callback_query']['message']['chat']['id'];
    }


    /**
     * Метод для получения сообщение пользователя
     * @return mixed
     */
    protected function getMessage()
    {
        if (isset($this->getData()['message']['text'])) {
            return $this->getData()['message']['text'];
        }

        return $this->getData()['callback_query']['message']['text'] ?? null;
    }

    /**
     * Метод для получения получения данных обратного вызова (при нажатии кнопки пользователем)
     * @return mixed|null
     */
    protected function getCallbackData()
    {
        if (isset($this->getData()['callback_query']['data'])) {
            return json_decode($this->getData()['callback_query']['data'], true);
        }
        return null;
    }

    /**
     *  Метод получает имя пользователя
     * @return mixed|null
     */
    protected function getFirstName()
    {
        return $this->data['message']['chat']['first_name'] ?? null;
    }

    /**
     * Метод получает фамилию пользователя
     * @return mixed|null
     */
    protected function getLastName()
    {
        return $this->data['message']['chat']['last_name'] ?? null;
    }

    /**
     * Метод получает никнейм пользователя
     * @return mixed|null
     */
    protected function getUsername()
    {
        return $this->data['message']['chat']['username'] ?? null;
    }
}