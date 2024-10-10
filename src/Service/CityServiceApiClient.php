<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\CityDto;

final class CityServiceApiClient extends AbstractJsonApiClient implements CityServiceInterface
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
