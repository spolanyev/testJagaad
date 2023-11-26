<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CityDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[SerializedName('name')]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Range(min: -90, max: 90)]
        #[SerializedName('latitude')]
        public float $latitude,
        #[Assert\NotBlank]
        #[Assert\Range(min: -180, max: 180)]
        #[SerializedName('longitude')]
        public float $longitude
    ) {
    }
}
