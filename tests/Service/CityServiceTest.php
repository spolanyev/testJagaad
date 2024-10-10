<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Tests\Service;

use App\Service\CityServiceApiClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CityServiceTest extends TestCase
{
    public function testGetCities(): void
    {
        $file = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'third-party-api-response'
            .DIRECTORY_SEPARATOR.'api.musement.com'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'v3'
            .DIRECTORY_SEPARATOR.'cities'.DIRECTORY_SEPARATOR.'get.json';

        $mockResponse = new MockResponse((string) file_get_contents($file));
        $httpClient = new MockHttpClient($mockResponse);

        $service = new CityServiceApiClient($httpClient);
        $actual = $service->getCities('https://api.musement.com/api/v3/cities');

        $this->assertSame(100, count($actual));
        $this->assertSame('Porto', $actual[0]->name);
        $this->assertSame(41.162, $actual[0]->latitude);
        $this->assertSame(-8.623, $actual[0]->longitude);
    }
}
