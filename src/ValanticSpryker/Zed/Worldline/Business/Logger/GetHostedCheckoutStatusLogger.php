<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Logger;

use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Timestamp\WorldlineTimestampConverterInterface;
use Spryker\Shared\Kernel\Transfer\TransferInterface;

class GetHostedCheckoutStatusLogger implements WorldlineApiLoggerPluginInterface
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
        if (!$transfer instanceof WorldlineGetHostedCheckoutStatusTransfer) {
            return;
        }

        $worldlineApiLogEntity->setApiMethod(WorldlineConstants::WORLDLINE_API_METHOD_GET_HOSTED_CHECKOUT_STATUS);
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
        if (!$transfer instanceof WorldlineGetHostedCheckoutStatusResponseTransfer) {
            return;
        }

        if ($transfer->getCreatedPaymentOutput() && $transfer->getCreatedPaymentOutput()->getPayment() && $transfer->getCreatedPaymentOutput()->getPayment()->getId()) {
            $apiLogEntity->setPaymentId($transfer->getCreatedPaymentOutput()->getPayment()->getId());
        }
        $this->logPaymentTransactionStatus($transactionStatusLogEntity, $apiLogEntity, $transfer, WorldlineConstants::TRANSACTION_STATUS_GET_HOSTED_CHECKOUT_STATUS_FAILED);
    }

    /**
     * @param \Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLog $transactionStatusLogEntity
     * @param \Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog $apiLogEntity
     * @param \Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer $transfer
     * @param string $failStatus
     *
     * @return void
     */
    protected function logPaymentTransactionStatus(
        VsyPaymentWorldlineTransactionStatusLog $transactionStatusLogEntity,
        VsyWorldlineApiLog $apiLogEntity,
        WorldlineGetHostedCheckoutStatusResponseTransfer $transfer,
        string $failStatus
    ): void {
        $transactionStatusLogEntity->setTransactionType('payment');
        $transactionStatusLogEntity->setFkPaymentWorldline($apiLogEntity->getFkPaymentWorldline());
        if ($transfer->getIsSuccess()) {
            $transactionStatusLogEntity->setStatus($transfer->getStatus());

            if ($transfer->getCreatedPaymentOutput() && $transfer->getCreatedPaymentOutput()->getPayment()) {
                if (
                    $transfer->getCreatedPaymentOutput()->getPayment()->getPaymentOutput()
                    && $transfer->getCreatedPaymentOutput()->getPayment()->getPaymentOutput()->getAmountOfMoney()
                ) {
                    $transactionStatusLogEntity->setAmount(
                        $transfer->getCreatedPaymentOutput()->getPayment()->getPaymentOutput()->getAmountOfMoney()->getAmount(),
                    );
                }

                $statusOutput = $transfer->getCreatedPaymentOutput()->getPayment()->getStatusOutput();
                if ($statusOutput) {
                    $transactionStatusLogEntity->setStatusCode($statusOutput->getStatusCode());
                    $transactionStatusLogEntity->setStatusCodeChangeDateTime($this->worldlineTimestampConverter->getWorldlineTimestampInUTC($statusOutput->getStatusCodeChangeDateTime()));
                    $transactionStatusLogEntity->setStatusCategory($statusOutput->getStatusCategory());
                    $transactionStatusLogEntity->setStatus($transfer->getCreatedPaymentOutput()->getPayment()->getStatus());
                    $transactionStatusLogEntity->setAuthorized($statusOutput->getIsAuthorized());
                    $transactionStatusLogEntity->setCancellable($statusOutput->getIsCancellable());
                    $transactionStatusLogEntity->setRefundable($statusOutput->getIsRefundable());
                }
            }
        } else {
            $transactionStatusLogEntity->setStatus($failStatus);
        }
        if (!$transactionStatusLogEntity->getStatusCodeChangeDateTime()) {
            $transactionStatusLogEntity->setStatusCodeChangeDateTime($this->worldlineTimestampConverter->createCurrentUTCTimestamp());
        }
    }
}
