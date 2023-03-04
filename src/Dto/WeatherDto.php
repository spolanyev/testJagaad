<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\SerializedPath;
use Symfony\Component\Validator\Constraints as Assert;

readonly class WeatherDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[SerializedPath('[current][condition][text]')]
        public string $currentWeather,
        #[SerializedPath('[forecast][forecastday][1][hour][0][condition][text]')]
        #[Assert\NotBlank]
        public string $tomorrowWeather
    ) {
    }
}
