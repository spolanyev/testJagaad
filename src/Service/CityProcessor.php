<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

final readonly class CityProcessor
{
    public function __construct(
        private CityServiceInterface $cityService,
        private WeatherFetcher $weatherFetcher,
        private string $cityApiUrl,
    ) {
    }

    public function processCities(): void
    {
        $processedCities = [];
        foreach ($this->cityService->getCities($this->cityApiUrl) as $city) {
            if (!in_array($city->name, $processedCities, true)) {
                $this->weatherFetcher->fetchWeatherForCity($city);
                $processedCities[] = $city->name;
            }
        }
    }
}
