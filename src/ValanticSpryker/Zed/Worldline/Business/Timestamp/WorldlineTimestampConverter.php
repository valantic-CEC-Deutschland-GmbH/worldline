<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Timestamp;

use DateTime;
use DateTimeZone;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class WorldlineTimestampConverter implements WorldlineTimestampConverterInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     */
    public function __construct(private WorldlineConfig $worldlineConfig)
    {
    }

    /**
     * @param string|null $statusCodeChangeDateTime
     *
     * @return string|null
     */
    public function getWorldlineTimestampInUTC(?string $statusCodeChangeDateTime): ?string
    {
        if (!$statusCodeChangeDateTime) {
            return null;
        }

        $timezoneOfWorldlineTimeStamps = $this->worldlineConfig->getWorldlineTimeStampTimeZone();
        $dateTimeZone = new DateTimeZone($timezoneOfWorldlineTimeStamps);
        $dateTimeInWorldlineTimezone = DateTime::createFromFormat('YmdHis', $statusCodeChangeDateTime, $dateTimeZone);

        $dateTimeZone = new DateTimeZone('UTC');

        return $dateTimeInWorldlineTimezone->setTimezone($dateTimeZone)->format('YmdHis');
    }

    /**
     * @inheritDoc
     */
    public function createCurrentUTCTimestamp(): string
    {
        return (new DateTime())->setTimestamp(time() - date('Z'))->format('YmdHis');
    }
}
