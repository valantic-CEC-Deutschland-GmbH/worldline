<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

interface WorldlineApiLoggerInterface
{
    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     *
     * @return \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog
     */
    public function logApiCallStart(TransferInterface $transfer): VsyWorldlineApiLog;

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity
     *
     * @return void
     */
    public function logApiCallEnd(TransferInterface $transfer, VsyWorldlineApiLog $apiLogEntity): void;
}
