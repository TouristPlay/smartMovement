<?php

namespace App\Jobs;

use App\Models\City;
use App\Models\Stop;
use App\Models\Transport;
use App\Services\Bot\Helper;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTransport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var
     */
    private $city;


    private $driver;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->driver = RemoteWebDriver::create(config('app.selenium.url'), DesiredCapabilities::chrome());

        $cities = City::all();

        foreach ($cities as $city) {

            $this->city = $city;

            $this->transportHandler();
        }


        $this->driver->close();
    }


    /**Метод для обработки остановок
     * @return void
     */
    private function transportHandler()
    {

        $stops = Stop::query()->whereCityId($this->city->id)->get();

        foreach ($stops as $stop) {

            $transports = $this->getTransports($stop);

            foreach ($transports as $transport) {
                $classList = $transport->getAttribute('class');
                $transportInfo = $this->getTransportInfo($transport);
                $this->storeTransport($transportInfo);
            }

        }

    }


    /**
     * Метод возвращает имена маршрутов
     * @return RemoteWebElement[]
     */
    private function getTransports($stop): array
    {
        $this->driver->get($stop->url);
        sleep(1);

        return $this->driver->findElements(WebDriverBy::className('masstransit-vehicle-snippet-view'));
    }


    /**Получаем информацию траспорта
     * @param $transport
     * @return array
     */
    private function getTransportInfo($transport): array
    {

        $types = [
            'bus' => '_type_bus',
            'minibus' => '_type_minibus',
            'trolleybus' => '_type_trolleybus',
        ];

        $info = $transport->findElement(WebDriverBy::className('masstransit-vehicle-snippet-view__name'));

        foreach ($types as $key => $type) {
            if (strripos($transport->getAttribute('class'), $type)) {
                return [
                    'name' => $info->getText(),
                    'type' => $key,
                    'url' => $info->getAttribute('href')
                ];
            }
        }

        return [];
    }

    /**Метод заменяет русские буквы в названии маршрута на английские
     * @return string
     */
    private function replaceTransportNameCharacter($transportName) : string {

        $ruCharacter = ['к', 'д', 'э', 'а'];
        $enCharacter = ['k', 'd', 'e', 'a'];

        return str_replace($ruCharacter, $enCharacter, $transportName);
    }

    /**Метод для сохранения траспорта
     * @return void
     */
    private function storeTransport($transport)
    {
        $systemTransport = Transport::query()->firstOrNew([
            'name' => $transport['name'],
        ]);
        $systemTransport->type = $transport['type'];
        $systemTransport->url = 'https://yandex.ru' . $transport['url'];
        $systemTransport->city_id = $this->city->id;
        $systemTransport->save();
    }
}
