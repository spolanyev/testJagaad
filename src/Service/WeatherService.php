<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Dto\WeatherDto;
use App\Exception\ApiNotAvailableException;
use App\Exception\InvalidApiResponseException;
use Symfony\Component\PropertyAccess\PropertyAccess;
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

        $normalized = (array) json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $currentWeather = $propertyAccessor->getValue(
            $normalized,
            '[current][condition][text]'
        );
        $tomorrowWeather = $propertyAccessor->getValue(
            $normalized,
            '[forecast][forecastday][1][hour][0][condition][text]'
        );

        $weather = new WeatherDto($currentWeather, $tomorrowWeather);
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
        $violations = $validator->validate($weather);

        if ($violations->count()) {
            throw new InvalidApiResponseException();
        }

        return $weather;
        /*
        //this doesn't work: Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException : Cannot create an instance of "App\Dto\WeatherDto" from serialized data because its constructor requires parameter "currentWeather" to be present.
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $nameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $normalizers = [new ObjectNormalizer($classMetadataFactory, $nameConverter)];
        $encoders = [new JsonEncoder()];

        $serializer = new Serializer($normalizers, $encoders);
        $weather = $serializer->deserialize(
            $json,
            WeatherDto::class,
            'json'
        );
        */
    }
}
