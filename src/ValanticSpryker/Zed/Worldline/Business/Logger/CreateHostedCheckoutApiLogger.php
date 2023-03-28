<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use DateTime;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;

class CreateHostedCheckoutApiLogger implements WorldlineApiLoggerPluginInterface
{
    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $worldlineApiLogEntity
     *
     * @return void
     */
    public function logApiCallStart(TransferInterface $transfer, VsyWorldlineApiLog $worldlineApiLogEntity): void
    {
        if (!$transfer instanceof WorldlineCreateHostedCheckoutTransfer) {
            return;
        }

        $worldlineApiLogEntity->setApiMethod(WorldlineConstants::WORLDLINE_API_METHOD_CREATE_HOSTED_CHECKOUT);
        if ($transfer->getOrder() && $transfer->getOrder()->getReferences() && $transfer->getOrder()->getReferences()->getMerchantReference()) {
            $worldlineApiLogEntity->setOrderReference($transfer->getOrder()->getReferences()->getMerchantReference());
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
        if (!$transfer instanceof WorldlineCreateHostedCheckoutResponseTransfer) {
            return;
        }
        $transactionStatusLogEntity->setTransactionType('payment');
        $transactionStatusLogEntity->setFkPaymentWorldline($apiLogEntity->getFkPaymentWorldline());
        $transactionStatusLogEntity->setStatus($transfer->getIsSuccess() ? WorldlineConstants::STATUS_HOSTED_CHECKOUT_CREATED : WorldlineConstants::STATUS_HOSTED_CHECKOUT_FAILED);
        $transactionStatusLogEntity->setStatusCodeChangeDateTime((new DateTime())->setTimestamp(time())->format('YmdHis'));
    }
}
