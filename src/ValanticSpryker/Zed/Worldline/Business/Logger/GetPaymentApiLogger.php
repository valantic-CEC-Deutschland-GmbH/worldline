<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Generated\Shared\Transfer\WorldlineGetPaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineGetPaymentResponseTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverterInterface;

class GetPaymentApiLogger implements WorldlineApiLoggerPluginInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverterInterface $worldlineTimestampConverter
     */
    public function __construct(private WorldlineTimestampConverterInterface $worldlineTimestampConverter)
    {
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $worldlineApiLogEntity
     *
     * @return void
     */
    public function logApiCallStart(TransferInterface $transfer, VsyWorldlineApiLog $worldlineApiLogEntity): void
    {
        if (!$transfer instanceof WorldlineGetPaymentRequestTransfer) {
            return;
        }
        $worldlineApiLogEntity->setPaymentId($transfer->getPaymentId());
        $worldlineApiLogEntity->setApiMethod(WorldlineConstants::WORLDLINE_API_METHOD_GET_PAYMENT);
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
        if (!$transfer instanceof WorldlineGetPaymentResponseTransfer) {
            return;
        }

        $transactionStatusLogEntity->setTransactionType('payment');
        $transactionStatusLogEntity->setFkPaymentWorldline($apiLogEntity->getFkPaymentWorldline());
        if ($transfer->getIsSuccess()) {
            if ($transfer->getPaymentOutput()) {
                if ($transfer->getPaymentOutput()->getAmountOfMoney()) {
                    $transactionStatusLogEntity->setAmount(
                        $transfer->getPaymentOutput()->getAmountOfMoney()->getAmount(),
                    );
                }

                $statusOutput = $transfer->getStatusOutput();
                if ($statusOutput) {
                    $transactionStatusLogEntity->setStatusCode($statusOutput->getStatusCode());
                    $transactionStatusLogEntity->setStatusCodeChangeDateTime($this->worldlineTimestampConverter->getWorldlineTimestampInUTC($statusOutput->getStatusCodeChangeDateTime()));
                    $transactionStatusLogEntity->setStatusCategory($statusOutput->getStatusCategory());
                    $transactionStatusLogEntity->setAuthorized($statusOutput->getIsAuthorized());
                    $transactionStatusLogEntity->setCancellable($statusOutput->getIsCancellable());
                    $transactionStatusLogEntity->setRefundable($statusOutput->getIsRefundable());
                }
            }
            $transactionStatusLogEntity->setStatus($transfer->getStatus());
        } else {
            $transactionStatusLogEntity->setStatus(WorldlineConstants::TRANSACTION_STATUS_GET_PAYMENT_STATUS_FAILED);
        }
        if (!$transactionStatusLogEntity->getStatusCodeChangeDateTime()) {
            $transactionStatusLogEntity->setStatusCodeChangeDateTime($this->worldlineTimestampConverter->createCurrentUTCTimestamp());
        }
    }
}
