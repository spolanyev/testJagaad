<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\CityDto;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class WeatherFetcher
{
    public function __construct(
        private WeatherServiceApiClient $weatherService,
        private string $weatherApiUrl,
    ) {
    }

    public function fetchWeatherForCity(OutputInterface $output, CityDto $city): void
    {
        $weatherUrl = sprintf(
            $this->weatherApiUrl,
            urlencode((string) $city->latitude),
            urlencode((string) $city->longitude)
        );

        $weather = $this->weatherService->getWeather($weatherUrl);
        $output->writeln(
            'Processed city '.$city->name.' | '.$weather->currentWeather.' - '.$weather->tomorrowWeather
        );
    }
}
