<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Order;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Oms\Command\CommandHandlerInterface;
use ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class OrderHydrator implements OrderHydratorInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Reader\WorldlineReaderInterface $worldlineReader
     * @param \ValanticSpryker\Zed\Worldline\Business\Oms\Command\CommandHandlerInterface $getHostedCheckoutStatusCommandHandler
     */
    public function __construct(private WorldlineReaderInterface $worldlineReader, private CommandHandlerInterface $getHostedCheckoutStatusCommandHandler)
    {
    }

    /**
     * @inheritDoc
     */
    public function hydrateOrderTransfer(OrderTransfer $orderTransfer): OrderTransfer
    {
        foreach ($orderTransfer->getPayments() as $payment) {
            if ($payment->getPaymentProvider() !== WorldlineConfig::PROVIDER_NAME) {
                continue;
            }
            $paymentWorldlineTransfer = $this->worldlineReader->getPaymentWorldlineByIdSalesOrder($orderTransfer->getIdSalesOrder());

            if (!$paymentWorldlineTransfer->getIdPaymentWorldline()) {
                continue;
            }
            $paymentWorldlineTransactionStatusTransfer = $this->worldlineReader->getLatestPaymentWorldlineTransactionStatusLogByFkPaymentWorldline($paymentWorldlineTransfer->getIdPaymentWorldline());

            if (
                $paymentWorldlineTransactionStatusTransfer->getStatus() === WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PENDING
                || $paymentWorldlineTransactionStatusTransfer->getStatus() === WorldlineConstants::STATUS_HOSTED_CHECKOUT_CREATED
            ) {
                $this->getHostedCheckoutStatusCommandHandler->handle([], $orderTransfer);
                $paymentWorldlineTransfer = $this->worldlineReader->getPaymentWorldlineByIdSalesOrder($orderTransfer->getIdSalesOrder());
                $paymentWorldlineTransactionStatusTransfer = $this->worldlineReader->getLatestPaymentWorldlineTransactionStatusLogByFkPaymentWorldline($paymentWorldlineTransfer->getIdPaymentWorldline());
            }

            $orderTransfer->setHostedCheckoutCompletionStatus($this->calculateHostedCheckoutCompletionStatus($paymentWorldlineTransactionStatusTransfer));
            if ($paymentWorldlineTransfer->getPaymentHostedCheckout()) {
                $orderTransfer->setHostedCheckoutId($paymentWorldlineTransfer->getPaymentHostedCheckout()->getHostedCheckoutId());
            }
        }

        return $orderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer $paymentWorldlineTransactionStatusTransfer
     *
     * @return string
     */
    private function calculateHostedCheckoutCompletionStatus(PaymentWorldlineTransactionStatusTransfer $paymentWorldlineTransactionStatusTransfer): string
    {
        return match ($paymentWorldlineTransactionStatusTransfer->getStatus()) {
            WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PENDING => WorldlineConstants::HOSTED_CHECKOUT_COMPLETION_STATUS_PENDING,
            WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CANCELLED_CLIENT_NOT_ELIGIBLE, WorldlineConstants::STATUS_HOSTED_CHECKOUT_STATUS_PAYMENT_CANCELLED_BY_CONSUMER => WorldlineConstants::HOSTED_CHECKOUT_COMPLETION_STATUS_UNSUCCESSFUL,
            default => WorldlineConstants::HOSTED_CHECKOUT_COMPLETION_STATUS_SUCCESSFUL,
        };
    }
}
