<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\CityDto;

final class CityService extends AbstractJsonApiClient
{
    /**
     * @return array<CityDto>
     */
    public function getCities(string $cityUri): array
    {
        return $this->fetchData($cityUri);
    }

    protected function getDtoClass(): string
    {
        return CityDto::class.'[]';
    }

    protected function validate(object|array $dto): void
    {
        $this->validateDto($dto);
    }
}
