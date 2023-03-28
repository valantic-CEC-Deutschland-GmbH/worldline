<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Exception;
use Ingenico\Connect\Sdk\ConnectionResponse;

interface WorldlineApiCallLoggerInterface
{
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
    ): void;

    /**
     * @param string $requestId
     * @param string $requestUri
     * @param \Ingenico\Connect\Sdk\ConnectionResponse $response
     *
     * @return void
     */
    public function logResponse(string $requestId, string $requestUri, ConnectionResponse $response): void;

    /**
     * @param string $requestId
     * @param string $requestUri
     * @param \Exception $exception
     *
     * @return void
     */
    public function logException(string $requestId, string $requestUri, Exception $exception): void;
}
