<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\CityDto;

final class CityService extends ApiService
{
    /**
     * @return array<CityDto>
     */
    public function getCities(string $cityUri): array
    {
        $json = $this->makeGetRequest($cityUri);
        $cities = $this->deserializeJson($json, CityDto::class.'[]');
        $this->validateDto($cities);

        return $cities;
    }
}
