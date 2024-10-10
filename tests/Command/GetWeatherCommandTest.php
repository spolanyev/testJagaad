<?php

namespace App\Tests\Command;

use App\Command\GetWeatherCommand;
use App\Service\CityProcessor;
use App\Service\CityServiceApiClient;
use App\Service\WeatherFetcher;
use App\Service\WeatherServiceApiClient;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class GetWeatherCommandTest extends KernelTestCase
{
    private function createCommand(callable $mockCallback): CommandTester
    {
        $httpClient = new MockHttpClient($mockCallback);
        $container = self::getContainer();
        $cityApiUrl = $container->getParameter('city_api_url');
        $weatherApiUrl = $container->getParameter('weather_api_url');
        $cityService = new CityServiceApiClient($httpClient);
        $weatherService = new WeatherServiceApiClient($httpClient);
        $logger = new Logger('test');
        $weatherFetcher = new WeatherFetcher($weatherService, $weatherApiUrl);
        $cityProcessor = new CityProcessor($cityService, $weatherFetcher, $cityApiUrl);
        $command = new GetWeatherCommand(
            $cityProcessor,
            $logger,
            'usleep'
        );

        return new CommandTester($command);
    }

    public function testCommandLine(): void
    {
        $cities = (string) file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'third-party-api-response'
            .DIRECTORY_SEPARATOR.'api.musement.com'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'v3'
            .DIRECTORY_SEPARATOR.'cities'.DIRECTORY_SEPARATOR.'get.json'
        );
        $weather =
            (string) file_get_contents(
                dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'third-party-api-response'
                .DIRECTORY_SEPARATOR.'api.weatherapi.com'.DIRECTORY_SEPARATOR.'v1'.DIRECTORY_SEPARATOR.'forecast'
                .DIRECTORY_SEPARATOR.'get-2-days.json'
            );
        $mockCallback = function () use ($cities, $weather) {
            static $isFirst = true;
            if ($isFirst) {
                $isFirst = false;

                return new MockResponse($cities);
            } else {
                return new MockResponse($weather);
            }
        };

        $commandTester = $this->createCommand($mockCallback);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();

        $this->assertSame(100, substr_count($output, 'Processed city '));
    }

    public function testNoCityApiReply(): void
    {
        $cities = '';
        $mockCallback = function () use ($cities) {
            return new MockResponse($cities);
        };

        $commandTester = $this->createCommand($mockCallback);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertSame(2, substr_count($output, '`Probably API not responding`, trying again'));
        $this->assertStringContainsString('`Probably API not responding`, stopping', $output);
    }

    public function testNoWeatherApiReply(): void
    {
        $cities = (string) file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'third-party-api-response'
            .DIRECTORY_SEPARATOR.'api.musement.com'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'v3'
            .DIRECTORY_SEPARATOR.'cities'.DIRECTORY_SEPARATOR.'get.json'
        );
        $weather = '';
        $mockCallback = function () use ($cities, $weather) {
            static $count = 0;
            if (0 === $count % 2) {
                $mock = new MockResponse($cities);
            } else {
                $mock = new MockResponse($weather);
            }
            ++$count;

            return $mock;
        };

        $commandTester = $this->createCommand($mockCallback);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertSame(2, substr_count($output, '`Probably API not responding`, trying again'));
        $this->assertStringContainsString('`Probably API not responding`, stopping', $output);
    }

    public function testStrangeCityApiReply(): void
    {
        $cities = '{"service":"ok"}';
        $mockCallback = function () use ($cities) {
            return new MockResponse($cities);
        };

        $commandTester = $this->createCommand($mockCallback);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertSame(2, substr_count($output, ', trying again'));
        $this->assertSame(1, substr_count($output, ', stopping'));
    }

    public function testStrangeWeatherApiReply(): void
    {
        $cities = (string) file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'third-party-api-response'
            .DIRECTORY_SEPARATOR.'api.musement.com'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'v3'
            .DIRECTORY_SEPARATOR.'cities'.DIRECTORY_SEPARATOR.'get.json'
        );
        $weather = '{"service":"ok"}';
        $mockCallback = function () use ($cities, $weather) {
            static $count = 0;
            if (0 === $count % 2) {
                $mock = new MockResponse($cities);
            } else {
                $mock = new MockResponse($weather);
            }
            ++$count;

            return $mock;
        };

        $commandTester = $this->createCommand($mockCallback);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertSame(2, substr_count($output, ', trying again'));
        $this->assertSame(1, substr_count($output, ', stopping'));
    }

    public function testInvalidValueCityApiReply(): void
    {
        $cities = (string) file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'third-party-api-response'
            .DIRECTORY_SEPARATOR.'api.musement.com'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'v3'
            .DIRECTORY_SEPARATOR.'cities'.DIRECTORY_SEPARATOR.'invalid-get.json'
        );
        $mockCallback = function () use ($cities) {
            return new MockResponse($cities);
        };

        $commandTester = $this->createCommand($mockCallback);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertSame('Probably API response changed, invalid data received', trim($output));
    }

    public function testInvalidValueWeatherApiReply(): void
    {
        $cities = (string) file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'third-party-api-response'
            .DIRECTORY_SEPARATOR.'api.musement.com'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'v3'
            .DIRECTORY_SEPARATOR.'cities'.DIRECTORY_SEPARATOR.'get.json'
        );
        $weather =
            (string) file_get_contents(
                dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'third-party-api-response'
                .DIRECTORY_SEPARATOR.'api.weatherapi.com'.DIRECTORY_SEPARATOR.'v1'.DIRECTORY_SEPARATOR.'forecast'
                .DIRECTORY_SEPARATOR.'invalid-get-2-days.json'
            );
        $mockCallback = function () use ($cities, $weather) {
            static $count = 0;
            if (0 === $count % 2) {
                $mock = new MockResponse($cities);
            } else {
                $mock = new MockResponse($weather);
            }
            ++$count;

            return $mock;
        };

        $commandTester = $this->createCommand($mockCallback);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertSame('Probably API response changed, invalid data received', trim($output));
    }

    public function testInvalidJsonWeatherApiReplay(): void
    {
        $cities = (string) file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'third-party-api-response'
            .DIRECTORY_SEPARATOR.'api.musement.com'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'v3'
            .DIRECTORY_SEPARATOR.'cities'.DIRECTORY_SEPARATOR.'get.json'
        );
        $weather = '{"name:"value"}';
        $mockCallback = function () use ($cities, $weather) {
            static $count = 0;
            if (0 === $count % 2) {
                $mock = new MockResponse($cities);
            } else {
                $mock = new MockResponse($weather);
            }
            ++$count;

            return $mock;
        };

        $commandTester = $this->createCommand($mockCallback);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertSame(2, substr_count($output, ', trying again'));
        $this->assertSame(1, substr_count($output, ', stopping'));
    }
}
