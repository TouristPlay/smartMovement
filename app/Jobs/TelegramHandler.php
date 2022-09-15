<?php

namespace App\Jobs;

use App\Http\Controllers\Bot\Telegram\Telegram;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TelegramHandler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    private array $options;


    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException
     */
    public function handle()
    {
        $telegram = new Telegram($this->options);
        $telegram->messageHandler();
    }
}
