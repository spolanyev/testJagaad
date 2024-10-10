<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\WeatherDto;

interface WeatherServiceInterface
{
    public function getWeather(string $weatherUri): WeatherDto;
}
