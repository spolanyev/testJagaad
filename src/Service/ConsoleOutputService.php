<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleOutputService implements OutputServiceInterface
{
    private ?OutputInterface $output;

    public function setOutput(?OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function write(string $message): void
    {
        if ($this->output) {
            $this->output->writeln($message);
        } else {
            throw new \LogicException('OutputInterface not set');
        }
    }
}
