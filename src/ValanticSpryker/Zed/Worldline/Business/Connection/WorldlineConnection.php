<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Connection;

use Exception;
use Ingenico\Connect\Sdk\ConnectionResponse;
use Ingenico\Connect\Sdk\DefaultConnection;
use ValanticSpryker\Zed\Worldline\Business\Logger\DummyCommunicatorLogger;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiCallLoggerInterface;

class WorldlineConnection extends DefaultConnection
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiCallLoggerInterface $apiLogger
     * @param int $connectTimeout
     * @param int $readTimeout
     */
    public function __construct(
        private WorldlineApiCallLoggerInterface $apiLogger,
        int $connectTimeout = -1,
        int $readTimeout = -1
    ) {
        parent::__construct(
            $connectTimeout,
            $readTimeout,
        );
        $this->enableLogging(new DummyCommunicatorLogger());
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
    protected function logRequest($requestId, $requestMethod, $requestUri, array $requestHeaders, $requestBody = ''): void
    {
        if ($this->communicatorLogger) {
            $this->apiLogger->logRequest(
                $requestId,
                $requestMethod,
                $requestUri,
                $requestHeaders,
                $requestBody,
            );
        }
    }

    /**
     * @param string $requestId
     * @param string $requestUri
     * @param \Ingenico\Connect\Sdk\ConnectionResponse $response
     *
     * @return void
     */
    protected function logResponse($requestId, $requestUri, ConnectionResponse $response): void
    {
        if ($this->communicatorLogger) {
            $this->apiLogger->logResponse(
                $requestId,
                $requestUri,
                $response,
            );
        }
    }

    /**
     * @param string $requestId
     * @param string $requestUri
     * @param \Exception $exception
     *
     * @return void
     */
    protected function logException($requestId, $requestUri, Exception $exception): void
    {
        if ($this->communicatorLogger) {
            $this->apiLogger->logException(
                $requestId,
                $requestUri,
                $exception,
            );
        }
    }
}
