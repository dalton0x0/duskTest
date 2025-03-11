<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $userAgent = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36";
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--user-data-dir=' . storage_path('app/chrome_profile'),
            '--user-agent=' . $userAgent,
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
    public function saveCookies(Browser $browser, string $filename): void
    {
        $cookies = $browser->driver->manage()->getCookies();
        $cookiesArray = [];
        foreach ($cookies as $cookie) {
            $cookiesArray[] = [
                'name' => $cookie->getName(),
                'value' => $cookie->getValue(),
                'path' => $cookie->getPath(),
                'domain' => $cookie->getDomain(),
                'expiry' => $cookie->getExpiry(),
                'secure' => $cookie->isSecure(),
                'httpOnly' => $cookie->isHttpOnly(),
                'sameSite' => $cookie->getSameSite(),
            ];
        }
        file_put_contents(storage_path("app/{$filename}"), json_encode($cookiesArray, JSON_PRETTY_PRINT));
    }

    public function loadCookies(Browser $browser, string $filename): void
    {
        if (file_exists(storage_path("app/{$filename}"))) {
            $cookiesArray = json_decode(file_get_contents(storage_path("app/{$filename}")), true);

            foreach ($cookiesArray as $cookieData) {
                $browser->driver->manage()->addCookie(
                    new Cookie(
                        $cookieData['name'],
                        $cookieData['value'],
                        $cookieData['domain'],
                        $cookieData['path'],
                        $cookieData['expiry'],
                        $cookieData['secure'],
                        $cookieData['httpOnly'],
                        $cookieData['sameSite']
                    )
                );
            }
            $browser->refresh();
        }
    }
}
