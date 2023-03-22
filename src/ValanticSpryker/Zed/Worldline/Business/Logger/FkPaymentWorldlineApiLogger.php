<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class FkPaymentWorldlineApiLogger implements WorldlineApiLoggerPluginInterface
{
    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $worldlineApiLogEntity
     *
     * @return void
     */
    public function logApiCallStart(TransferInterface $transfer, VsyWorldlineApiLog $worldlineApiLogEntity): void
    {
        if (method_exists($transfer, 'getFkPaymentWorldline')) {
            $worldlineApiLogEntity->setFkPaymentWorldline($transfer->getFkPaymentWorldline());
        }
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity
     * @param \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionStatusLogEntity
     *
     * @return void
     */
    public function logApiCallEnd(
        TransferInterface $transfer,
        VsyWorldlineApiLog $apiLogEntity,
        VsyPaymentWorldlineTransactionStatusLog $transactionStatusLogEntity
    ): void {
    }
}
