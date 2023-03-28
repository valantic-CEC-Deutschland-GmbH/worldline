<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\Worldline\Business;

use Exception;
use Ingenico\Connect\Sdk\ConnectionResponse;
use Ingenico\Connect\Sdk\ProxyConfiguration;
use ValanticSpryker\Zed\Worldline\Business\Connection\WorldlineConnection;
use ValanticSpryker\Zed\Worldline\Business\Logger\WorldlineApiCallLoggerInterface;

class TestingConnection extends WorldlineConnection
{
    private $response;

    private $exception;

    function __construct(WorldlineApiCallLoggerInterface $apiLogger, ?ConnectionResponse $response = null, ?Exception $exception = null)
    {
        parent::__construct($apiLogger);
        $this->response = $response;
        $this->exception = $exception;
    }

    /**
     * @param $httpMethod
     * @param $requestUri
     * @param $requestHeaders
     * @param $body
     * @param callable $responseHandler
     * @param \Ingenico\Connect\Sdk\ProxyConfiguration|null $proxyConfiguration
     *
     * @throws \Exception
     *
     * @return \Ingenico\Connect\Sdk\ConnectionResponse|\Ingenico\Connect\Sdk\DefaultConnectionResponse|null
     */
    protected function executeRequest(
        $httpMethod,
        $requestUri,
        $requestHeaders,
        $body,
        callable $responseHandler,
        ?ProxyConfiguration $proxyConfiguration = null
    ) {
        if ($this->exception !== null) {
            throw $this->exception;
        } else {
            $statusCode = $this->response->getHttpStatusCode();
            $body = $this->response->getBody();
            $headers = $this->response->getHeaders();
            call_user_func($responseHandler, $statusCode, $body, $headers);

            return $this->response;
        }
    }
}
