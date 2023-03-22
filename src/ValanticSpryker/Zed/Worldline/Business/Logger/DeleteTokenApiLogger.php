<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class DeleteTokenApiLogger implements WorldlineApiLoggerPluginInterface
{
    /**
     * @inheritDoc
     */
    public function logApiCallStart(TransferInterface $transfer, VsyWorldlineApiLog $worldlineApiLogEntity): void
    {
        if (!$transfer instanceof WorldlineDeleteTokenRequestTransfer) {
            return;
        }

        $worldlineApiLogEntity->setApiMethod(WorldlineConstants::WORLDLINE_API_METHOD_DELETE_TOKEN);
    }

    /**
     * @inheritDoc
     */
    public function logApiCallEnd(TransferInterface $transfer, VsyWorldlineApiLog $apiLogEntity, VsyPaymentWorldlineTransactionStatusLog $transactionStatusLogEntity): void
    {
    }
}
