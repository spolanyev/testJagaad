<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\CityDto;

interface CityServiceInterface
{
    /**
     * @return array<CityDto>
     */
    public function getCities(string $cityUri): array;
}
