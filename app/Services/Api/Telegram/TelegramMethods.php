<?php namespace App\Services\Api\Telegram;

use App\Services\Api\Telegram\TelegramBotAPI;
use App\Services\Bot\Helper;
use GuzzleHttp\Exception\GuzzleException;

// TODO может сделать методы для сборки запроса, ибо везде одинаковые параметры
// TODO или сделать классы для каждого метода с надстройкой и соответсвующими методами
class TelegramMethods extends TelegramBotAPI {

    /**
     *  Метод отправляет сообщение пользователю
     *
     *   https://core.telegram.org/bots/api#sendmessage
     *
     * В options указывается следующее по желанию
     *  $options = [
     *      'disable_web_page_preview' => false/true // Отключает превью ссылок
     *      'disable_notification' => false/true // Отключает звук уведоиления
     *      'protect_content' => false/true // Отключает пересылку сообщения
     *      'reply_to_message_id' => integer // ID ответного сообщения
     *      'allow_sending_without_reply' => true/false // Отпарвка сообщения, если ответного сообщения не найдно
     *      'reply_markup' => [Объект клавиатуры] // см в документации апи телеграма
     * ]
     *
     * @param $chat_id
     * @param $text
     * @param array $options
     * @param bool $escaping
     * @param string $parse_mode
     * @return false|mixed
     * @throws GuzzleException
     */
    public function sendMessage($chat_id, $text, array $options = [], bool $escaping = true, string $parse_mode = 'MarkdownV2') {

        $query = [
            'chat_id' => $chat_id,
            'text' => $escaping ? Helper::escapingCharacter($text) : $text,
            'parse_mode' => $parse_mode,
        ];


        // Если есть клавиатура
        if (isset($options['reply_markup'])) {
            $options['reply_markup'] = json_encode($options['reply_markup'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        }


        return $this->sendRequest('sendMessage', array_merge([], $query, $options));
    }

    /**
     *  Метод удаляет сообщение в чате по его ID
     * https://core.telegram.org/bots/api#deletemessage
     *
     * @param $chat_id
     * @param $message_id
     * @return void
     * @throws GuzzleException
     */
    public function deleteMessage($chat_id, $message_id) {
        $query = [
            'chat_id' => $chat_id,
            'message_id' => $message_id
        ];

        return $this->sendRequest('deleteMessage', $query);
    }

    /**
     * Метод изменяет сообщение на новое по его ID
     * https://core.telegram.org/bots/api#deletemessage
     *
     * Доп настроки указываются по желанию
     * $options = [
     *      'disable_web_page_preview' => false/true // Отключает превью ссылок
     *      'reply_markup' => [Объект клавиатуры] // см в документации апи телеграма
     * ];
     *
     * @param $chat_id
     * @param $message_id
     * @param $text
     * @param array $options
     * @param string $parse_mode
     * @return void
     * @throws GuzzleException
     */
    public function editMessageText($chat_id, $message_id, $text, array $options = [], string $parse_mode = 'MarkdownV2') {

        $query = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => $parse_mode
        ];

        return $this->sendRequest('editMessageText', array_merge([], $query, $options));
    }

    /**
     * Метод редактирует сообщение с кнопками
     * https://core.telegram.org/bots/api#editmessagereplymarkup
     *
     * @param $chat_id
     * @param $message_id
     * @param array $reply_markup
     * @return void
     * @throws GuzzleException
     */
    public function editMessageReplyMarkup($chat_id, $message_id, array $reply_markup = []) {

        $query = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode($reply_markup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)
        ];

        return $this->sendRequest('editMessageReplyMarkup', $query);
    }

    /**
     *  Метод отправляет сообщение пользователю
     *
     *   https://core.telegram.org/bots/api#sendmessage
     *
     * В options указывается следующее по желанию
     *  $options = [
     *      'disable_web_page_preview' => false/true // Отключает превью ссылок
     *      'disable_notification' => false/true // Отключает звук уведоиления
     *      'protect_content' => false/true // Отключает пересылку сообщения
     *      'reply_to_message_id' => integer // ID ответного сообщения
     *      'allow_sending_without_reply' => true/false // Отпарвка сообщения, если ответного сообщения не найдно
     *      'reply_markup' => [Объект клавиатуры] // см в документации апи телеграма
     * ]
     *
     * @param $chat_id
     * @param $photo
     * @param array $options
     * @param bool $escaping
     * @param string $parse_mode
     * @return false|mixed
     * @throws GuzzleException
     */
    public function sendPhoto($chat_id, $photo, array $options = [], bool $escaping = true, string $parse_mode = 'MarkdownV2') {

        $query = [
            'chat_id' => $chat_id,
            'photo' => $photo,
            'parse_mode' => $parse_mode,
        ];


        // Если есть клавиатура
        if (isset($options['reply_markup'])) {
            $options['reply_markup'] = json_encode($options['reply_markup'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        }


        return $this->sendRequest('sendPhoto', array_merge([], $query, $options));
    }
}
