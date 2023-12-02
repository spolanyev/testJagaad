<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Command;

use App\Dto\CityDto;
use App\Exception\ApiNotAvailableException;
use App\Exception\InvalidApiResponseException;
use App\Service\CityService;
use App\Service\WeatherService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:get-weather',
    description: 'Get weather for cities',
)]
final class GetWeatherCommand extends Command
{
    private const int MAX_ATTEMPTS = 3;

    public function __construct(
        private readonly CityService $cityService,
        private readonly WeatherService $weatherService,
        private readonly LoggerInterface $logger,
        private readonly string $cityApiUrl,
        private readonly string $weatherApiUrl,
        private readonly string $sleepFunction = 'sleep',
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $attemptQuantity = 0;

        while ($attemptQuantity < self::MAX_ATTEMPTS) {
            try {
                $this->processCities($output);

                return Command::SUCCESS;
            } catch (ApiNotAvailableException $exception) {
                $this->handleException(
                    $exception,
                    $output,
                    $attemptQuantity,
                    'app:get-weather no API response'
                );
            } catch (InvalidApiResponseException $exception) {
                $this->handleInvalidApiResponseException($exception, $output);
                break;
            } catch (\Throwable $error) {
                $this->handleException(
                    $error,
                    $output,
                    $attemptQuantity,
                    'app:get-weather got error `{error}`'
                );
            }
        }

        return Command::FAILURE;
    }

    private function processCities(OutputInterface $output): void
    {
        $this->logger->info('app:get-weather is called');

        foreach ($this->cityService->getCities($this->cityApiUrl) as $city) {
            $this->processCity($output, $city);
        }
    }

    private function processCity(OutputInterface $output, CityDto $city): void
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

    private function handleException(
        \Throwable $error,
        OutputInterface $output,
        int &$attemptQuantity,
        string $logMessage,
    ): void {
        ++$attemptQuantity;

        $logContext = ['error' => $error->getMessage()];
        $logSeverity = self::MAX_ATTEMPTS !== $attemptQuantity ? 'error' : 'critical';

        $this->logger->$logSeverity($logMessage, $logContext);
        $output->writeln('`'.$error->getMessage().'`, '.('error' === $logSeverity ? 'trying again' : 'stopping'));

        if (is_callable($this->sleepFunction) && 'error' === $logSeverity) {
            ($this->sleepFunction)(pow($attemptQuantity, 2));
        }
    }

    private function handleInvalidApiResponseException(
        InvalidApiResponseException $exception,
        OutputInterface $output
    ): void {
        $this->logger->critical('app:get-weather invalid data received, stopping');
        $output->writeln($exception->getMessage().', invalid data received');
    }
}
