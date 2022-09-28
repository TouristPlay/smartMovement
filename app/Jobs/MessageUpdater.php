<?php

namespace App\Jobs;

use App\Http\Controllers\Bot\Telegram\Keyboard\InlineKeyboard;
use App\Models\Message;
use App\Models\Stop;
use App\Models\User;
use App\Services\Api\Telegram\TelegramMethods;
use App\Services\Bot\Helper;
use App\Services\Bot\Transport\StopScheduleService;
use App\Services\Bot\Transport\StopService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MessageUpdater implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException
     */
    public function handle()
    {
        $this->messageUpdater();
    }

    /**
     * @throws GuzzleException
     */
    private function messageUpdater() {

        $messages = Message::all();
        $inlineKeyboard = new InlineKeyboard();
        $stopSchedule = new StopScheduleService();
        $telegram = new TelegramMethods();

        foreach ($messages as $message) {

            if ($message->stop_id == null) {
                continue;
            }

            if (!$this->checkTime($message)) {
                continue;
            }

            $user = User::whereId($message->user_id)->first();

            $stop = Stop::query()->whereId($message->stop_id )->first();
            $transport = $stopSchedule->getStopSchedule($stop);
            $transportKeyboard = $inlineKeyboard->getStopScheduleMenu($transport);

            $telegram->deleteMessage($user->chat_id, $message->message_id);

            $botMessage = $telegram->sendMessage($user->chat_id,"Маршруты остановки *" . Helper::escapingCharacter($stop->name) . "*\n",
                ['reply_markup' => $transportKeyboard],
                false
            );

            $this->updateMessage($botMessage['result']['message_id'], $message);
        }

    }

    /**
     * @return bool
     */
    private function checkTime($message) : bool {

        $currentTime = time();
        $messageCreateTime = strtotime($message->created_at);

        if ($currentTime - $messageCreateTime >= 1500) {
            return false;
        }

        return true;
    }

    /**
     * @param $messageId
     * @param $message
     * @return void
     */
    private function updateMessage($messageId, $message) {
        $message->update([
            'message_id' => $messageId,
        ]);
    }
}
