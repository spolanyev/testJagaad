<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Tests\Service;

use App\Service\WeatherServiceApiClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class WeatherServiceApiClientTest extends KernelTestCase
{
    public function testGetWeather(): void
    {
        $file =
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'third-party-api-response'
            .DIRECTORY_SEPARATOR.'api.weatherapi.com'.DIRECTORY_SEPARATOR.'v1'.DIRECTORY_SEPARATOR.'forecast'
            .DIRECTORY_SEPARATOR.'get-2-days.json';

        $mockResponse = new MockResponse((string) file_get_contents($file));
        $httpClient = new MockHttpClient($mockResponse);

        $serializer = self::getContainer()->get('Symfony\Component\Serializer\SerializerInterface');
        $validator = self::getContainer()->get('Symfony\Component\Validator\Validator\ValidatorInterface');
        $service = new WeatherServiceApiClient($httpClient, $serializer, $validator);
        $actual = $service->getWeather(
            'https://api.weatherapi.com/v1/forecast.json?key='.$_ENV['API_KEY'].'&q='
            .urlencode((string) 41.16).','.urlencode((string) -8.62).'&days=2'
        );

        $this->assertSame('Light rain', $actual->currentWeather);
        $this->assertSame('Light rain shower', $actual->tomorrowWeather);
    }
}
