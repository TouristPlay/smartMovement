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
use Illuminate\Support\Facades\Log;

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

        if ($this->callbackKey == 'stops' && $this->systemMessage->action == null) {

            $this->deleteMessage();

            $botMessage = $this->telegram->sendMessage($this->user->chat_id, "Выберите пункт меню",
                ['reply_markup' => $this->inlineKeyboard->getMainMenu()],
                false
            );

            $stop = Stop::query()->whereId($this->callbackData['data'])->first();

            $this->systemMessage->update([
                'message_id' => $botMessage['result']['message_id'],
                'from_stop_id' => $stop->id
            ]);

            return;
        }

        if ($this->callbackKey == 'schedule') {
            $this->stopsHandler($this->systemMessage->from_stop_id);
            return;
        }

        if ($this->callbackKey == 'buildRoute' && $this->callbackKey != 'stops') {

            $this->deleteMessage();

            $botMessage = $this->telegram->sendMessage($this->user->chat_id, "Отправьте координаты конечной остановки",
                [],//['reply_markup' => $this->inlineKeyboard->getMainMenu()],
                false
            );

            $this->systemMessage->update([
                'message_id' => $botMessage['result']['message_id'],
                'action' => $this->callbackKey
            ]);
            return;
        }

        // Вывод избранных остановок
        if ($this->message == '/favorite') {

            $this->deleteMessage();
            $stops = Stop::query()->whereIn('id', $this->user->favorite->pluck('stop_id'))->get();
            $stopKeyboard = $this->inlineKeyboard->getFavoriteStopMenu($stops);
            $botMessage = $this->telegram->sendMessage($this->user->chat_id, "Выберите остановку", ['reply_markup' => $stopKeyboard]);

            $this->saveMessage($botMessage['result']['message_id']);
            return;
        }

        // Добавление в избранные
        if ($this->callbackKey == 'createFavorite') {
            $this->saveFavorite($this->callbackData['data']);
            $this->stopsHandler($this->callbackData['data']);
            return;
        }

        // Удаление из избранных
        if ($this->callbackKey == 'deleteFavorite') {
            $this->deleteFavorite($this->callbackData['data']);
            $this->stopsHandler($this->callbackData['data']);
            return;
        }


        if ($this->callbackKey == 'stops') {
            $this->systemMessage->update([
                'to_stop_id' => $this->callbackData['data']
            ]);


            $this->buildRouteHandler();
            return;
        }


        if (isset($this->systemMessage) && $this->systemMessage->action == 'buildRoute') {
            $stops = $this->stopService->getStopsAroundUser($coords);
            $stopKeyboard = $this->inlineKeyboard->getAroundStopsMenu($stops);

            $botMessage = $this->telegram->sendMessage($this->user->chat_id, "Выберите конечную остановку", ['reply_markup' => $stopKeyboard]);

            $this->systemMessage->update([
                'message_id' => $botMessage['result']['message_id'],
            ]);

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
    private function buildRouteHandler() {

        $fromStop = Stop::query()->whereId($this->systemMessage->from_stop_id)->first();
        $toStop = Stop::query()->whereId($this->systemMessage->to_stop_id)->first();

        $fromTransport = $this->stopSchedule->getStopSchedule($fromStop);
        $toTransport = $this->stopSchedule->getStopSchedule($toStop);

        $bus = collect(array_merge($fromTransport['bus'], $toTransport['bus']));
        $minibus = collect(array_merge($fromTransport['minibus'], $toTransport['minibus']));
        $trolleybus = collect(array_merge($fromTransport['trolleybus'], $toTransport['trolleybus']));

        $uniqueTransport = [
            'bus' => $bus->whereIn('name', $bus->duplicates('name'))->toArray(),
            'minibus' => $minibus->whereIn('name', $minibus->duplicates('name'))->unique('name')->toArray(),
            'trolleybus' => $trolleybus->whereIn('name', $trolleybus->duplicates('name'))->toArray(),
        ];


        $transportKeyboard = $this->inlineKeyboard->getStopScheduleMenu($uniqueTransport);

        $this->telegram->deleteMessage($this->user->chat_id, $this->systemMessage->message_id);

        $botMessage = $this->telegram->sendMessage($this->user->chat_id,
            "Маршруты от остановки *" . Helper::escapingCharacter($fromStop->name) . "* до *" . $toStop->name . "*\n",
            ['reply_markup' => $transportKeyboard],
            false
        );

        $this->systemMessage->delete();
//        $this->systemMessage->update([
//            'message_id' => $botMessage['result']['message_id'],
//        ]);
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


        $this->systemMessage->update([
            'message_id' => $botMessage['result']['message_id'],
            'from_stop_id' => $stop->id,
            'action' => $this->callbackKey
        ]);

        //$this->telegram->editMessageText($this->user->chat_id, $message->message_id, "Маршруты остановки *" . $stop->name . "*\n");
        //$this->telegram->editMessageReplyMarkup($this->user->chat_id, $message->message_id, ['reply_markup' => $transportKeyboard]);

    }

    /**
     * @param $messageId
     * @return void
     */
    private function saveMessage($messageId) {
        $message = Message::query()->firstOrNew([
            'user_id' => $this->user->id,
        ]);
        $message->message_id = $messageId;
        $message->save();
    }

    /**
     * @throws GuzzleException
     */
    private function deleteMessage() {
        if (isset($this->systemMessage)) {
            $this->telegram->deleteMessage($this->user->chat_id, $this->systemMessage->message_id);
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
        $favorite->save(); // TODO при посторном выборе остановки, он выводит маршурты между двух остановокв
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