<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use ArrayObject;
use Exception;
use Generated\Shared\Transfer\WorldlineApiLogTransfer;
use Generated\Shared\Transfer\WorldlineHeaderTransfer;
use Ingenico\Connect\Sdk\ConnectionResponse;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManagerInterface;

class WorldlineApiCallLogger implements WorldlineApiCallLoggerInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManagerInterface $entityManager
     */
    public function __construct(private WorldlineEntityManagerInterface $entityManager)
    {
    }

    /**
     * @param string $requestId
     * @param string $requestMethod
     * @param string $requestUri
     * @param array $requestHeaders
     * @param string $requestBody
     *
     * @return void
     */
    public function logRequest(
        string $requestId,
        string $requestMethod,
        string $requestUri,
        array $requestHeaders,
        string $requestBody = ''
    ): void {
        $requestHeaderTransfers = new ArrayObject();

        foreach ($requestHeaders as $name => $value) {
            $requestHeaderTransfers->append(
                (new WorldlineHeaderTransfer())
                    ->setName($name)
                    ->setValue($value),
            );
        }

        $apiLogTransfer = (new WorldlineApiLogTransfer())
            ->setRequestId($requestId)
            ->setRequestMethod($requestMethod)
            ->setRequestUri($requestUri)
            ->setRequestHeaders($requestHeaderTransfers)
            ->setRequestBody($requestBody);

        $this->entityManager->saveApiCallRequest($apiLogTransfer);
    }

    /**
     * @param string $requestId
     * @param string $requestUri
     * @param \Ingenico\Connect\Sdk\ConnectionResponse $response
     *
     * @return void
     */
    public function logResponse(string $requestId, string $requestUri, ConnectionResponse $response): void
    {
        $responseHeaderTransfers = new ArrayObject();

        foreach ($response->getHeaders() as $name => $value) {
            $responseHeaderTransfers->append(
                (new WorldlineHeaderTransfer())
                    ->setName($name)
                    ->setValue($value),
            );
        }
        $apiLogTransfer = (new WorldlineApiLogTransfer())
            ->setRequestId($requestId)
            ->setRequestUri($requestUri)
            ->setResponseHeaders($responseHeaderTransfers)
            ->setResponseBody($response->getBody());

        $this->entityManager->saveApiCallResponse($apiLogTransfer);
    }

    /**
     * @param string $requestId
     * @param string $requestUri
     * @param \Exception $exception
     *
     * @return void
     */
    public function logException(string $requestId, string $requestUri, Exception $exception): void
    {
        $apiLogTransfer = (new WorldlineApiLogTransfer())
            ->setRequestId($requestId)
            ->setRequestUri($requestUri)
            ->setErrorCode($exception->getCode())
            ->setErrorMessage($exception->getMessage());

        $this->entityManager->saveApiCallException($apiLogTransfer);
    }
}
