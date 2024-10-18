<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\CityDto;

final readonly class WeatherFetcher
{
    public function __construct(
        private WeatherServiceInterface $weatherService,
        private string $weatherApiUrl,
        private OutputServiceInterface $output,
    ) {
    }

    public function fetchWeatherForCity(CityDto $city): void
    {
        $weatherUrl = sprintf(
            $this->weatherApiUrl,
            urlencode((string) $city->latitude),
            urlencode((string) $city->longitude)
        );

        $weather = $this->weatherService->getWeather($weatherUrl);
        $this->output->write(
            'Processed city '.$city->name.' | '.$weather->currentWeather.' - '.$weather->tomorrowWeather
        );
    }
}
