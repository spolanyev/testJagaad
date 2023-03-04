<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\CityDto;
use App\Exception\ApiNotAvailableException;
use App\Exception\InvalidApiResponseException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CityService
{
    /**
     * @return array<CityDto>
     */
    public function getCities(string $cityUri, HttpClientInterface $httpClient): array
    {
        $response = $httpClient->request('GET', $cityUri);
        $json = $response->getContent();

        if (empty($json)) {
            throw new ApiNotAvailableException();
        }

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer(), new ArrayDenormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $cities = $serializer->deserialize($json, CityDto::class.'[]', 'json');
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
        $violations = $validator->validate($cities);

        if ($violations->count()) {
            throw new InvalidApiResponseException();
        }

        return $cities;
    }
}
