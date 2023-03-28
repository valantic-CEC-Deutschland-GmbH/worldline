<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Exception;
use Ingenico\Connect\Sdk\CommunicatorLogger;

class DummyCommunicatorLogger implements CommunicatorLogger
{
    /**
     * @param string $message
     *
     * @return void
     */
    public function log($message): void
    {
    }

    /**
     * @param string $message
     * @param \Exception $exception
     *
     * @return void
     */
    public function logException($message, Exception $exception): void
    {
    }
}
