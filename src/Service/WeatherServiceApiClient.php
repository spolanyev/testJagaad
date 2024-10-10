<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\WeatherDto;

final class WeatherServiceApiClient extends AbstractJsonApiClient implements WeatherServiceInterface
{
    public function getWeather(string $weatherUri): WeatherDto
    {
        return $this->fetchData($weatherUri);
    }

    protected function getDtoClass(): string
    {
        return WeatherDto::class;
    }

    protected function validate(object|array $dto): void
    {
        $this->validateDto($dto);
    }
}
