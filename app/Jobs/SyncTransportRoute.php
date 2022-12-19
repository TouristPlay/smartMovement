<?php

namespace App\Jobs;

use App\Models\Route;
use App\Models\Stop;
use App\Models\Transport;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTransportRoute implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $driver;

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
     */
    public function handle()
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

        $this->driver = RemoteWebDriver::create(config('app.selenium.url'), $desiredCapabilities);
        sleep(1);


        $transports = Transport::all();

        foreach ($transports as $transport) {
            $this->routeHandler($transport);
        }

    }


    /**
     * Обработчик маршрута
     * @param $transport
     * @return void
     */
    private function routeHandler($transport) {

        $routes = $this->getTransportRoute($transport);

        if ($routes) {
            foreach ($routes as $route) {
                $routeLink = $route->getAttribute('href');

                $linkArray = explode('/', $routeLink);

                $stopID = Stop::query()->where('stop_id', $linkArray[count($linkArray) - 2])->value('id');

                if ($stopID == null) {
                    continue;
                }

                $this->saveRoute($transport->id, $stopID);
            }
        }
    }


    /**
     * Метод сохраненения пути
     * @param $transportID
     * @param $stopID
     * @return void
     */
    private function saveRoute($transportID, $stopID) {
        $route = Route::query()->firstOrNew([
            'transport_id' => $transportID,
            'stop_id' => $stopID
        ]);
        $route->save();
    }


    /**
     * Метод получает маршрут транспорта
     * @param $transport
     * @return false
     */
    private function getTransportRoute($transport)
    {

        try {
            $this->driver->get($transport->url);
            sleep(5);
            $this->driver->findElement(WebDriverBy::className('masstransit-legend-group-view__open-button'))->click();
        } catch (\Exception $e) {
            return false;
        }

        return $this->driver->findElements(WebDriverBy::className('masstransit-legend-group-view__item-link'));
    }
}
