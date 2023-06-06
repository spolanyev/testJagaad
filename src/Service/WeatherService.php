<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\WeatherDto;
use App\Exception\ApiNotAvailableException;
use App\Exception\InvalidApiResponseException;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    public function getWeather(string $weatherUri, HttpClientInterface $httpClient): WeatherDto
    {
        $response = $httpClient->request('GET', $weatherUri);
        $json = $response->getContent();

        if (empty($json)) {
            throw new ApiNotAvailableException();
        }

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer($classMetadataFactory)];
        $serializer = new Serializer($normalizers, $encoders);
        $weather = $serializer->deserialize($json, WeatherDto::class, 'json');
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
        $violations = $validator->validate($weather);

        if ($violations->count()) {
            throw new InvalidApiResponseException();
        }

        return $weather;
    }
}
