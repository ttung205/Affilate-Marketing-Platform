<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
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
        // Khởi động Chromedriver nội bộ lắng nghe cổng 9515 bên trong container
        static::startChromeDriver(['--port=9515']);
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
                        // Bổ sung các cờ bắt buộc cho môi trường Docker / DDEV Linux Container
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--ignore-certificate-errors',
            '--allow-insecure-localhost',
            '--allow-running-insecure-content',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new', // Bắt buộc chạy ẩn (headless) trong DDEV
            ]);
        })->all());

        // Đã loại bỏ đoạn setBinary của Edge chạy Windows vì Dusk đang thực thi trong Docker Linux

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://127.0.0.1:9515', // Đổi localhost thành 127.0.0.1
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}