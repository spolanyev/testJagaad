<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Command;

use App\Exception\ApiNotAvailableException;
use App\Exception\InvalidApiResponseException;
use App\Service\CityProcessor;
use App\Service\OutputServiceInterface;
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
        private readonly CityProcessor $cityProcessor,
        private readonly LoggerInterface $logger,
        private readonly OutputServiceInterface $outputService,
        private readonly string $sleepFunction = 'sleep',
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputService->setOutput($output);
        $attemptQuantity = 0;

        $this->logger->info('app:get-weather is called');

        while ($attemptQuantity < self::MAX_ATTEMPTS) {
            try {
                $this->cityProcessor->processCities();

                return Command::SUCCESS;
            } catch (ApiNotAvailableException $exception) {
                $this->handleException(
                    $exception,
                    $attemptQuantity,
                    'app:get-weather no API response'
                );
            } catch (InvalidApiResponseException $exception) {
                $this->handleInvalidApiResponseException($exception);
                break;
            } catch (\Throwable $error) {
                $this->handleException(
                    $error,
                    $attemptQuantity,
                    'app:get-weather got error `{error}`'
                );
            }
        }

        return Command::FAILURE;
    }

    private function handleException(
        \Throwable $error,
        int &$attemptQuantity,
        string $logMessage,
    ): void {
        ++$attemptQuantity;

        $logContext = ['error' => $error->getMessage()];
        $logSeverity = self::MAX_ATTEMPTS !== $attemptQuantity ? 'error' : 'critical';

        $this->logger->$logSeverity($logMessage, $logContext);
        $this->outputService->write(
            '`'.$error->getMessage().'`, '.('error' === $logSeverity ? 'trying again' : 'stopping')
        );

        if (is_callable($this->sleepFunction) && 'error' === $logSeverity) {
            ($this->sleepFunction)(pow($attemptQuantity, 2));
        }
    }

    private function handleInvalidApiResponseException(InvalidApiResponseException $exception): void
    {
        $this->logger->critical('app:get-weather invalid data received, stopping');
        $this->outputService->write($exception->getMessage().', invalid data received');
    }
}
