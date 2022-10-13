<?php

namespace App\Http\Controllers\Bot\Telegram\Menu;

use App\Http\Controllers\Bot\Telegram\Keyboard\InlineKeyboard;
use App\Http\Controllers\Bot\Telegram\TelegramOptions;
use App\Models\FavoriteStop;
use App\Models\Message;
use App\Models\Stop;
use App\Services\Api\Telegram\TelegramMethods;
use App\Services\Bot\Helper;
use App\Services\Bot\Transport\StopScheduleService;
use App\Services\Bot\Transport\StopService;
use GuzzleHttp\Exception\GuzzleException;

class TelegramMenu extends TelegramOptions
{

    /**
     * @var
     */
    private $systemMessage;

    /**
     * @var mixed|null
     */
    private $callbackKey;

    /**
     * @var InlineKeyboard
     */
    private InlineKeyboard $inlineKeyboard;

    /**
     * @var StopService
     */
    private StopService $stopService;

    /**
     * @var StopScheduleService
     */
    private StopScheduleService $stopSchedule;




    public function __construct($options)
    {
        parent::__construct($options);
        $this->callbackKey = $this->callbackData['callbackKey'] ?? null;
        $this->inlineKeyboard = new InlineKeyboard($this->user);
        $this->stopService = new StopService();
        $this->stopSchedule = new StopScheduleService();
    }

    /**
     * @throws GuzzleException
     */
    public function handler($coords) {

        $this->systemMessage = Message::query()->whereUserId($this->user->id)->first() ?? null;

        if ($this->callbackKey == 'stops') {
            $this->stopsHandler($this->callbackData['data']);
            return;
        }

        if ($this->message == '/favorite') {

            $this->deleteMessage();
            $stops = Stop::query()->whereIn('id', $this->user->favorite->pluck('stop_id'))->get();
            $stopKeyboard = $this->inlineKeyboard->getFavoriteStopMenu($stops);
            $botMessage = $this->telegram->sendMessage($this->user->chat_id, "Выберите остановку", ['reply_markup' => $stopKeyboard]);

            $this->saveMessage($botMessage['result']['message_id']);
            return;
        }

        if ($this->callbackKey == 'createFavorite') {
            $this->saveFavorite($this->callbackData['data']);
            $this->stopsHandler($this->callbackData['data']);
            return;
        }

        if ($this->callbackKey == 'deleteFavorite') {
            $this->deleteFavorite($this->callbackData['data']);
            $this->stopsHandler($this->callbackData['data']);
            return;
        }


        $stops = $this->stopService->getStopsAroundUser($coords);
        $stopKeyboard = $this->inlineKeyboard->getAroundStopsMenu($stops);
        $botMessage = $this->telegram->sendMessage($this->user->chat_id, "Выберите остановку", ['reply_markup' => $stopKeyboard]);


        $this->deleteMessage();
        $this->saveMessage($botMessage['result']['message_id']);
    }

    /**
     * @throws GuzzleException
     */
    private function stopsHandler($stopID) {

        $stop = Stop::query()->whereId($stopID)->first();

        $transport = $this->stopSchedule->getStopSchedule($stop);
        $transportKeyboard = $this->inlineKeyboard->getStopScheduleMenu($transport);

        $this->telegram->deleteMessage($this->user->chat_id, $this->systemMessage->message_id);

        $botMessage = $this->telegram->sendMessage($this->user->chat_id, "Маршруты остановки *" . Helper::escapingCharacter($stop->name) . "*\n",
            ['reply_markup' => $transportKeyboard],
            false
        );

        $this->updateMessage($botMessage['result']['message_id'], $this->systemMessage, $stop);

        //$this->telegram->editMessageText($this->user->chat_id, $message->message_id, "Маршруты остановки *" . $stop->name . "*\n");
        //$this->telegram->editMessageReplyMarkup($this->user->chat_id, $message->message_id, ['reply_markup' => $transportKeyboard]);

    }

    /**
     * @param $messageId
     * @return void
     */
    private function saveMessage($messageId) {
        $message = Message::query()->firstOrNew([
            'message_id' => $messageId,
            'user_id' => $this->user->id,
        ]);
        $message->save();
    }

    /**
     * @param $messageId
     * @param $message
     * @param $stop
     * @return void
     */
    private function updateMessage($messageId, $message, $stop) {
        $message->update([
            'message_id' => $messageId,
            'stop_id' => $stop->id
        ]);
    }

    /**
     * @throws GuzzleException
     */
    private function deleteMessage() {
        if (isset($this->systemMessage)) {
            $this->telegram->deleteMessage($this->user->chat_id, $this->systemMessage->message_id);
            $this->systemMessage->delete();
        }
    }

    /**
     * @param $stop
     * @return void
     */
    private function saveFavorite($stop) {
        $favorite = FavoriteStop::firstOrNew([
            'user_id' => $this->user->id,
            'stop_id' => $stop
        ]);
        $favorite->save();
    }

    /**
     * @param $stop
     * @return void
     */
    private function deleteFavorite($stop) {
        FavoriteStop::query()
            ->whereUserId($this->user->id)
            ->whereStopId($stop)
            ->delete();
    }
}