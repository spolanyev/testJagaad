<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Command;

use App\Exception\ApiNotAvailableException;
use App\Exception\InvalidApiResponseException;
use App\Service\CityService;
use App\Service\WeatherService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:get-weather',
    description: 'Get weather for cities',
)]
class GetWeatherCommand extends Command
{
    public function __construct(
        private readonly CityService $cityService,
        private readonly WeatherService $weatherService,
        private readonly LoggerInterface $logger,
        private HttpClientInterface $httpClient,
        private readonly string $cityApiUrl,
        private readonly string $weatherApiUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $attemptQuantity = 0;
        $maxAttemptQuantity = 3;

        while ($attemptQuantity < $maxAttemptQuantity) {
            try {
                $this->logger->info('app:get-weather is called');
                foreach (
                    $this->cityService->getCities(
                        $this->cityApiUrl,
                        $this->httpClient
                    ) as $city
                ) {
                    $weatherUrl = sprintf(
                        $this->weatherApiUrl,
                        urlencode((string) $city->latitude),
                        urlencode((string) $city->longitude)
                    );
                    $weather = $this->weatherService->getWeather($weatherUrl, $this->httpClient);
                    $output->writeln(
                        'Processed city '.$city->name.' | '.$weather->currentWeather.' - '.$weather->tomorrowWeather
                    );
                }
                break;
            } catch (ApiNotAvailableException $exception) {
                ++$attemptQuantity;
                if ($attemptQuantity !== $maxAttemptQuantity) {
                    $this->logger->warning('app:get-weather no API response, retrying');
                    $output->writeln('`'.$exception->getMessage().'`, trying again');
                } else {
                    $this->logger->critical('app:get-weather no API response, stopping');
                    $output->writeln('`'.$exception->getMessage().'`, stopping');
                }
            } catch (InvalidApiResponseException $exception) {
                $this->logger->critical('app:get-weather invalid data received, stopping');
                $output->writeln($exception->getMessage().', invalid data received');
                break;
            } catch (\Throwable $error) {
                ++$attemptQuantity;
                if ($attemptQuantity !== $maxAttemptQuantity) {
                    $this->logger->error(
                        'app:get-weather got error `{error}`, retrying',
                        ['error' => $error->getMessage()]
                    );
                    $output->writeln('`'.$error->getMessage().'`, trying again');
                } else {
                    $this->logger->critical(
                        'app:get-weather got error `{error}`, stopping',
                        ['error' => $error->getMessage()]
                    );
                    $output->writeln('`'.$error->getMessage().'`, stopping');
                }
            }
        }

        return Command::SUCCESS;
    }
}
