<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Generated\Shared\Transfer\WorldlineApprovePaymentRequestTransfer;
use Generated\Shared\Transfer\WorldlineApprovePaymentResponseTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Exception\ApprovePaymentFailedException;
use ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverterInterface;

class ApprovePaymentApiLogger implements WorldlineApiLoggerPluginInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverterInterface $worldlineTimestampConverter
     */
    public function __construct(private WorldlineTimestampConverterInterface $worldlineTimestampConverter)
    {
    }

    /**
     * @inheritDoc
     */
    public function logApiCallStart(TransferInterface $transfer, VsyWorldlineApiLog $worldlineApiLogEntity): void
    {
        if (!$transfer instanceof WorldlineApprovePaymentRequestTransfer) {
            return;
        }
        $worldlineApiLogEntity->setPaymentId($transfer->getPaymentId());
        $worldlineApiLogEntity->setApiMethod(WorldlineConstants::WORLDLINE_API_METHOD_APPROVE_PAYMENT);
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity
     * @param \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionStatusLogEntity
     *
     * @throws \ValanticSpryker\Zed\Worldline\Business\Exception\ApprovePaymentFailedException
     *
     * @return void
     */
    public function logApiCallEnd(TransferInterface $transfer, VsyWorldlineApiLog $apiLogEntity, VsyPaymentWorldlineTransactionStatusLog $transactionStatusLogEntity): void
    {
        if (!$transfer instanceof WorldlineApprovePaymentResponseTransfer) {
            return;
        }

        $transactionStatusLogEntity->setTransactionType('payment');
        $transactionStatusLogEntity->setFkPaymentWorldline($apiLogEntity->getFkPaymentWorldline());
        if ($transfer->getIsSuccess() && $transfer->getPayment()) {
            if ($transfer->getPayment()->getPaymentOutput() && $transfer->getPayment()->getPaymentOutput()->getAmountOfMoney()) {
                $transactionStatusLogEntity->setAmount(
                    $transfer->getPayment()->getPaymentOutput()->getAmountOfMoney()->getAmount(),
                );
            }

            $statusOutput = $transfer->getPayment()->getStatusOutput();
            if ($statusOutput) {
                $transactionStatusLogEntity->setStatusCode($statusOutput->getStatusCode());
                $transactionStatusLogEntity->setStatusCodeChangeDateTime($this->worldlineTimestampConverter->getWorldlineTimestampInUTC($statusOutput->getStatusCodeChangeDateTime()));
                $transactionStatusLogEntity->setStatusCategory($statusOutput->getStatusCategory());
                $transactionStatusLogEntity->setAuthorized($statusOutput->getIsAuthorized());
                $transactionStatusLogEntity->setCancellable($statusOutput->getIsCancellable());
                $transactionStatusLogEntity->setRefundable($statusOutput->getIsRefundable());
            }
            $transactionStatusLogEntity->setStatus($transfer->getPayment()->getStatus());
        } else {
            $transactionStatusLogEntity->setStatus(WorldlineConstants::TRANSACTION_STATUS_APPROVE_PAYMENT_STATUS_FAILED);
            $transactionStatusLogEntity->setStatusCodeChangeDateTime($this->worldlineTimestampConverter->createCurrentUTCTimestamp());

            throw new ApprovePaymentFailedException();
        }
        if (!$transactionStatusLogEntity->getStatusCodeChangeDateTime()) {
            $transactionStatusLogEntity->setStatusCodeChangeDateTime($this->worldlineTimestampConverter->createCurrentUTCTimestamp());
        }
    }
}
