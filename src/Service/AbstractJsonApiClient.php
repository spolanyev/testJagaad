<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Service;

use App\Exception\ApiNotAvailableException;
use App\Exception\InvalidApiResponseException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractJsonApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
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
        $dto = $this->serializer->deserialize($json, $this->getDtoClass(), 'json');
        $this->validate($dto);

        return $dto;
    }
}
