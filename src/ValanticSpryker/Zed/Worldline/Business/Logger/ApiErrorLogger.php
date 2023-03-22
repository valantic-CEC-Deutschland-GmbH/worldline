<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class ApiErrorLogger implements WorldlineApiLoggerPluginInterface
{
    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $worldlineApiLogEntity
     *
     * @return void
     */
    public function logApiCallStart(TransferInterface $transfer, VsyWorldlineApiLog $worldlineApiLogEntity): void
    {
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
        if (method_exists($transfer, 'getErrorId')) {
            $apiLogEntity->setErrorId($transfer->getErrorId());
        }
        if (method_exists($transfer, 'getErrors') && $transfer->getErrors() && ($transfer->getErrors()->count() > 0)) {
            /** @var \Generated\Shared\Transfer\WorldlineErrorItemTransfer $error */
            $error = $transfer->getErrors()->offsetGet(0); // only first error logged atm
            $apiLogEntity->setErrorCode($error->getCode());
            $apiLogEntity->setErrorPropertyName($error->getPropertyName());
            $apiLogEntity->setErrorMessage($error->getMessage());
        }
    }
}
