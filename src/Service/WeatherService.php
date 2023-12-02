<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\WeatherDto;

final class WeatherService extends ApiService
{
    public function getWeather(string $weatherUri): WeatherDto
    {
        $json = $this->makeGetRequest($weatherUri);
        $weather = $this->deserializeJson($json, WeatherDto::class);
        $this->validateDto($weather);

        return $weather;
    }
}
