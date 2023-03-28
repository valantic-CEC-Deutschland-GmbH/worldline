<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Timestamp;

interface WorldlineTimestampConverterInterface
{
    /**
     * @param string|null $statusCodeChangeDateTime
     *
     * @return string|null
     */
    public function getWorldlineTimestampInUTC(?string $statusCodeChangeDateTime): ?string;

    /**
     * @return string
     */
    public function createCurrentUTCTimestamp(): string;
}
