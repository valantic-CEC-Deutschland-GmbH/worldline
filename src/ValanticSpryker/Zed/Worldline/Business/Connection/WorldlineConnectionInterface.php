<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Connection;

use Ingenico\Connect\Sdk\Connection;

interface WorldlineConnectionInterface
{
    /**
     * @return \Ingenico\Connect\Sdk\Connection
     */
    public function getConnection(): Connection;
}
