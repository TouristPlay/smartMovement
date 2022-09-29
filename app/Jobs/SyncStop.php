<?php

namespace App\Jobs;

use App\Models\City;
use App\Models\Stop;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncStop implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * @var int
     */
    private int $city_id;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $cities = City::all();

        foreach ($cities as $city) {

            $this->city_id = $city->id;

            $coordinate = [
                'latitude' => $city->latitude,
                'longitude' => $city->longitude
            ];

            $this->getStops($coordinate);
        }
    }


    /**
     * Метод получает остановки
     */
    public function getStops($coordinate)
    {
        $desiredCapabilities = DesiredCapabilities::chrome();

        // Disable accepting SSL certificates
        $desiredCapabilities->setCapability('acceptSslCerts', false);

        // Add arguments via FirefoxOptions to start headless firefox
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(['---no-sandbox']);
        $chromeOptions->addArguments(['-headless']);
        $chromeOptions->addArguments(['--disable-dev-shm-usage']);
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $driver = RemoteWebDriver::create(config('app.selenium.url'), $desiredCapabilities);

        $driver->get('https://yandex.ru/maps/?ll=' . $coordinate['longitude'] . '%2C' . $coordinate['latitude'] . '&mode=search&text=Остановки%20общественного%20транспорта&z=12');
        sleep(1);

        $this->scrollStopView($driver);

        $stops = $driver->findElements(WebDriverBy::className('search-snippet-view__body'));

        $this->stopsHandler($stops);

        $driver->close();
    }


    /** Метод для прокрутки списка остановок
     * @param $driver
     * @return void
     */
    private function scrollStopView($driver)
    {
        $driver->findElement(WebDriverBy::className('search-filters-view'))->click();

        // TODO переделать с проверкой до полной прогрузки страницы
        for ($i = 0; $i <= 1000; ++$i) {
            $driver->getKeyboard()->sendKeys(array(WebDriverKeys::PAGE_DOWN));
        }
    }


    /**
     * Метод обрабатывает все остсновки
     * @param $stops
     */
    private function stopsHandler($stops)
    {
        foreach ($stops as $stop) {
            $stopInfo = $this->getStopInfo($stop);
            $this->storeStop($stopInfo);
        }
    }


    /**
     * Метод получает данные из отсановки
     * @param $stop
     * @return array
     */
    private function getStopInfo($stop): array
    {
        $coordinate = $stop->getAttribute('data-coordinates');
        $stopLink = $stop->findElement(WebDriverBy::className('search-snippet-view__link-overlay'));
        $address = $stop->findElement(WebDriverBy::className('search-business-snippet-view__address'))->getText();
        $name = $stopLink->getText();


        return [
            'name' => $name,
            'id' => strstr(explode("=", $stopLink->getAttribute('href'))[1], '&', 1),
            'url' => 'https://yandex.ru' . $stopLink->getAttribute('href'),
            'coordinate' => [
                'longitude' => strstr($coordinate, ',', 1),
                'latitude' => substr($coordinate, strpos($coordinate, ',') + 1),
            ],
            'address' => $address
        ];
    }


    /**Метод сохраняет остановку в нашу систему
     * @param $stop
     * @return void
     */
    private function storeStop($stop)
    {

        $systemStop = Stop::query()->firstOrNew([
            'stop_id' => $stop['id']
        ]);
        $systemStop->name = $stop['name'];
        $systemStop->address = $stop['address'];
        $systemStop->url = $stop['url'];
        $systemStop->city_id = $this->city_id;
        $systemStop->longitude = $stop['coordinate']['longitude'];
        $systemStop->latitude = $stop['coordinate']['latitude'];
        $systemStop->save();
    }

}
