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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractJsonApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ValidatorInterface $validator
    ) {
    }

    abstract protected function getDtoClass(): string;

    /**
     * @param object|array<object> $dto
     */
    abstract protected function validate(object|array $dto): void;

    protected function makeGetRequest(string $uri): string
    {
        $response = $this->httpClient->request('GET', $uri);
        $json = $response->getContent();

        if (empty($json)) {
            throw new ApiNotAvailableException();
        }

        return $json;
    }

    protected function deserializeJson(string $json): mixed
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer($classMetadataFactory), new ArrayDenormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->deserialize($json, $this->getDtoClass(), 'json');
    }

    /**
     * @param object|array<object> $dto
     */
    protected function validateDto(object|array $dto): void
    {
        $violations = $this->validator->validate($dto);
        if ($violations->count()) {
            throw new InvalidApiResponseException();
        }
    }

    public function fetchData(string $uri): mixed
    {
        $json = $this->makeGetRequest($uri);
        $dto = $this->deserializeJson($json);
        $this->validate($dto);

        return $dto;
    }
}
