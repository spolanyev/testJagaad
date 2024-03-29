<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Exception\ApiNotAvailableException;
use App\Exception\InvalidApiResponseException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiService
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    protected function makeGetRequest(string $uri): string
    {
        $response = $this->httpClient->request('GET', $uri);
        $json = $response->getContent();

        if (empty($json)) {
            throw new ApiNotAvailableException();
        }

        return $json;
    }

    protected function deserializeJson(string $json, string $dtoClass, string $format = 'json'): mixed
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer($classMetadataFactory), new ArrayDenormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->deserialize($json, $dtoClass, $format);
    }

    /**
     * @param object|array<object> $dto
     */
    protected function validateDto(object|array $dto): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $violations = $validator->validate($dto);

        if ($violations->count()) {
            throw new InvalidApiResponseException();
        }
    }
}
