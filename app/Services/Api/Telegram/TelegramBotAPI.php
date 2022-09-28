<?php namespace App\Services\Api\Telegram;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class TelegramBotAPI {

    protected $token;
    protected $url;
    public \Psr\Log\LoggerInterface $log;

    public function __construct() {
       $this->setToken(config('app.telegram.token'));
       $this->setUrl(config('app.telegram.url'));
       $this->log = Log::channel('daily');
    }

    /**
     * @param mixed $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * Метод для отправки запроса
     * @param string $url
     * @param array $query
     * @param string $method
     * @param array $data
     * @param array $headers
     * @return false|mixed
     * @throws GuzzleException
     */
    protected function sendRequest(string $url, array $query = [], string $method = 'POST', array $data = [], array $headers = []) {

        $client = new Client();

        $options = [
            'headers' => array_merge([
                'Accept-Encoding' => 'gzip',
                'Connection' => 'keep-alive',
                'Accept-Language' => 'ru',
            ], $headers),
            'query' => array_merge([], $method === 'GET' ? $data : [], $query),
        ];

        $this->log->info('REQUEST: ' . $url);
        $this->log->info(json_encode($options));

        try {
            $response = $client->request($method, $this->getUrl() . 'bot' . $this->getToken() . '/' . $url, $options);
        } catch (RequestException $exception) {
            $this->log->info('ERROR');
            $this->log->error(Psr7\Message::toString($exception->getRequest()));
            $this->log->error(Psr7\Message::toString($exception->getResponse()));
            return false;
        }

        $this->log->info('RESPONSE');
        $this->log->info($response->getBody());

        return json_decode($response->getBody(), true);
    }
}