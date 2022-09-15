<?php

namespace App\Http\Controllers\Bot\Telegram;


use App\Models\User;
use App\Services\Api\Telegram\TelegramMethods;

class TelegramOptions
{

    /**
     * Id чата пользователя
     * @var mixed
     */
    protected $chatId;
    /**
     * JSON события в чате
     * @var mixed
     */
    protected $data;
    /**
     * Сообщение пользователя
     * @var mixed
     */
    protected $message;
    /**
     * Данные обратного вызова
     * @var array|mixed
     */
    protected array $callbackData;

    /**
     * Ответ сервера при отправки сообщения ботом
     * @var array
     */
    protected array $messageRequest;

    /**
     * Внутренний пользователь бота
     * @var
     */
    protected $user;
    /**
     * Методы для работы с телеграм API
     * @var TelegramMethods
     */
    protected TelegramMethods $telegram;

    /**Массив всех данных
     * @var array
     */
    protected array $options;

    /**
     * Метод для получения ID чата с пользователем
     * @return int
     */
    public function getChatId() : int
    {
        return $this->chatId;
    }

    /**
     * Метод для получения json данных
     *
     * @return array
     */
    public function getData() : array {
        return $this->data;
    }

    /**
     * Метод для получения данных обратного вызова
     * @return array
     */
    public function getCallbackData() : array {
        return $this->callbackData;
    }

    /**
     * Устанавливаем начальные значения
     * @param $options
     */
    public function __construct($options) {
        $this->chatId = $options['chat_id'];
        $this->data = $options['data'];
        $this->message = $options['message']['text'];
        $this->callbackData = $options['message']['callbackData'] ?? [];

        $this->telegram = new TelegramMethods();
        $this->user = User::query()->whereChatId($this->getChatId())->first() ?? null;
        $this->options = $options;
    }

}