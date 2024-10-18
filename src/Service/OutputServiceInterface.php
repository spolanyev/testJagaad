<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

interface OutputServiceInterface
{
    public function write(string $message): void;
}
