<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use Symfony\Component\Console\Output\OutputInterface;

interface OutputServiceInterface
{
    public function setOutput(OutputInterface $output): void;

    public function write(string $message): void;
}
