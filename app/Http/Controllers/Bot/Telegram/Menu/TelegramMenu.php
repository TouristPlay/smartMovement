<?php

namespace App\Http\Controllers\Bot\Telegram\Menu;

use App\Http\Controllers\Bot\Telegram\Keyboard\InlineKeyboard;
use App\Http\Controllers\Bot\Telegram\TelegramOptions;
use App\Models\Message;
use App\Models\Stop;
use App\Services\Api\Telegram\TelegramMethods;
use App\Services\Bot\Helper;
use App\Services\Bot\Transport\StopScheduleService;
use App\Services\Bot\Transport\StopService;
use GuzzleHttp\Exception\GuzzleException;

class TelegramMenu extends TelegramOptions
{

    private $callbackKey;

    public function __construct($options)
    {
        parent::__construct($options);
        $this->callbackKey = $this->callbackData['callbackKey'] ?? null;
    }

    /**
     * @throws GuzzleException
     */
    public function handler($coords) {

        $inlineKeyboard = new InlineKeyboard();
        $stopService = new StopService();
        $stopSchedule = new StopScheduleService();
        $telegram = new TelegramMethods();

        $message = Message::query()->whereUserId($this->user->id)->first();

        if ($this->callbackKey == 'stops') {
            $stop = Stop::query()->whereId($this->callbackData['data'])->first();
            $transport = $stopSchedule->getStopSchedule($stop);
            $transportKeyboard = $inlineKeyboard->getStopScheduleMenu($transport);

            $telegram->deleteMessage($this->user->chat_id, $message->message_id);

            $botMessage = $telegram->sendMessage($this->user->chat_id, "Маршруты остановки *" . Helper::escapingCharacter($stop->name) . "*\n",
                ['reply_markup' => $transportKeyboard],
                false
            );

            $this->updateMessage($botMessage['result']['message_id'], $message, $stop);

            //$telegram->editMessageText($this->user->chat_id, $message->message_id, "Маршруты остановки *" . $stop->name . "*\n");
            //$telegram->editMessageReplyMarkup($this->user->chat_id, $message->message_id, ['reply_markup' => $transportKeyboard]);
            return;
        }

        $stops = $stopService->getStopsAroundUser($coords);
        $stopKeyboard = $inlineKeyboard->getAroundStopsMenu($stops);
        $botMessage = $telegram->sendMessage($this->user->chat_id, "Выберите остановку", ['reply_markup' => $stopKeyboard]);

        if (isset($message)) {
            $telegram->deleteMessage($this->user->chat_id, $message->message_id);
            $message->delete();
        }
        $this->saveMessage($botMessage['result']['message_id']);
    }

    private function saveMessage($messageId) {
        $message = Message::query()->firstOrNew([
            'message_id' => $messageId,
            'user_id' => $this->user->id,
        ]);
        $message->save();
    }

    private function updateMessage($messageId, $message, $stop) {
        $message->update([
            'message_id' => $messageId,
            'stop_id' => $stop->id
        ]);
    }

}